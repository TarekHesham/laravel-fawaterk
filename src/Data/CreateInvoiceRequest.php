<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data;

use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Enums\Frequency;

final class CreateInvoiceRequest
{
    /**
     * @param array<CartItemData> $cartItems
     * @param array<string, mixed>|null $payLoad Custom fields to persist between send/receive steps.
     * @param string|null $dueDate Format: Y-m-d.
     * @param array<string, mixed>|null $payLoad
     */
    public function __construct(
        public readonly float $cartTotal, // Mandatory
        public readonly Currency $currency, // Mandatory: 'USD', 'EGP', 'SR', 'AED', 'KWD', 'QAR', 'BHD'
        public readonly CustomerData $customer, // Mandatory
        public readonly array $cartItems, // Mandatory: Array of CartItemData
        public readonly ?string $shipping = null, // Optional: Decimal
        public readonly ?Frequency $frequency = null, // Optional: Default 'once'
        public readonly ?bool $sendSMS = null, // Optional
        public readonly ?bool $sendEmail = null, // Optional
        public readonly ?DiscountData $discountData = null, // Optional
        public readonly ?TaxData $taxData = null, // Optional
        public readonly ?array $payLoad = null, // Optional
        public readonly ?string $dueDate = null, // Optional: Y-m-d
        public readonly ?RedirectionUrlsData $redirectionUrls = null, // Optional
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'cartTotal' => (string) $this->cartTotal,
            'currency' => $this->currency->value,
            'customer' => $this->customer->toArray(),
            'cartItems' => array_map(fn(CartItemData $item) => $item->toArray(), $this->cartItems),
        ];

        if ($this->shipping !== null) {
            $data['shipping'] = $this->shipping;
        }
        if ($this->frequency !== null) {
            $data['frequency'] = $this->frequency->value;
        }
        if ($this->sendSMS !== null) {
            $data['sendSMS'] = $this->sendSMS;
        }
        if ($this->sendEmail !== null) {
            $data['sendEmail'] = $this->sendEmail;
        }
        if ($this->discountData !== null) {
            $data['discountData'] = $this->discountData->toArray();
        }
        if ($this->taxData !== null) {
            $data['taxData'] = $this->taxData->toArray();
        }
        if ($this->payLoad !== null) {
            $data['payLoad'] = $this->payLoad;
        }
        if ($this->dueDate !== null) {
            $data['due_date'] = $this->dueDate;
        }
        if ($this->redirectionUrls !== null) {
            $data['redirectionUrls'] = $this->redirectionUrls->toArray();
        }

        return $data;
    }

    public static function builder(): CreateInvoiceBuilder
    {
        return new CreateInvoiceBuilder();
    }
}
