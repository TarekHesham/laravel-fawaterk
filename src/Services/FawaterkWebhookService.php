<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Services;

use ElFarmawy\Fawaterk\Data\Webhooks\CanceledWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\FailedWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\PaidWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\RefundWebhookData;
use ElFarmawy\Fawaterk\Enums\WebhookType;
use ElFarmawy\Fawaterk\Exceptions\WebhookSignatureVerificationException;
use Illuminate\Support\Facades\Log;

class FawaterkWebhookService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('fawaterk.api_key');
    }

    public function verifySignature(array $payload, WebhookType $webhookType): bool
    {
        $hashKey = $payload['hashKey'] ?? null;
        if (! $hashKey) {
            return false;
        }

        $expectedHash = $this->generateExpectedHash($payload, $webhookType);

        return hash_equals($expectedHash, $hashKey);
    }

    public function verifyAndParse(array $payload, WebhookType $webhookType): PaidWebhookData|CanceledWebhookData|FailedWebhookData|RefundWebhookData
    {
        if (! $this->verifySignature($payload, $webhookType)) {
            throw new WebhookSignatureVerificationException('Invalid webhook signature.');
        }

        return match ($webhookType) {
            WebhookType::PAID     => PaidWebhookData::fromArray($payload),
            WebhookType::CANCELED => CanceledWebhookData::fromArray($payload),
            WebhookType::FAILED   => FailedWebhookData::fromArray($payload),
            WebhookType::REFUND   => RefundWebhookData::fromArray($payload),
            default => throw new \InvalidArgumentException('Unknown webhook type.'),
        };
    }

    public function safeVerifySignature(array $payload, WebhookType $webhookType): bool
    {
        try {
            return $this->verifySignature($payload, $webhookType);
        } catch (\Throwable $e) {
            Log::error('Fawaterk webhook signature verification failed: ' . $e->getMessage(), ['exception' => $e]);

            return false;
        }
    }

    /**
     * Generate expected webhook hash per Fawaterk documentation.
     * 
     * Hash generation rules:
     * 1. Extract fields in the order specified for each webhook type
     * 2. Format as URL query string (field=value&field2=value2)
     * 3. Handle null/empty values as empty strings
     * 4. Use HMAC-SHA256 with API key
     * 
     * @see https://fawaterk.com/docs/webhooks/verification or whatever the docs link is
     * @throws InvalidArgumentException If webhook type is unknown or not supported
     */
    protected function generateExpectedHash(array $payload, WebhookType $webhookType): string
    {
        $queryParam = '';

        switch ($webhookType) {
            case WebhookType::PAID:
            case WebhookType::FAILED:
                // Documentation example: InvoiceId=response.invoice_id&InvoiceKey=response.invoice_key&PaymentMethod=response.payment_method
                $queryParam = sprintf(
                    'InvoiceId=%s&InvoiceKey=%s&PaymentMethod=%s',
                    $payload['invoice_id'] ?? '',
                    $payload['invoice_key'] ?? '',
                    $payload['payment_method'] ?? ''
                );
                break;
            case WebhookType::CANCELED:
                // Documentation example: referenceId=response.referenceId&PaymentMethod=response.paymentMethod
                $queryParam = sprintf(
                    'referenceId=%s&PaymentMethod=%s',
                    $payload['referenceId'] ?? '',
                    $payload['paymentMethod'] ?? ''
                );
                break;
            case WebhookType::REFUND:
                // SECURITY FIX: Refund webhook signature verification is not documented.
                // Contact Fawaterk support to obtain the correct hash generation algorithm.
                // Until then, reject all refund webhooks to prevent spoofing attacks.
                throw new \InvalidArgumentException(
                    'Refund webhook signature verification is not yet implemented. '
                    . 'Please contact Fawaterk support for hash generation documentation '
                    . 'or update the SDK once the algorithm is known.'
                );
            default:
                throw new \InvalidArgumentException('Unknown webhook type for hash generation: ' . $webhookType->value);
        }

        return hash_hmac('sha256', $queryParam, $this->apiKey, false);
    }
}
