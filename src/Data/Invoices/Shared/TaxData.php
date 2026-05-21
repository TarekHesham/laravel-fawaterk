<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Invoices\Shared;

use InvalidArgumentException;

final class TaxData
{
    public function __construct(
        public readonly string $title,
        public readonly float $value,
    ) {
        if (trim($this->title) === '') {
            throw new InvalidArgumentException('Tax title cannot be empty.');
        }
        if ($this->value < 0) {
            throw new InvalidArgumentException('Tax value cannot be negative.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? throw new InvalidArgumentException('Missing required field: title'),
            value: (float) ($data['value'] ?? throw new InvalidArgumentException('Missing required field: value')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'value' => $this->value,
        ];
    }
}
