<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Webhooks;

class PaidWebhookData
{
    public function __construct(
        public readonly int $invoice_id,
        public readonly string $invoice_key,
        public readonly string $payment_method,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $customer_name,
        public readonly string $customer_email,
        public readonly string $status,
        public readonly string $hashKey,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            invoice_id: (int) $data['invoice_id'],
            invoice_key: (string) $data['invoice_key'],
            payment_method: (string) $data['payment_method'],
            amount: (float) $data['amount'],
            currency: (string) $data['currency'],
            customer_name: (string) $data['customer_name'],
            customer_email: (string) $data['customer_email'],
            status: (string) $data['status'],
            hashKey: (string) $data['hashKey'],
        );
    }
}
