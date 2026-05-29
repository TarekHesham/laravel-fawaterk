<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\Invoices\Requests\CreateInvoiceRequest;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CartItemData;
use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Enums\Frequency;

describe('CreateInvoiceRequest', function (): void {

    it('can be instantiated and converted to array', function (): void {
        $customer = new CustomerData(firstName: 'John', lastName: 'Doe', email: 'john@example.com');
        $item = new CartItemData(name: 'Item 1', price: 100.0, quantity: 1);

        $request = new CreateInvoiceRequest(
            cartTotal: 100.0,
            currency: Currency::EGP,
            customer: $customer,
            cartItems: [$item],
            shipping: '10.0',
            frequency: Frequency::WEEKLY,
            sendSMS: true,
            sendEmail: false,
            discountData: new \ElFarmawy\Fawaterk\Data\Invoices\Shared\DiscountData('pcg', 10),
            taxData: new \ElFarmawy\Fawaterk\Data\Invoices\Shared\TaxData('VAT', 14),
            payLoad: ['custom_key' => 'custom_value'],
            dueDate: '2026-10-10',
            redirectionUrls: new \ElFarmawy\Fawaterk\Data\Invoices\Shared\RedirectionUrlsData(successUrl: 'https://example.com/callback')
        );

        $array = $request->toArray();

        expect($array)->toBeArray()
            ->and($array['cartTotal'])->toBe(100.0)
            ->and($array['currency'])->toBe('EGP')
            ->and($array['customer']['first_name'])->toBe('John')
            ->and($array['cartItems'][0]['name'])->toBe('Item 1')
            ->and($array['shipping'])->toBe('10.0')
            ->and($array['frequency'])->toBe('weekly')
            ->and($array['sendSMS'])->toBeTrue()
            ->and($array['sendEmail'])->toBeFalse()
            ->and($array['discountData']['type'])->toBe('pcg')
            ->and($array['taxData']['title'])->toBe('VAT')
            ->and($array['payLoad']['custom_key'])->toBe('custom_value')
            ->and($array['dueDate'])->toBe('2026-10-10')
            ->and($array['redirectionUrls']['successUrl'])->toBe('https://example.com/callback');
    });

    it('excludes null optional properties from array', function (): void {
        $customer = new CustomerData(firstName: 'John', lastName: 'Doe');
        $item = new CartItemData(name: 'Item 1', price: 100.0, quantity: 1);

        $request = new CreateInvoiceRequest(
            cartTotal: 100.0,
            currency: Currency::EGP,
            customer: $customer,
            cartItems: [$item]
        );

        $array = $request->toArray();

        expect($array)->not->toHaveKey('shipping')
            ->not->toHaveKey('frequency')
            ->not->toHaveKey('sendSMS')
            ->not->toHaveKey('sendEmail')
            ->not->toHaveKey('redirectUrl');
    });
});
