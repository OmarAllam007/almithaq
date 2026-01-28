<?php

namespace App\Services\Contracts;

use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Initiate a payment session with the gateway
     *
     * @param  array  $data  Payment data including amount, currency, user_id, etc.
     * @return array Returns payment URL and gateway payment ID
     */
    public function initiatePayment(array $data): array;

    /**
     * Verify a payment transaction
     *
     * @param  string  $transactionId  Gateway transaction ID
     * @return array Payment verification data
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Handle webhook callback from gateway
     *
     * @param  array  $payload  Webhook payload
     */
    public function handleWebhook(array $payload): Payment;

    /**
     * Verify webhook signature
     *
     * @param  array  $payload  Webhook payload
     * @param  string  $signature  Webhook signature
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool;
}
