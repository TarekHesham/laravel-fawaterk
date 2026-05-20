<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Gateway;

class DeleteTokenRequest
{
    public function __construct(
        public readonly string $customerUniqueId,
        public readonly string $cardTokenUniqueId
    ) {}

    public function toArray(): array
    {
        return [
            'customerUniqueId' => $this->customerUniqueId,
            'cardTokenUniqueId' => $this->cardTokenUniqueId,
        ];
    }
}
