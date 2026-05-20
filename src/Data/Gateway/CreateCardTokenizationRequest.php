<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Gateway;

use ElFarmawy\Fawaterk\Data\CustomerData;

class CreateCardTokenizationRequest
{
    public function __construct(
        public readonly string $currency,
        public readonly CustomerData $customerData,
        public readonly array $cardData,
        public readonly bool $deductTotalAmount = false,
        public readonly ?array $payLoad = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'order' => [
                'currency' => $this->currency,
            ],
            'customerData' => $this->customerData->toArray(),
            'cardData' => $this->cardData,
        ];

        if ($this->deductTotalAmount) {
            $data['deduct_total_amount'] = $this->deductTotalAmount;
        }

        if ($this->payLoad) {
            $data['payLoad'] = $this->payLoad;
        }

        return $data;
    }
}
