<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Services;

use ElFarmawy\Fawaterk\Data\Webhooks\CanceledWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\FailedWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\PaidWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\RefundWebhookData;
use ElFarmawy\Fawaterk\Exceptions\WebhookSignatureVerificationException;
use Illuminate\Support\Facades\Log;

class FawaterkWebhookService
{
    protected string $vendorKey;

    public function __construct()
    {
        $this->vendorKey = config('fawaterk.vendor_key');
    }

    public function verifySignature(array $payload, string $webhookType): bool
    {
        // Implement signature verification logic here
        // The expected queryParams for HMAC SHA256 vary by webhookType
        // Example for 'paid': invoice_id, invoice_key, payment_method, amount, currency, customer_name, customer_email, status

        $hashKey = $payload['hashKey'] ?? null;
        if (! $hashKey) {
            return false;
        }

        $expectedHash = $this->generateExpectedHash($payload, $webhookType);

        return hash_equals($expectedHash, $hashKey);
    }

    public function parse(array $payload, string $webhookType): PaidWebhookData|CanceledWebhookData|FailedWebhookData|RefundWebhookData
    {
        if (! $this->verifySignature($payload, $webhookType)) {
            throw new WebhookSignatureVerificationException('Invalid webhook signature.');
        }

        return match ($webhookType) {
            'paid' => PaidWebhookData::fromArray($payload),
            'canceled' => CanceledWebhookData::fromArray($payload),
            'failed' => FailedWebhookData::fromArray($payload),
            'refund' => RefundWebhookData::fromArray($payload),
            default => throw new \InvalidArgumentException('Unknown webhook type.'),
        };
    }

    public function safeVerifySignature(array $payload, string $webhookType): bool
    {
        try {
            return $this->verifySignature($payload, $webhookType);
        } catch (\Throwable $e) {
            Log::error('Fawaterk webhook signature verification failed: '.$e->getMessage(), ['exception' => $e]);

            return false;
        }
    }

    protected function generateExpectedHash(array $payload, string $webhookType): string
    {
        $queryParam = '';
        switch ($webhookType) {
            case 'paid':
            case 'failed':
                // Documentation example: InvoiceId=response.invoice_id&InvoiceKey=response.invoice_key&PaymentMethod=response.payment_method
                $queryParam = sprintf(
                    'InvoiceId=%s&InvoiceKey=%s&PaymentMethod=%s',
                    $payload['invoice_id'] ?? '',
                    $payload['invoice_key'] ?? '',
                    $payload['payment_method'] ?? ''
                );
                break;
            case 'canceled':
                // Documentation example: referenceId=response.referenceId&PaymentMethod=response.paymentMethod
                $queryParam = sprintf(
                    'referenceId=%s&PaymentMethod=%s',
                    $payload['referenceId'] ?? '',
                    $payload['paymentMethod'] ?? ''
                );
                break;
            case 'refund':
                // Hash key generation is not documented for refund webhooks.
                // According to web-hook.md, the refund payload does not include hashKey.
                // Thus, no hash can be generated or verified.
                return '';
            default:
                throw new \InvalidArgumentException('Unknown webhook type for hash generation: '.$webhookType);
        }

        return hash_hmac('sha256', $queryParam, $this->vendorKey, false);
    }
}
