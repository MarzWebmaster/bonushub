<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\VerificationOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    /**
     * Send OTP to email
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|in:registration,password_reset',
        ]);

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
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'type' => 'required|in:registration,password_reset',
        ]);

        $verified = VerificationOtp::verify($request->email, $request->otp, $request->type);

        if ($verified) {
            return response()->json([
                'success' => true,
                'message' => 'Email berjaya disahkan!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kod pengesahan tidak sah atau telah tamat tempoh.',
        ], 422);
    }
}
