<?php

namespace App\Services;

use App\Models\Payment;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymobService implements PaymentServiceInterface
{
    private string $apiKey;

    private string $integrationId;

    private string $hmacSecret;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('payment.paymob.api_key');
        $this->integrationId = config('payment.paymob.integration_id');
        $this->hmacSecret = config('payment.paymob.hmac_secret');
        $this->baseUrl = config('payment.paymob.base_url', 'https://ksa.paymob.com/api');
    }

    public function initiatePayment(array $data): array
    {
        try {
            // Step 1: Get authentication token
            $authToken = $this->getAuthToken();

            // Step 2: Create order
            $order = $this->createOrder($authToken, $data);

            // Step 3: Get payment key
            $paymentKey = $this->getPaymentKey($authToken, $order['id'], $data);

            // Build payment URL
            $paymentUrl = "https://ksa.paymob.com/api/acceptance/iframes/12267?payment_token={$paymentKey}";

            return [
                'payment_url' => $paymentUrl,
                'gateway_payment_id' => (string) $order['id'],
            ];
        } catch (\Exception $e) {
            Log::error('Paymob payment initiation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new \RuntimeException('Failed to initiate payment: '.$e->getMessage());
        }
    }

    public function verifyPayment(string $transactionId): array
    {
        try {
            $authToken = $this->getAuthToken();

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$authToken}",
            ])->get("{$this->baseUrl}/ecommerce/orders/transaction/{$transactionId}");

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to verify payment');
            }

            $data = $response->json();

            return [
                'success' => $data['success'] ?? false,
                'transaction_id' => $data['id'] ?? null,
                'amount' => $data['amount_cents'] / 100 ?? null,
                'status' => $this->mapStatus($data['success'] ?? false),
            ];
        } catch (\Exception $e) {
            Log::error('Paymob payment verification failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            throw new \RuntimeException('Failed to verify payment: '.$e->getMessage());
        }
    }

    public function handleWebhook(array $payload): Payment
    {
        $payment = Payment::where('gateway_transaction_id', $payload['obj']['id'] ?? null)
            ->firstOrFail();

        $isSuccess = $payload['success'] ?? false;
        $status = $this->mapStatus($isSuccess);

        $payment->update([
            'status' => $status,
            'gateway_transaction_id' => $payload['obj']['id'] ?? $payment->gateway_transaction_id,
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook_payload' => $payload,
                'processed_at' => now()->toIso8601String(),
            ]),
        ]);

        return $payment;
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        $hmac = $payload['hmac'] ?? null;

        if (! $hmac) {
            return false;
        }

        $calculatedHmac = hash_hmac('sha512', json_encode($payload['obj'] ?? []), $this->hmacSecret);

        return hash_equals($calculatedHmac, $hmac);
    }

    private function getAuthToken(): string
    {
        $response = Http::post("{$this->baseUrl}/api/auth/tokens", [
            'api_key' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to authenticate with Paymob');
        }

        return $response->json()['token'];
    }

    private function createOrder(string $authToken, array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
        ])->post("{$this->baseUrl}/api/ecommerce/orders", [
            'delivery_needed' => false,
            'amount_cents' => (int) ($data['amount'] * 100),
            'currency' => 'SAR',
            'merchant_order_id' => (string) Str::uuid(),

        ]);
        // dd($response->json());
        if (! $response->successful()) {
            throw new \RuntimeException('Failed to create Paymob order');
        }

        return $response->json();
    }

    private function getPaymentKey(string $authToken, int $orderId, array $data): string
    {
        $billingData = [
            'first_name' => 'Omar',
            'last_name' => 'Garana',
            'email' => 'omar@email.com',
            'phone_number' => '+966562338328',
            'country' => 'SA',
            'city' => 'Riyadh',
            'street' => 'NA',
            'building' => 'NA',
            'floor' => 'NA',
            'apartment' => 'NA',
            'state' => 'Riyadh',
            'postal_code' => 'NA',
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
        ])->post("{$this->baseUrl}/api/acceptance/payment_keys", [
            'auth_token' => $authToken,
            'amount_cents' => (int) ($data['amount'] * 100),
            'expires_at' => now()->addHours(1)->timestamp,
            'order_id' => $orderId,
            'billing_data' => $billingData,
            'currency' => 'SAR',
            'integration_id' => (int) $this->integrationId,
            'lock_order_when_paid' => false,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to get Paymob payment key');
        }

        return $response->json()['token'];
    }

    private function mapStatus(bool $success): string
    {
        return $success ? 'completed' : 'failed';
    }
}
