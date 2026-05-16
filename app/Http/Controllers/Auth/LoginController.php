<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import Log facade

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Log successful authentication
            Log::info('User authenticated successfully: ' . $request->email);
            return redirect()->intended('/dashboard');
        }

        // Log failed authentication
        Log::warning('Failed login attempt for: ' . $request->email);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}
