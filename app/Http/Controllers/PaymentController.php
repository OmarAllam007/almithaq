<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Enums\PaymentGateway;
use App\Models\Enums\PaymentMethod;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\SubscriptionPlanPrice;
use App\Models\Enums\SubscriptionStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function create(Request $request)
    {
        $planPrices = SubscriptionPlanPrice::getAllValues();
        $planDuration = (int) $request->query('plan_duration', 1);
        $amount = (float) $request->query('amount', $planPrices[$planDuration]);

        // Validate plan_duration is valid
        if (! in_array($planDuration, [1, 3, 6, 12], true)) {
            return redirect()->route('subscription.index')
                ->with('error', 'Invalid subscription plan selected.');
        }

        // Validate amount matches the plan_duration
        $expectedAmount = $planPrices[$planDuration] ?? null;
        if ($expectedAmount === null || abs($amount - $expectedAmount) > 0.01) {
            return redirect()->route('subscription.index')
                ->with('error', 'Invalid payment amount for selected plan.');
        }

        return Inertia::render('Payment/Index', [
            'payment' => [
                'id' => null,
                'amount' => $amount,
                'currency' => config('payment.currency', 'USD'),
                'subscription' => [
                    'plan_duration' => $planDuration,
                    'price' => $amount,
                ],
                'plan_duration' => $planDuration,
            ],
        ]);
    }

    public function show(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        if ($payment->isSuccessful()) {
            return redirect()->route('subscription.index')
                ->with('success', 'Payment already completed');
        }

        return Inertia::render('Payment/Index', [
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'subscription' => $payment->subscription ? [
                    'plan_duration' => $payment->subscription->plan_duration,
                    'price' => $payment->subscription->price,
                ] : null,
            ],
        ]);
    }

    public function process(ProcessPaymentRequest $request, ?Payment $payment = null)
    {
        $planPrices = SubscriptionPlanPrice::getAllValues();

        $planDuration = (int) $request->plan_duration;

        // Validate plan_duration is valid
        if (! in_array($planDuration, [1, 3, 6, 12], true)) {
            return redirect()->route('subscription.index')
                ->with('error', 'Invalid subscription plan selected.');
        }

        $price = $planPrices[$planDuration];

        // If payment exists, verify ownership
        if ($payment) {
            if ($payment->user_id !== auth()->id()) {
                abort(403);
            }

            if ($payment->isSuccessful()) {
                return redirect()->route('subscription.index')
                    ->with('success', 'Payment already completed');
            }
        } else {
            // Check if payment already exists for this user with same plan (pending)
            $existingPayment = Payment::where('user_id', auth()->id())
                ->where('status', PaymentStatus::PENDING)
                ->whereHas('subscription', function ($query) use ($planDuration) {
                    $query->where('plan_duration', $planDuration)
                        ->where('status', SubscriptionStatus::PENDING);
                })
                ->first();

            if ($existingPayment) {
                $payment = $existingPayment;
            } else {
                try {
                    DB::beginTransaction();

                    // Create subscription
                    $subscription = Subscription::create([
                        'user_id' => auth()->id(),
                        'plan_duration' => $planDuration,
                        'price' => $price,
                        'status' => SubscriptionStatus::PENDING,
                    ]);

                    // Create payment
                    $payment = Payment::create([
                        'user_id' => auth()->id(),
                        'subscription_id' => $subscription->id,
                        'amount' => $price,
                        'currency' => config('payment.currency', 'USD'),
                        'gateway' => PaymentGateway::from(config('payment.default_gateway', 'paymob')),
                        'status' => PaymentStatus::PENDING,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    return back()->withErrors([
                        'payment' => 'Failed to create payment. Please try again.',
                    ]);
                }
            }
        }

        // Update payment method
        $payment->update([
            'payment_method' => PaymentMethod::from($request->payment_method),
            'metadata' => array_merge($payment->metadata ?? [], [
                'payment_method_selected' => $request->payment_method,
                'initiated_at' => now()->toIso8601String(),
            ]),
        ]);

        try {
            $gateway = PaymentService::getGateway();

            $paymentData = [
                'amount' => $payment->amount,
                'currency' => 'SAR',
                'user_id' => $payment->user_id,
                'user_name' => auth()->user()->name ?? auth()->user()->full_name,
                'user_email' => auth()->user()->email,
                'user_phone' => auth()->user()->phone_number,
                'callback_url' => route('payment.callback', [], true),
                'error_url' => route('payment.callback', [], true),
                'order_id' => "payment_{$payment->id}",
            ];

            $result = $gateway->initiatePayment($paymentData);

            $payment->update([
                'gateway_payment_id' => $result['gateway_payment_id'],
            ]);

            return response()->json([
                'url' => $result['payment_url'],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'payment' => 'Failed to process payment. Please try again.',
            ]);
        }
    }

    public function callback(Request $request, ?Payment $payment = null)
    {
        // Get callback data from request (query params or POST)
        $callbackData = $request->all();

        // Find payment by order ID from callback data
        $orderId = $callbackData['order'] ?? null;

        if (! $orderId) {
            return redirect()->route('subscription.index')
                ->with('error', 'Invalid payment callback.');
        }

        // Find payment by gateway_payment_id (which stores the Paymob order ID)
        $payment = Payment::where('gateway_payment_id', $orderId)->first();

        if (! $payment) {
            return redirect()->route('subscription.index')
                ->with('error', 'Payment not found.');
        }

        // Verify payment belongs to current user
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if payment is already processed
        if ($payment->isSuccessful()) {
            return redirect()->route('subscription.index')
                ->with('success', 'Payment already completed.');
        }

        // Get success status from callback (Paymob sends "true" or "false" as string)
        $success = $callbackData['success'] ?? 'false';
        $isSuccess = $success === 'true' || $success === true;

        if ($isSuccess && $payment->isPending()) {
            // Update payment status
            $payment->update([
                'status' => PaymentStatus::COMPLETED,
                'gateway_transaction_id' => $callbackData['id'] ?? $payment->gateway_transaction_id,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'callback_data' => $callbackData,
                    'processed_at' => now()->toIso8601String(),
                ]),
            ]);

            // Activate subscription if payment is successful
            if ($payment->subscription) {
                $payment->subscription->update([
                    'status' => \App\Models\Enums\SubscriptionStatus::ACTIVE,
                    'starts_at' => now(),
                    'expires_at' => now()->addMonths($payment->subscription->plan_duration),
                ]);

                $this->notificationService->notifySubscriptionRenewed($payment->user_id);
            }

            return redirect()->route('subscription.index')
                ->with('success', 'Payment completed successfully!');
        }

        // Payment failed
        $payment->update([
            'status' => PaymentStatus::FAILED,
            'metadata' => array_merge($payment->metadata ?? [], [
                'callback_data' => $callbackData,
                'processed_at' => now()->toIso8601String(),
            ]),
        ]);

        return redirect()->route('subscription.index')
            ->with('error', 'Payment failed. Please try again.');
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature') ?? $request->header('Signature') ?? '';

        try {
            $gateway = PaymentService::getGateway();

            // Verify webhook signature
            if (! $gateway->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Invalid webhook signature', [
                    'payload' => $payload,
                ]);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Handle webhook
            $payment = $gateway->handleWebhook($payload);

            // Activate subscription if payment is successful
            if ($payment->isSuccessful() && $payment->subscription) {
                $payment->subscription->update([
                    'status' => \App\Models\Enums\SubscriptionStatus::ACTIVE,
                    'starts_at' => now(),
                    'expires_at' => now()->addMonths($payment->subscription->plan_duration),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }
}
