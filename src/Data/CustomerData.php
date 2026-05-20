<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data;

use InvalidArgumentException;

final class CustomerData
{
    /**
     * @param string $firstName Alphanumeric, @, -, _, . (Mandatory)
     * @param string $lastName Alphanumeric, @, -, _, . (Mandatory)
     * @param string|null $email (Optional)
     * @param string|null $phone (Optional)
     * @param string|null $address Alphanumeric, @, -, _, ., ,, : (Optional)
     * @param string|null $customerUniqueId (Mandatory if tokenization is used)
     */
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?string $customerUniqueId = null,
    ) {
        if (trim($this->firstName) === '') {
            throw new InvalidArgumentException('Customer first name cannot be empty.');
        }
        if (trim($this->lastName) === '') {
            throw new InvalidArgumentException('Customer last name cannot be empty.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'] ?? throw new InvalidArgumentException('Missing required field: first_name'),
            lastName: $data['last_name'] ?? throw new InvalidArgumentException('Missing required field: last_name'),
            email: $data['email'] ?? null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            address: $data['address'] ?? null,
            customerUniqueId: $data['customer_unique_id'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
        ];

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }
        if ($this->phone !== null && $this->phone !== '') {
            $data['phone'] = $this->phone;
        }
        if ($this->address !== null) {
            $data['address'] = $this->address;
        }
        if ($this->customerUniqueId !== null) {
            $data['customer_unique_id'] = $this->customerUniqueId;
        }

        return $data;
    }
}
