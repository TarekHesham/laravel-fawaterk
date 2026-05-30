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
     * @throws InvalidArgumentException If validation fails
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
    ) {
        $this->validate();
    }

    /**
     * Validate all payment request parameters.
     * 
     * SECURITY FIX: Prevents invalid amounts, payment methods, and malformed data.
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        // Validate payment method ID
        if ($this->payment_method_id <= 0) {
            throw new InvalidArgumentException(
                'payment_method_id must be a positive integer, got: ' . $this->payment_method_id
            );
        }

        // Validate cart total
        if ($this->cartTotal <= 0) {
            throw new InvalidArgumentException(
                'cartTotal must be greater than 0, got: ' . $this->cartTotal
            );
        }

        // Prevent extremely large amounts (security limit: 1 million)
        if ($this->cartTotal > 1000000) {
            throw new InvalidArgumentException(
                'cartTotal exceeds maximum allowed amount (1,000,000). Got: ' . $this->cartTotal
            );
        }

        // Validate cart items
        if (empty($this->cartItems)) {
            throw new InvalidArgumentException('At least one cart item is required.');
        }

        // Validate authAndCapture if provided
        if ($this->authAndCapture !== null && !in_array($this->authAndCapture, [0, 1], true)) {
            throw new InvalidArgumentException(
                'authAndCapture must be 0 or 1, got: ' . $this->authAndCapture
            );
        }

        // Validate mobile wallet number if provided
        if ($this->mobileWalletNumber !== null && trim($this->mobileWalletNumber) === '') {
            throw new InvalidArgumentException('mobileWalletNumber cannot be empty if provided.');
        }
    }

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
