<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Webhooks;

class PaidWebhookData
{
    public function __construct(
        public readonly int $invoice_id,
        public readonly string $invoice_key,
        public readonly string $payment_method,
        public readonly string $invoice_status,
        public readonly float $paidAmount,
        public readonly string $paidCurrency,
        public readonly ?string $referenceNumber,
        public readonly string $hashKey,
        public readonly ?array $customerData,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            invoice_id: (int) $data['invoice_id'],
            invoice_key: (string) $data['invoice_key'],
            payment_method: (string) $data['payment_method'],
            invoice_status: (string) $data['invoice_status'],
            paidAmount: (float) ($data['paidAmount'] ?? 0),
            paidCurrency: (string) ($data['paidCurrency'] ?? ''),
            referenceNumber: $data['referenceNumber'] ?? null,
            hashKey: (string) $data['hashKey'],
            customerData: $data['customerData'] ?? null,
        );
    }
}
