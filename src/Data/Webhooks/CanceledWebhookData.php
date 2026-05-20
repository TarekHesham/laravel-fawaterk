<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Webhooks;

class CanceledWebhookData
{
    public function __construct(
        public readonly string $hashKey,
        public readonly string $referenceId,
        public readonly string $status,
        public readonly string $paymentMethod,
        public readonly ?string $pay_load, // pay_load can be null
        public readonly int $transactionId,
        public readonly string $transactionKey,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            hashKey: (string) $data['hashKey'],
            referenceId: (string) $data['referenceId'],
            status: (string) $data['status'],
            paymentMethod: (string) $data['paymentMethod'],
            pay_load: $data['pay_load'] ?? null,
            transactionId: (int) $data['transactionId'],
            transactionKey: (string) $data['transactionKey'],
        );
    }
}
