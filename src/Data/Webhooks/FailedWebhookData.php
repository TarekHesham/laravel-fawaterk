<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Webhooks;

class FailedWebhookData
{
    public function __construct(
        public readonly int $invoice_id,
        public readonly string $invoice_key,
        public readonly string $payment_method,
        public readonly ?string $pay_load,
        public readonly float $amount,
        public readonly string $paidCurrency, // Changed from currency to paidCurrency
        public readonly string $errorMessage,
        public readonly array $response, // As seen in docs, it's an array
        public readonly string $referenceNumber,
        public readonly string $hashKey,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            invoice_id: (int) $data['invoice_id'],
            invoice_key: (string) $data['invoice_key'],
            payment_method: (string) $data['payment_method'],
            pay_load: $data['pay_load'] ?? null,
            amount: (float) $data['amount'],
            paidCurrency: (string) $data['paidCurrency'], // Changed from currency to paidCurrency
            errorMessage: (string) $data['errorMessage'],
            response: (array) $data['response'],
            referenceNumber: (string) $data['referenceNumber'],
            hashKey: (string) $data['hashKey'],
        );
    }
}
