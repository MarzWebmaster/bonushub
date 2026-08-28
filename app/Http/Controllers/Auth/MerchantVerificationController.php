<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MerchantVerificationController extends Controller
{
    /**
     * Show verification page (upload IC & SSM)
     */
    public function showVerificationForm()
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return redirect()->route('merchant.register')
                ->with('error', 'Akaun merchant tidak dijumpai.');
        }

        return view('auth.merchant-verify', compact('merchant'));
    }

    /**
     * Upload documents (IC & SSM) + PDPA consent
     */
    public function uploadDocuments(Request $request)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return back()->with('error', 'Akaun merchant tidak dijumpai.');
        }

        // ── VALIDATION ─────────────────────
        $validated = $request->validate([
            'ic_image'    => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'ssm_image'   => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'consent_pdpa' => 'required|accepted',
        ], [
            'ic_image.required'   => 'Sila muat naik gambar IC.',
            'ssm_image.required'  => 'Sila muat naik gambar SSM.',
            'consent_pdpa.accepted' => 'Anda perlu bersetuju dengan Dasar Perlindungan Data Peribadi (PDPA).',
        ]);

        // ── STORE FILES ──────────────────────
        try {
            // Store IC image
            $icPath = $request->file('ic_image')->store('verification/ic', 'public');

            // Store SSM image
            $ssmPath = $request->file('ssm_image')->store('verification/ssm', 'public');

            // Update merchant
            $merchant->update([
                'ic_image'     => $icPath,
                'ssm_image'    => $ssmPath,
                'consent_pdpa' => true,
                'status'       => Merchant::STATUS_PENDING, // Still pending until superadmin approves
            ]);

            Log::info("Merchant documents uploaded: {$merchant->company_name}", [
                'merchant_id' => $merchant->id,
                'ic_path'     => $icPath,
                'ssm_path'    => $ssmPath,
            ]);

            return redirect()->route('merchant.dashboard')
                ->with('success', '📄 Dokumen berjaya dihantar! Permintaan anda sedang menunggu pengesahan admin.');

        } catch (\Exception $e) {
            Log::error("Merchant document upload failed: " . $e->getMessage(), [
                'merchant_id' => $merchant->id,
            ]);

            return back()->withInput()
                ->with('error', 'Gagal memuat naik dokumen. Sila cuba lagi.');
        }
    }

    /**
     * Skip verification (merchant can still access limited features)
     */
    public function skipVerification()
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return redirect()->route('merchant.register');
        }

        $merchant->update([
            'consent_pdpa' => false,
            'status'       => Merchant::STATUS_PENDING,
        ]);

        Log::info("Merchant skipped verification: {$merchant->company_name}", [
            'merchant_id' => $merchant->id,
        ]);

        return redirect()->route('merchant.dashboard')
            ->with('warning', '⚠️ Pengesahan dilangkau. Anda boleh mengedit profil sahaja sehingga dokumen dimuat naik dan disahkan.');
    }
}
