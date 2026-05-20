<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Webhooks;

class RefundWebhookData
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $amount, // The documentation shows this as a string.
        public readonly string $currency,
        public readonly string $status,
        public readonly ?string $reason, // Optional
        public readonly string $approvedAt,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            transactionId: (string) $data['transactionId'],
            amount: (string) $data['amount'],
            currency: (string) $data['currency'],
            status: (string) $data['status'],
            reason: $data['reason'] ?? null,
            approvedAt: (string) $data['approvedAt'],
        );
    }
}
