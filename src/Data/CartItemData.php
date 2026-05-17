<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data;

use InvalidArgumentException;

final class CartItemData
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $quantity,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Cart item name cannot be empty.');
        }
        if ($this->price < 0) {
            throw new InvalidArgumentException('Cart item price cannot be negative.');
        }
        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('Cart item quantity must be greater than zero.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? throw new InvalidArgumentException('Missing required field: name'),
            price: (float) ($data['price'] ?? throw new InvalidArgumentException('Missing required field: price')),
            quantity: (int) ($data['quantity'] ?? throw new InvalidArgumentException('Missing required field: quantity')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
        ];
    }
}
