<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
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
