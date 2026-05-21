<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Invoices\Shared;

use InvalidArgumentException;

final class DiscountData
{
    public function __construct(
        public readonly string $type,
        public readonly float $value,
    ) {
        if (! in_array($this->type, ['pcg', 'literal'], true)) {
            throw new InvalidArgumentException('Discount type must be either "pcg" or "literal".');
        }
        if ($this->value < 0) {
            throw new InvalidArgumentException('Discount value cannot be negative.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? throw new InvalidArgumentException('Missing required field: type'),
            value: (float) ($data['value'] ?? throw new InvalidArgumentException('Missing required field: value')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
        ];
    }
}
