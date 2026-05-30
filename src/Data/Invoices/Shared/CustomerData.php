<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Invoices\Shared;

use InvalidArgumentException;

final class CustomerData
{
    /**
     * @param string $firstName Alphanumeric, @, -, _, . (Mandatory)
     * @param string $lastName Alphanumeric, @, -, _, . (Mandatory)
     * @param string|null $email (Optional)
     * @param string|null $phone (Optional - Mandatory in case of Mobile Wallet payment method presented)
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
        $pattern = '/^[a-zA-Z0-9@\-_.]+$/';

        if (trim($this->firstName) === '') {
            throw new InvalidArgumentException('Customer first name cannot be empty.');
        }
        if (!preg_match($pattern, $this->firstName)) {
            // SECURITY FIX: Log field metadata instead of PII
            \Illuminate\Support\Facades\Log::warning(
                'Customer first name contains characters outside of expected range (length: ' . 
                strlen($this->firstName) . ' characters)'
            );
        }

        if (trim($this->lastName) === '') {
            throw new InvalidArgumentException('Customer last name cannot be empty.');
        }
        if (!preg_match($pattern, $this->lastName)) {
            // SECURITY FIX: Log field metadata instead of PII
            \Illuminate\Support\Facades\Log::warning(
                'Customer last name contains characters outside of expected range (length: ' . 
                strlen($this->lastName) . ' characters)'
            );
        }

        // SECURITY FIX: Validate email format
        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address format provided.');
        }
    }


    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['first_name'] ?? throw new InvalidArgumentException('Missing required field: first_name'),
            $data['last_name'] ?? throw new InvalidArgumentException('Missing required field: last_name'),
            $data['email'] ?? null,
            isset($data['phone']) ? (string) $data['phone'] : null,
            $data['address'] ?? null,
            $data['customer_unique_id'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'customer_unique_id' => $this->customerUniqueId,
        ], fn($value) => $value !== null);
    }
}
