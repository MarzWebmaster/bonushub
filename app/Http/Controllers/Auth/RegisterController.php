<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CampaignLink;
use App\Models\Customer;
use App\Models\CustomerMerchant;
use App\Models\User;
use App\Models\VerificationOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function showRegisterForm(Request $request)
    {
        $ref = $request->query('ref');
        $campaign = null;

        if ($ref) {
            $campaign = CampaignLink::where('slug', $ref)->first();
        }

        return view('auth.register', compact('ref', 'campaign'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'ref'      => 'nullable|string',
            'otp'      => 'required|string|size:6',
        ]);

        // Verify OTP first
        $otpVerified = VerificationOtp::verify($validated['email'], $validated['otp'], 'registration');
        if (!$otpVerified) {
            return back()->withErrors([
                'otp' => 'Kod pengesahan tidak sah atau telah tamat tempoh.',
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            // 1. Create User with Customer role
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status'   => 'active',
            ]);

            $user->assignRole('Customer');

            // 2. Create Customer record
            $customer = Customer::create([
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'phone'         => $validated['phone'],
                'password'      => Hash::make($validated['password']),
                'tier_global'   => 'Basic',
                'registered_at' => now(),
            ]);

            // 3. If campaign ref exists, tie to merchant
            $campaign = null;
            if (!empty($validated['ref'])) {
                $campaign = CampaignLink::where('slug', $validated['ref'])
                    ->where('status', 'active')
                    ->first();

                if ($campaign) {
                    // Check if already tied
                    $exists = CustomerMerchant::where('customer_id', $customer->id)
                        ->where('merchant_id', $campaign->merchant_id)
                        ->exists();

                    if (!$exists) {
                        CustomerMerchant::create([
                            'customer_id'        => $customer->id,
                            'merchant_id'        => $campaign->merchant_id,
                            'points'             => 0,
                            'tier_per_merchant'  => 'Basic',
                            'tied_at'            => now(),
                            'campaign_link_id'   => $campaign->id,
                        ]);

                        // Track registration on campaign
                        $campaign->recordRegistration();
                    }
                }
            }

            DB::commit();

            // Log in the user
            auth()->login($user);

            Log::info("New customer registered: {$validated['email']}" . ($campaign ? " via campaign {$campaign->slug}" : ''));

            return redirect()->route('customer.dashboard')
                ->with('success', 'Welcome to BonusHub!' . ($campaign ? " 🎉 You've been enrolled in {$campaign->merchant->company_name}'s loyalty program!" : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ])->withInput();
        }
    }
}
