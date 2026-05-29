<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Invoices\Shared;

final class RedirectionUrlsData
{
    public function __construct(
        public readonly ?string $successUrl = null,
        public readonly ?string $failUrl = null,
        public readonly ?string $pendingUrl = null,
        public readonly ?string $webhookUrl = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['successUrl'] ?? null,
            $data['failUrl'] ?? null,
            $data['pendingUrl'] ?? null,
            $data['webhookUrl'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'successUrl' => $this->successUrl,
            'failUrl'    => $this->failUrl,
            'pendingUrl' => $this->pendingUrl,
            'webhookUrl' => $this->webhookUrl,
        ], fn($value) => $value !== null);
    }
}
