<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Gateway\Requests;

use ElFarmawy\Fawaterk\Enums\Currency;

class TokenizationPayRequest
{
    public function __construct(
        public readonly float $amount,
        public readonly Currency $currency,
        public readonly string $customerToken,
        public readonly string $tokenAction = 'tokenization',
        public readonly ?array $payload = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'tokenAction' => $this->tokenAction,
            'order' => [
                'amount' => $this->amount,
                'currency' => $this->currency->value,
            ],
            'customerData' => [
                'customer_token' => $this->customerToken,
            ],
        ];

        if ($this->payload) {
            $data['payload'] = $this->payload;
        }

        return $data;
    }
}
