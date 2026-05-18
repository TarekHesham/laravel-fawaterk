<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data;

use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Enums\Frequency;

final class CreateInvoiceRequest
{
    /**
     * @param array<CartItemData> $cartItems
     * @param array<string, mixed>|null $payLoad
     */
    public function __construct(
        public readonly float $cartTotal,
        public readonly Currency $currency,
        public readonly CustomerData $customer,
        public readonly array $cartItems,
        public readonly ?string $shipping = null,
        public readonly ?Frequency $frequency = null,
        public readonly ?bool $sendSMS = null,
        public readonly ?bool $sendEmail = null,
        public readonly ?DiscountData $discountData = null,
        public readonly ?TaxData $taxData = null,
        public readonly ?array $payLoad = null,
        public readonly ?string $dueDate = null,
        public readonly ?RedirectionUrlsData $redirectionUrls = null,
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
