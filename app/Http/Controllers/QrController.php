<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Common\EccLevel;
use Illuminate\Http\Request;

class QrController extends Controller
{
    /**
     * Show QR code page for staff.
     */
    public function staffQrPage()
    {
        $user = auth()->user();
        $merchant = $user->merchant;
        $branches = $merchant ? $merchant->branches()->where('status', 'active')->get() : collect();

        return view('staff.qr', compact('merchant', 'branches'));
    }

    /**
     * Generate QR code image as base64.
     */
    public function generateQr(Request $request)
    {
        $user = auth()->user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json(['success' => false, 'message' => 'No merchant assigned.'], 403);
        }

        $branchId = $request->branch_id;
        $branch = null;
        if ($branchId) {
            $branch = Branch::where('id', $branchId)
                ->where('merchant_id', $merchant->id)
                ->where('status', 'active')
                ->first();
            if (!$branch) {
                return response()->json(['success' => false, 'message' => 'Branch not found.'], 404);
            }
        }

        // Build scan URL
        $url = url("/scan/{$merchant->id}" . ($branchId ? "/{$branchId}" : ''));

        try {
            // Generate QR code using v6 API
            $options = new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'eccLevel' => EccLevel::M,
                'scale' => 10,
                'returnResource' => false,
            ]);

            $qr = new QRCode($options);
            $imageData = $qr->render($url);

            return response()->json([
                'success' => true,
                'qr_image' => 'data:image/png;base64,' . base64_encode($imageData),
                'url' => $url,
                'merchant' => $merchant->company_name,
                'branch' => $branch->name ?? 'All Branches',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('QR generation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate QR code.'], 500);
        }
    }
}
