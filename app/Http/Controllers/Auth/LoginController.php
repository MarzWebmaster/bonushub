<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 900; // 15 minutes

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Brute-force protection: 5 failures per email+IP, then locked for 15 minutes.
        $throttleKey = Str::lower((string) $request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning('Login locked out for: ' . $request->email);
            return back()->withErrors([
                'email' => "Terlalu banyak percubaan log masuk. Cuba lagi dalam {$seconds} saat.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);

            // A merchant can deactivate a staff account; it must not be able to log in (or stay logged in).
            if ((Auth::user()->status ?? 'active') === 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Akaun ini telah dinyahaktifkan. Sila hubungi merchant atau pentadbir anda.',
                ])->onlyInput('email');
            }

            Log::info('User authenticated successfully: ' . $request->email);

            // Redirect to role-specific dashboard
            $user = Auth::user();
            if ($user->hasRole('superadmin')) {
                return redirect()->route('superadmin.dashboard');
            } elseif ($user->hasRole('merchant')) {
                return redirect()->route('merchant.dashboard');
            } elseif ($user->hasRole('staff')) {
                return redirect()->route('staff.dashboard');
            } elseif ($user->hasRole('customer')) {
                return redirect()->route('customer.points');
            }

            return redirect()->intended('/dashboard');
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
        Log::warning('Failed login attempt for: ' . $request->email);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
