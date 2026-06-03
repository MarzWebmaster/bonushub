<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\BonusNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MerchantRegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.merchant-register');
    }

    public function register(Request $request)
    {
        // ── BOT PREVENTION ──────────────────────
        // 1. Honeypot: bots auto-fill hidden field
        if (!empty($request->input('website'))) {
            Log::warning("MerchantRegister: Bot detected (honeypot filled)", [
                'ip'     => $request->ip(),
                'email'  => $request->input('email'),
                'honeypot_value' => $request->input('website'),
            ]);
            // Silently redirect back — don't tell the bot it was caught
            return redirect()->route('merchant.register')
                ->with('success', 'Registration submitted! Please check your email for next steps.');
        }

        // 2. Time trap: form filled too fast (< 3 seconds) = bot
        $timestamp = (int) $request->input('_t');
        if ($timestamp && (time() - $timestamp) < 3) {
            Log::warning("MerchantRegister: Bot detected (time trap — form filled in " . (time() - $timestamp) . "s)", [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);
            return redirect()->route('merchant.register')
                ->with('success', 'Registration submitted! Please check your email for next steps.');
        }

        // 3. Turnstile: verify Cloudflare token
        $turnstileResponse = $request->input('cf-turnstile-response');
        if (!$turnstileResponse || !static::verifyTurnstile($turnstileResponse, $request->ip())) {
            Log::warning("MerchantRegister: Turnstile verification failed", [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);
            return back()->withErrors([
                'turnstile' => 'Sila sahkan anda bukan robot.',
            ])->withInput();
        }

        // ── VALIDATION ─────────────────────────
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'required|string|max:20',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create User with Merchant role
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status'   => 'active',
            ]);
            $user->assignRole('merchant');

            // 2. Create Merchant — AUTO APPROVED
            $merchant = Merchant::create([
                'company_name' => $validated['company_name'],
                'phone'        => $validated['phone'],
                'status'       => 'active',
            ]);

            // 3. Link user to merchant
            $user->update(['merchant_id' => $merchant->id]);

            DB::commit();

            // ── NOTIFY ──────────────────────────
            // Seed defaults if not already seeded
            if (NotificationSetting::count() === 0) {
                NotificationSetting::seedDefaults();
            }

            BonusNotifier::toMerchant($merchant, 'merchant_registration', [
                'title'   => '🎉 Selamat datang ke BonusHub!',
                'message' => "Hai {$validated['name']},\n\n"
                    . "Akaun merchant anda untuk **{$validated['company_name']}** telah berjaya didaftarkan!\n\n"
                    . "Anda kini boleh log masuk dan mula menggunakan BonusHub untuk program loyalty anda.\n\n"
                    . "👉 " . route('login') . "\n\n"
                    . "Terima kasih,\nPasukan BonusHub",
                'type'    => 'success',
                'data'    => [
                    'merchant_id'   => $merchant->id,
                    'company_name'  => $validated['company_name'],
                    'redirect_url'  => route('login'),
                ],
            ]);

            // Log in the user
            auth()->login($user);

            Log::info("New merchant registered: {$validated['email']} — {$validated['company_name']}", [
                'merchant_id' => $merchant->id,
            ]);

            return redirect()->route('merchant.dashboard')
                ->with('success', '🎉 Selamat datang ke BonusHub! Akaun merchant anda telah diaktifkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Merchant registration failed: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'Pendaftaran gagal. Sila cuba lagi.',
            ])->withInput();
        }
    }

    /**
     * Verify Cloudflare Turnstile token.
     */
    private static function verifyTurnstile(string $token, string $ip): bool
    {
        $secret = config('services.turnstile.secret_key') ?? env('TURNSTILE_SECRET_KEY');

        if (!$secret || $secret === 'your-secret-key-here') {
            // Not configured yet — allow through (dev mode)
            return true;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->withOptions(['verify' => false])
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            $result = $response->json();

            return ($result['success'] ?? false) === true;
        } catch (\Throwable $e) {
            Log::error("Turnstile verify failed: {$e->getMessage()}");
            return true; // fail-open: don't block real users if Turnstile API down
        }
    }
}
