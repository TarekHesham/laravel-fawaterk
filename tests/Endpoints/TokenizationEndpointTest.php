<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\CustomerData;
use ElFarmawy\Fawaterk\Data\CreateInvoiceRequest as OrderData;
use ElFarmawy\Fawaterk\Data\RedirectionUrlsData;
use ElFarmawy\Fawaterk\Data\Gateway\CreateTokenScreenRequest;
use ElFarmawy\Fawaterk\Data\Gateway\CreateCardTokenizationRequest;
use ElFarmawy\Fawaterk\Data\Gateway\TokenizationPayRequest;
use ElFarmawy\Fawaterk\Data\Gateway\DeleteTokenRequest;
use ElFarmawy\Fawaterk\Endpoints\GatewayEndpoint;
use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Http\FawaterakClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $client = new FawaterakClient(['api_token' => 'test-token', 'vendor_key' => 'test-key', 'mode' => 'staging']);
    $this->endpoint = new GatewayEndpoint($client);
});

it('can create a card token screen', function () {
    Http::fake([
        '*/createCardTokenScreen' => Http::response([
            'data' => [
                'status' => 'success',
                'redirectUrl' => 'https://staging.fawaterk.com/nbe/storeToken/123'
            ]
        ], 200),
    ]);

    $customer = new CustomerData('123', 'test', 'customer', 'test@test.com', '01234567891');
    $request = new CreateTokenScreenRequest(
        order: new OrderData(100.0, Currency::EGP, $customer, []),
        customerData: $customer,
        redirectionUrls: new RedirectionUrlsData('https://success.com', 'https://fail.com')
    );

    $url = $this->endpoint->createCardTokenScreen($request);

    expect($url)->toBe('https://staging.fawaterk.com/nbe/storeToken/123');
});

it('can create card tokenization', function () {
    Http::fake([
        '*/createCardTokenization' => Http::response([
            'data' => [
                'status' => 'success',
                'token' => '9731673377207107',
                'cardNumber' => '512345xxxxxx0008'
            ]
        ], 200),
    ]);

    $request = new CreateCardTokenizationRequest(
        currency: 'EGP',
        customerData: new CustomerData('222111333', 'Fname', 'Lname', 'usr@fawaterk.com', '01111111111'),
        cardData: [
            'card_number' => '5123450000000008',
            'expire_year' => '2027',
            'expire_month' => '12',
            'sec_code' => '100'
        ]
    );

    $response = $this->endpoint->createCardTokenization($request);

    expect($response)->toBe([
        'status' => 'success',
        'token' => '9731673377207107',
        'cardNumber' => '512345xxxxxx0008'
    ]);
});

it('can create tokenization pay request', function () {
    Http::fake([
        '*/createTokenizationPayRequest' => Http::response([
            'data' => [
                'status' => 'success',
                'redirectTo' => 'https://staging.fawaterk.com/mpgs/auth'
            ]
        ], 200),
    ]);

    $request = new TokenizationPayRequest(
        amount: 1000,
        currency: Currency::EGP,
        customerToken: '9731673377207107'
    );

    $response = $this->endpoint->createTokenizationPayRequest($request);

    expect($response['status'])->toBe('success');
    expect($response['redirectTo'])->toBe('https://staging.fawaterk.com/mpgs/auth');
});

it('can delete customer token', function () {
    Http::fake([
        '*/deleteCustomerToken' => Http::response([
            'data' => [
                'status' => 'success',
                'message' => 'token deleted'
            ]
        ], 200),
    ]);

    $request = new DeleteTokenRequest('222111333', '2345');

    $result = $this->endpoint->deleteCustomerToken($request);

    expect($result)->toBeTrue();
});
