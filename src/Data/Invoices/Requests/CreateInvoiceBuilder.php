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

final class CreateInvoiceBuilder
{
    private ?float $cartTotal = null;
    private ?Currency $currency = null;
    private ?CustomerData $customer = null;
    /** @var array<CartItemData>|null */
    private ?array $cartItems = null;
    private ?string $shipping = null;
    private ?Frequency $frequency = null;
    private ?bool $sendSMS = null;
    private ?bool $sendEmail = null;
    private ?DiscountData $discountData = null;
    private ?TaxData $taxData = null;
    /** @var array<string, mixed>|null */
    private ?array $payLoad = null;
    private ?string $dueDate = null;
    private ?RedirectionUrlsData $redirectionUrls = null;

    public function cartTotal(float $cartTotal): self
    {
        $this->cartTotal = $cartTotal;
        return $this;
    }

    public function currency(Currency $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function customer(CustomerData $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param array<CartItemData> $cartItems
     */
    public function cartItems(array $cartItems): self
    {
        $this->cartItems = $cartItems;
        return $this;
    }

    public function shipping(?string $shipping): self
    {
        $this->shipping = $shipping;
        return $this;
    }

    public function frequency(?Frequency $frequency): self
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function sendSMS(?bool $sendSMS): self
    {
        $this->sendSMS = $sendSMS;
        return $this;
    }

    public function sendEmail(?bool $sendEmail): self
    {
        $this->sendEmail = $sendEmail;
        return $this;
    }

    public function discountData(?DiscountData $discountData): self
    {
        $this->discountData = $discountData;
        return $this;
    }

    public function taxData(?TaxData $taxData): self
    {
        $this->taxData = $taxData;
        return $this;
    }

    /**
     * @param array<string, mixed>|null $payLoad
     */
    public function payLoad(?array $payLoad): self
    {
        $this->payLoad = $payLoad;
        return $this;
    }

    public function dueDate(?string $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function redirectionUrls(?RedirectionUrlsData $redirectionUrls): self
    {
        $this->redirectionUrls = $redirectionUrls;
        return $this;
    }

    public function build(): CreateInvoiceRequest
    {
        if ($this->cartTotal === null) {
            throw new \InvalidArgumentException('cartTotal is required.');
        }
        if ($this->currency === null) {
            throw new \InvalidArgumentException('currency is required.');
        }
        if ($this->customer === null) {
            throw new \InvalidArgumentException('customer is required.');
        }
        if (empty($this->cartItems)) {
            throw new \InvalidArgumentException('cartItems is required.');
        }

        foreach ($this->cartItems as $index => $item) {
            if (! $item instanceof CartItemData) {
                throw new \InvalidArgumentException("Item at index {$index} must be an instance of CartItemData.");
            }
        }

        return new CreateInvoiceRequest(
            cartTotal: $this->cartTotal,
            currency: $this->currency,
            customer: $this->customer,
            cartItems: $this->cartItems,
            shipping: $this->shipping,
            frequency: $this->frequency,
            sendSMS: $this->sendSMS,
            sendEmail: $this->sendEmail,
            discountData: $this->discountData,
            taxData: $this->taxData,
            payLoad: $this->payLoad,
            dueDate: $this->dueDate,
            redirectionUrls: $this->redirectionUrls,
        );
    }
}
