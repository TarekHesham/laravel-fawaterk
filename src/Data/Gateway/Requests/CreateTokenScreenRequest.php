<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Gateway\Requests;

use ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData;
use ElFarmawy\Fawaterk\Data\Invoices\Requests\CreateInvoiceRequest as OrderData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\RedirectionUrlsData;

class CreateTokenScreenRequest
{
    public function __construct(
        public readonly OrderData $order,
        public readonly CustomerData $customerData,
        public readonly RedirectionUrlsData $redirectionUrls,
        public readonly bool $deductTotalAmount = true,
        public readonly array $allowedCardTypes = []
    ) {}

    public function toArray(): array
    {
        return [
            'deduct_total_amount' => $this->deductTotalAmount,
            'order' => $this->order->toArray(),
            'customerData' => $this->customerData->toArray(),
            'redirectionUrls' => $this->redirectionUrls->toArray(),
            'allowedCardTypes' => $this->allowedCardTypes,
        ];
    }
}
