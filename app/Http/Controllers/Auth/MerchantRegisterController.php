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
}
