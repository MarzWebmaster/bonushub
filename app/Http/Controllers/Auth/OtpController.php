<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\VerificationOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    /**
     * Send OTP to email
     */
    public function send(Request $request)
    {
        $request->validate([
            // not_regex: laravel/framework 11.x has no fix for the CRLF-in-email advisory (fixed
            // only in 12.60+). This address goes straight into a mail header (Mail::raw ->to()).
            'email' => ['required', 'email', 'max:254', 'not_regex:/[\r\n]/'],
            'type' => 'required|in:registration,password_reset',
        ]);

        // Rate limit per IP as well (the per-email cap below does not stop one client
        // from mass-mailing many different addresses): 10 sends per IP per hour.
        $ipKey = 'otp-send-ip|' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percubaan. Sila cuba lagi selepas 1 jam.',
            ], 429);
        }
        RateLimiter::hit($ipKey, 3600);

        try {
            // Rate limit: max 3 OTPs per email per hour
            $recentCount = VerificationOtp::where('email', $request->email)
                ->where('created_at', '>', now()->subHour())
                ->count();

            if ($recentCount >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percubaan. Sila cuba lagi selepas 1 jam.',
                ], 429);
            }

            $otp = VerificationOtp::generateForEmail($request->email, $request->type);

            return response()->json([
                'success' => true,
                'message' => 'Kod pengesahan telah dihantar ke email anda.',
            ]);

        } catch (\Exception $e) {
            Log::error('OTP send failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghantar kod. Sila cuba lagi.',
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:254', 'not_regex:/[\r\n]/'],
            'otp' => 'required|string|size:6',
            'type' => 'required|in:registration,password_reset',
        ]);

        // A 6-digit OTP has only 1,000,000 values, so cap guesses per email: 5 wrong
        // attempts per 10 minutes, after which the pending OTP is burned (must request a new one).
        $verifyKey = 'otp-verify|' . strtolower($request->email) . '|' . $request->type;
        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percubaan. Sila minta kod baharu selepas beberapa minit.',
            ], 429);
        }

        $verified = VerificationOtp::verify($request->email, $request->otp, $request->type);

        if ($verified) {
            RateLimiter::clear($verifyKey);
            return response()->json([
                'success' => true,
                'message' => 'Email berjaya disahkan!',
            ]);
        }

        RateLimiter::hit($verifyKey, 600);
        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            VerificationOtp::where('email', $request->email)
                ->where('type', $request->type)
                ->where('is_used', false)
                ->delete();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kod pengesahan tidak sah atau telah tamat tempoh.',
        ], 422);
    }
}
