<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Http\FawaterakClient;
use ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint;
use ElFarmawy\Fawaterk\Data\CreateInvoiceRequest;
use ElFarmawy\Fawaterk\Data\CustomerData;
use ElFarmawy\Fawaterk\Data\CartItemData;
use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Enums\Frequency;
use ElFarmawy\Fawaterk\Data\InvoiceResponse;
use Illuminate\Support\Facades\Http;

function makeInvoiceEndpoint(): InvoiceEndpoint
{
    $config = [
        'api_key'        => 'test-key',
        'mode'           => 'sandbox',
        'sandbox_url'    => 'https://staging.fawaterk.com/api/v2',
        'timeout'        => 30,
        'retries'        => 0,
    ];

    $client = new FawaterakClient($config);
    
    return new InvoiceEndpoint($client);
}

describe('InvoiceEndpoint', function (): void {
    
    it('creates an invoice link successfully', function (): void {
        Http::fake([
            '*createInvoiceLink*' => Http::response([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'data' => [
                    'invoice_id' => 123,
                    'invoice_url' => 'https://staging.fawaterk.com/invoice/pay/abc',
                    'cartTotal' => '100',
                    'currency' => 'EGP'
                ]
            ], 200),
        ]);

        $endpoint = makeInvoiceEndpoint();

        $request = new CreateInvoiceRequest(
            cartTotal: 100.0,
            currency: Currency::EGP,
            customer: new CustomerData(firstName: 'John', lastName: 'Doe', email: 'john@example.com'),
            cartItems: [
                new CartItemData(name: 'Item 1', price: 100.0, quantity: 1),
            ],
            frequency: Frequency::ONCE,
            sendSMS: false,
            sendEmail: true,
            redirectionUrls: new \ElFarmawy\Fawaterk\Data\RedirectionUrlsData(successUrl: 'https://example.com/callback')
        );

        $response = $endpoint->createInvoiceLink($request);

        expect($response)->toBeInstanceOf(InvoiceResponse::class)
            ->and($response->successful)->toBeTrue()
            ->and($response->data->invoiceId)->toBe(123)
            ->and($response->data->invoiceUrl)->toBe('https://staging.fawaterk.com/invoice/pay/abc')
            ->and($response->data->cartTotal)->toBe(100.0)
            ->and($response->data->currency)->toBe('EGP');
            
        Http::assertSent(function ($request) {
            return $request->url() === 'https://staging.fawaterk.com/api/v2/createInvoiceLink' &&
                   $request->method() === 'POST' &&
                   $request['cartTotal'] === '100' &&
                   $request['currency'] === 'EGP' &&
                   $request['customer']['first_name'] === 'John' &&
                   $request['cartItems'][0]['name'] === 'Item 1' &&
                   $request['frequency'] === 'once' &&
                   $request['sendEmail'] === true;
        });
    });

    it('gets invoice data successfully', function (): void {
        Http::fake([
            '*getInvoiceData/123*' => Http::response([
                'status' => 'success',
                'data' => [
                    'invoice_id' => 123,
                    'invoice_status' => 'paid',
                    'payment_method' => 'card',
                    'total' => '100.00',
                    'currency' => 'EGP'
                ]
            ], 200),
        ]);

        $endpoint = makeInvoiceEndpoint();

        $response = $endpoint->getInvoiceData(123);

        expect($response)->toBeInstanceOf(InvoiceResponse::class)
            ->and($response->successful)->toBeTrue()
            ->and($response->data->invoiceId)->toBe(123)
            ->and($response->data->invoiceStatus)->toBe('paid')
            ->and($response->data->cartTotal)->toBe(100.0)
            ->and($response->data->currency)->toBe('EGP');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://staging.fawaterk.com/api/v2/getInvoiceData/123' &&
                   $request->method() === 'GET';
        });
    });

});
