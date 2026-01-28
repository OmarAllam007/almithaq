<?php

namespace App\Services;

use App\Models\Payment;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyFatoorahService implements PaymentServiceInterface
{
    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('payment.myfatoorah.api_key');
        $this->baseUrl = config('payment.myfatoorah.base_url', 'https://api.myfatoorah.com');
    }

    public function initiatePayment(array $data): array
    {
        try {
            $invoiceData = [
                'InvoiceAmount' => $data['amount'],
                'CurrencyIso' => $data['currency'] ?? 'USD',
                'CustomerName' => $data['user_name'] ?? 'Customer',
                'CustomerEmail' => $data['user_email'] ?? 'customer@example.com',
                'CustomerMobile' => $data['user_phone'] ?? '+201000000000',
                'CallBackUrl' => $data['callback_url'],
                'ErrorUrl' => $data['error_url'] ?? $data['callback_url'],
                'Language' => 'en',
                'DisplayCurrencyIso' => $data['currency'] ?? 'USD',
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/v2/ExecutePayment", $invoiceData);

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to initiate MyFatoorah payment');
            }

            $result = $response->json();

            if (! ($result['IsSuccess'] ?? false)) {
                throw new \RuntimeException($result['Message'] ?? 'Payment initiation failed');
            }

            $paymentUrl = $result['Data']['PaymentURL'] ?? null;
            $invoiceId = $result['Data']['InvoiceId'] ?? null;

            if (! $paymentUrl || ! $invoiceId) {
                throw new \RuntimeException('Invalid response from MyFatoorah');
            }

            return [
                'payment_url' => $paymentUrl,
                'gateway_payment_id' => (string) $invoiceId,
            ];
        } catch (\Exception $e) {
            Log::error('MyFatoorah payment initiation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new \RuntimeException('Failed to initiate payment: '.$e->getMessage());
        }
    }

    public function verifyPayment(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/v2/GetPaymentStatus", [
                'Key' => $transactionId,
                'KeyType' => 'PaymentId',
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to verify MyFatoorah payment');
            }

            $result = $response->json();

            if (! ($result['IsSuccess'] ?? false)) {
                throw new \RuntimeException($result['Message'] ?? 'Payment verification failed');
            }

            $invoice = $result['Data']['InvoiceTransactions'][0] ?? null;

            if (! $invoice) {
                throw new \RuntimeException('Invalid payment data');
            }

            $isSuccess = ($invoice['TransactionStatus'] ?? '') === 'SUCCESS';

            return [
                'success' => $isSuccess,
                'transaction_id' => $invoice['TransactionId'] ?? null,
                'amount' => $invoice['PaidCurrencyValue'] ?? null,
                'status' => $this->mapStatus($isSuccess),
            ];
        } catch (\Exception $e) {
            Log::error('MyFatoorah payment verification failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            throw new \RuntimeException('Failed to verify payment: '.$e->getMessage());
        }
    }

    public function handleWebhook(array $payload): Payment
    {
        $paymentId = $payload['Data']['InvoiceId'] ?? null;

        $payment = Payment::where('gateway_payment_id', $paymentId)
            ->firstOrFail();

        $transactionStatus = $payload['Data']['InvoiceTransactions'][0]['TransactionStatus'] ?? null;
        $isSuccess = $transactionStatus === 'SUCCESS';
        $status = $this->mapStatus($isSuccess);

        $payment->update([
            'status' => $status,
            'gateway_transaction_id' => $payload['Data']['InvoiceTransactions'][0]['TransactionId'] ?? $payment->gateway_transaction_id,
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook_payload' => $payload,
                'processed_at' => now()->toIso8601String(),
            ]),
        ]);

        return $payment;
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        // MyFatoorah uses HMAC SHA256 for webhook verification
        $data = $payload['Data'] ?? [];
        $calculatedSignature = hash_hmac('sha256', json_encode($data), $this->apiKey);

        return hash_equals($calculatedSignature, $signature);
    }

    private function mapStatus(bool $success): string
    {
        return $success ? 'completed' : 'failed';
    }
}
