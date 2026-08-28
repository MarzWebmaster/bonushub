<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Models\VerificationOtp;
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
            'otp'          => 'required|string|size:6',
        ]);

        // ── VERIFY OTP ─────────────────────────
        $otpVerified = VerificationOtp::verify($validated['email'], $validated['otp'], 'registration');
        if (!$otpVerified) {
            return back()->withErrors([
                'otp' => 'Kod pengesahan tidak sah atau telah tamat tempoh.',
            ])->withInput();
        }

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

            // 2. Create Merchant — PENDING VERIFICATION (not auto-approved)
            $merchant = Merchant::create([
                'company_name' => $validated['company_name'],
                'owner_name'   => $validated['name'],
                'phone'        => $validated['phone'],
                'status'       => Merchant::STATUS_PENDING,
            ]);

            // 3. Link user to merchant
            $user->update(['merchant_id' => $merchant->id]);

            DB::commit();

            // ── NOTIFY ──────────────────────────
            if (NotificationSetting::count() === 0) {
                NotificationSetting::seedDefaults();
            }

            BonusNotifier::toMerchant($merchant, 'merchant_registration', [
                'title'   => '🎉 Selamat datang ke BonusHub!',
                'message' => "Hai {$validated['name']},\n\n"
                    . "Akaun merchant anda untuk **{$validated['company_name']}** telah berjaya didaftarkan!\n\n"
                    . "Sila lengkapkan proses pengesahan dengan memuat naik dokumen IC dan SSM anda.\n\n"
                    . "👉 " . route('merchant.verification') . "\n\n"
                    . "Terima kasih,\nPasukan BonusHub",
                'type'    => 'success',
                'data'    => [
                    'merchant_id'   => $merchant->id,
                    'company_name'  => $validated['company_name'],
                    'redirect_url'  => route('merchant.verification'),
                ],
            ]);

            // Log in the user
            auth()->login($user);

            Log::info("New merchant registered: {$validated['email']} — {$validated['company_name']}", [
                'merchant_id' => $merchant->id,
            ]);

            // Redirect to verification page (upload IC & SSM)
            return redirect()->route('merchant.verification')
                ->with('success', '🎉 Pendaftaran berjaya! Sila lengkapkan pengesahan dokumen.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Merchant registration failed: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'Pendaftaran gagal. Sila cuba lagi.',
            ])->withInput();
        }
    }
}
