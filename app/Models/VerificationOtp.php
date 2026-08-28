<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class VerificationOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'type',
        'is_used',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Generate and send OTP for email verification
     */
    public static function generateForEmail(string $email, string $type = 'registration'): self
    {
        // Delete old unused OTPs for this email
        self::where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Create OTP record (expires in 10 minutes)
        $otpRecord = self::create([
            'email' => $email,
            'otp' => $otp,
            'type' => $type,
            'is_used' => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send email
        try {
            Mail::raw("
Selamat datang ke BonusHub!

Kod pengesahan anda ialah: {$otp}

Kod ini akan tamat tempoh dalam 10 minit.

Jika anda tidak memohon kod ini, sila abaikan email ini.

Terima kasih,
Pasukan BonusHub
            ", function ($message) use ($email, $otp) {
                $message->to($email)
                    ->subject('BonusHub - Kod Pengesahan Email');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send OTP email: ' . $e->getMessage());
            // Don't throw - let the OTP be created but user might not receive
        }

        return $otpRecord;
    }

    /**
     * Verify OTP
     */
    public static function verify(string $email, string $otp, string $type = 'registration'): bool
    {
        $otpRecord = self::where('email', $email)
            ->where('otp', $otp)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return false;
        }

        // Mark as used
        $otpRecord->update(['is_used' => true]);

        return true;
    }

    /**
     * Check if email has a valid verified OTP
     */
    public static function isEmailVerified(string $email, string $type = 'registration'): bool
    {
        return self::where('email', $email)
            ->where('type', $type)
            ->where('is_used', true)
            ->where('created_at', '>', now()->subHour()) // Valid for 1 hour
            ->exists();
    }
}
