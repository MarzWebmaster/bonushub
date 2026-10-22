<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\ApiErrorException;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Show checkout page for a package
     */
    public function checkout(Package $package)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return redirect()->route('merchant.dashboard')->with('error', 'Merchant not found.');
        }

        // Check if already on this package
        if ($merchant->package_id === $package->id) {
            return redirect()->route('merchant.dashboard')->with('info', 'You are already on this package.');
        }

        return view('checkout', compact('package', 'merchant'));
    }

    /**
     * Create Stripe Checkout Session
     */
    public function createSession(Request $request, Package $package)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'Merchant not found'], 404);
        }

        try {
            // Create payment record
            $payment = Payment::create([
                'merchant_id' => $merchant->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'currency' => 'myr',
                'status' => 'pending',
                'type' => 'subscription',
                'description' => "BonusHub {$package->name} Package",
            ]);

            // Create Stripe Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card', 'fpx'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'myr',
                        'product_data' => [
                            'name' => "BonusHub {$package->name} Package",
                            'description' => "{$package->name} plan - Branch limit: " . ($package->branch_limit == -1 ? 'Unlimited' : $package->branch_limit) . ", Staff: " . ($package->staff_limit == -1 ? 'Unlimited' : $package->staff_limit),
                        ],
                        'unit_amount' => (int) ($package->price * 100), // Stripe uses cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'customer_email' => Auth::user()->email,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'merchant_id' => $merchant->id,
                    'package_id' => $package->id,
                ],
            ]);

            // Save session ID
            $payment->update(['stripe_checkout_session_id' => $session->id]);

            return redirect($session->url);

        } catch (ApiErrorException $e) {
            Log::error('Stripe session creation failed: ' . $e->getMessage());
            return back()->with('error', 'Payment failed. Please try again.');
        }
    }

    /**
     * Payment success callback
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('merchant.dashboard');
        }

        try {
            $session = Session::retrieve($sessionId);
            $payment = Payment::where('stripe_checkout_session_id', $sessionId)->first();

            if ($payment && $payment->status === 'succeeded') {
                return view('payment-success', compact('payment'));
            }

            // If webhook hasn't processed yet, check session status
            if ($session->payment_status === 'paid' && $payment) {
                $this->fulfillPayment($payment, $session);
                return view('payment-success', compact('payment'));
            }

            return view('payment-success', ['payment' => $payment]);

        } catch (\Exception $e) {
            Log::error('Payment success page error: ' . $e->getMessage());
            return redirect()->route('merchant.dashboard')->with('error', 'Could not verify payment.');
        }
    }

    /**
     * Payment cancel callback
     */
    public function cancel()
    {
        return view('payment-cancel');
    }

    /**
     * Stripe Webhook handler
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (!$secret) {
            Log::warning('Stripe webhook secret not configured');
            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $payment = Payment::where('stripe_checkout_session_id', $session->id)->first();

            if ($payment) {
                $this->fulfillPayment($payment, $session);
            }
        }

        return response('OK', 200);
    }

    /**
     * Fulfill payment - upgrade merchant package
     */
    private function fulfillPayment(Payment $payment, $session): void
    {
        if ($payment->status === 'succeeded') {
            return; // Already fulfilled
        }

        $payment->update([
            'status' => 'succeeded',
            'stripe_payment_intent_id' => $session->payment_intent,
            'paid_at' => now(),
        ]);

        // Upgrade merchant package
        $merchant = $payment->merchant;
        if ($merchant && $payment->package_id) {
            $merchant->update([
                'package_id' => $payment->package_id,
                'subscription_expiry' => now()->addYear(), // 1 year subscription
                'status' => 'active',
            ]);

            Log::info("Merchant {$merchant->company_name} upgraded to package #{$payment->package_id}");
        }
    }

    /**
     * Show payment history for merchant
     */
    public function history()
    {
        $merchant = Auth::user()->merchant;
        abort_unless($merchant, 403, 'Hanya akaun merchant boleh melihat sejarah pembayaran.');
        $payments = Payment::where('merchant_id', $merchant->id)
            ->with('package')
            ->latest()
            ->paginate(10);

        return view('payment-history', compact('payments'));
    }

    /**
     * Get Stripe publishable key for frontend
     */
    public function config()
    {
        return response()->json([
            'publishable_key' => config('services.stripe.key'),
        ]);
    }
}