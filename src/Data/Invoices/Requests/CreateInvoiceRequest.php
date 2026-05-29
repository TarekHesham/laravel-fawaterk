<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Invoices\Requests;

use ElFarmawy\Fawaterk\Data\Invoices\Shared\CartItemData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\DiscountData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\RedirectionUrlsData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\TaxData;
use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Enums\Frequency;

final class CreateInvoiceRequest
{
    /**
     * @param array<CartItemData> $cartItems
     * @param array<string, mixed>|null $payLoad Custom fields to persist between send/receive steps.
     * @param string|null $dueDate Format: Y-m-d.
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
        return array_filter([
            'cartTotal'       => $this->cartTotal,
            'currency'        => $this->currency->value,
            'customer'        => $this->customer->toArray(),
            'cartItems'       => array_map(fn($item) => $item->toArray(), $this->cartItems),
            'shipping'        => $this->shipping,
            'frequency'       => $this->frequency?->value,
            'sendSMS'         => $this->sendSMS,
            'sendEmail'       => $this->sendEmail,
            'discountData'    => $this->discountData?->toArray(),
            'taxData'         => $this->taxData?->toArray(),
            'payLoad'         => $this->payLoad,
            'dueDate'         => $this->dueDate,
            'redirectionUrls' => $this->redirectionUrls?->toArray(),
        ], fn($value) => $value !== null);
    }

    public static function builder(): CreateInvoiceBuilder
    {
        return new CreateInvoiceBuilder();
    }
}
