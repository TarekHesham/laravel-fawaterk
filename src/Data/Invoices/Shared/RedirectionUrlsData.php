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
            successUrl: $data['successUrl'] ?? null,
            failUrl: $data['failUrl'] ?? null,
            pendingUrl: $data['pendingUrl'] ?? null,
            webhookUrl: $data['webhookUrl'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->successUrl !== null) {
            $data['successUrl'] = $this->successUrl;
        }
        if ($this->failUrl !== null) {
            $data['failUrl'] = $this->failUrl;
        }
        if ($this->pendingUrl !== null) {
            $data['pendingUrl'] = $this->pendingUrl;
        }
        if ($this->webhookUrl !== null) {
            $data['webhookUrl'] = $this->webhookUrl;
        }

        return $data;
    }
}
