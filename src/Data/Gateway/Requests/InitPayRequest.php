<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Gateway\Requests;

use Carbon\Carbon;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CartItemData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\DiscountData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\RedirectionUrlsData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\TaxData;
use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Enums\Frequency;
use ElFarmawy\Fawaterk\Enums\Language;

final class InitPayRequest
{
    /**
     * @param  CartItemData[] $cartItems
     */
    public function __construct(
        public readonly int $payment_method_id,
        public readonly float $cartTotal,
        public readonly Currency $currency,
        public readonly CustomerData $customer,
        public readonly array $cartItems,
        public readonly ?string $invoice_number = null,
        public readonly ?RedirectionUrlsData $redirectionUrls = null,
        public readonly ?Frequency $frequency = null,
        public readonly ?string $customExpireDate = null,
        public readonly ?DiscountData $discountData = null,
        public readonly ?TaxData $taxData = null,
        public readonly ?int $authAndCapture = null, // 0 or 1
        public readonly mixed $payLoad = null,
        public readonly ?string $mobileWalletNumber = null,
        public readonly ?Carbon $due_date = null,
        public readonly ?bool $sendEmail = null,
        public readonly ?bool $sendSMS = null,
        public readonly ?Language $lang = null,
        public readonly ?bool $redirectOption = null, // default false
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'payment_method_id' => $this->payment_method_id,
            'cartTotal' => (string) $this->cartTotal,
            'currency' => $this->currency->value,
            'invoice_number' => $this->invoice_number,
            'customer' => $this->customer->toArray(),
            'redirectionUrls' => $this->redirectionUrls?->toArray(),
            'cartItems' => array_map(fn($item) => $item->toArray(), $this->cartItems),
            'frequency' => $this->frequency?->value,
            'customExpireDate' => $this->customExpireDate,
            'discountData' => $this->discountData?->toArray(),
            'taxData' => $this->taxData?->toArray(),
            'authAndCapture' => $this->authAndCapture,
            'payLoad' => $this->payLoad,
            'mobileWalletNumber' => $this->mobileWalletNumber,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'sendEmail' => $this->sendEmail,
            'sendSMS' => $this->sendSMS,
            'lang' => $this->lang?->value,
            'redirectOption' => $this->redirectOption,
        ], fn($value) => $value !== null);
    }
}
