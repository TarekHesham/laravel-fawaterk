<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\Invoices\Shared\CartItemData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayFawryResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayMeezaResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayRedirectResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Requests\InitPayRequest;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\PaymentMethodResponse;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\RedirectionUrlsData;
use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Endpoints\GatewayEndpoint;
use ElFarmawy\Fawaterk\Http\FawaterakClient;
use Illuminate\Support\Facades\Http;

function makeGatewayEndpoint(): GatewayEndpoint
{
    $config = [
        'api_key'        => 'api-key', // Updated API key
        'mode'           => 'staging',
        'staging_url'    => 'https://staging.fawaterk.com/api/v2',
        'timeout'        => 30,
        'retries'        => 0,
    ];

    $client = new FawaterakClient($config);

    return new GatewayEndpoint($client);
}


describe('GatewayEndpoint', function (): void {

    it('gets payment methods successfully', function (): void {
        Http::fake([
            '*getPaymentmethods*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        "paymentId" => 2,
                        "name_en" => "Visa-Mastercard",
                        "name_ar" => "فيزا -ماستر كارد",
                        "redirect" => "true",
                        "logo" => "https://app.fawaterak.xyz/clients/payment_options/mastercard-visa.png",
                    ],
                    [
                        "paymentId" => 3,
                        "name_en" => "Fawry",
                        "name_ar" => "فوري",
                        "redirect" => "false",
                        "logo" => "https://app.fawaterak.xyz/clients/payment_options/fawry.png",
                    ],
                ]
            ], 200),
        ]);

        $endpoint = makeGatewayEndpoint();

        $paymentMethods = $endpoint->paymentMethods();

        expect($paymentMethods)->toBeArray()
            ->and(count($paymentMethods))->toBe(2)
            ->and($paymentMethods[0])->toBeInstanceOf(PaymentMethodResponse::class)
            ->and($paymentMethods[0]->paymentId)->toBe(2)
            ->and($paymentMethods[0]->nameEn)->toBe('Visa-Mastercard')
            ->and($paymentMethods[0]->nameAr)->toBe('فيزا -ماستر كارد')
            ->and($paymentMethods[0]->redirect)->toBeTrue()
            ->and($paymentMethods[1])->toBeInstanceOf(PaymentMethodResponse::class)
            ->and($paymentMethods[1]->paymentId)->toBe(3)
            ->and($paymentMethods[1]->nameEn)->toBe('Fawry')
            ->and($paymentMethods[1]->nameAr)->toBe('فوري')
            ->and($paymentMethods[1]->redirect)->toBeFalse();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://staging.fawaterk.com/api/v2/getPaymentmethods' &&
                $request->method() === 'GET';
        });
    });

    it('returns an empty array when no payment methods are available', function (): void {
        Http::fake([
            '*getPaymentmethods*' => Http::response([
                'status' => 'success',
                'data' => [],
            ], 200),
        ]);

        $endpoint = makeGatewayEndpoint();

        $paymentMethods = $endpoint->paymentMethods();

        expect($paymentMethods)->toBeArray()->toBeEmpty();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://staging.fawaterk.com/api/v2/getPaymentmethods' &&
                $request->method() === 'GET';
        });
    });

    it('handles API errors gracefully', function (): void {
        Http::fake([
            '*getPaymentmethods*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $endpoint = makeGatewayEndpoint();

        // Expect an AuthenticationException to be thrown because of the 401 status
        expect(fn() => $endpoint->paymentMethods())->toThrow(\ElFarmawy\Fawaterk\Exceptions\AuthenticationException::class);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://staging.fawaterk.com/api/v2/getPaymentmethods' &&
                $request->method() === 'GET';
        });
    });

    it('initiates payment successfully with redirect response', function (): void {
        Http::fake([
            '*invoiceInitPay*' => Http::response([
                'status' => 'success',
                'data' => [
                    'invoice_id' => 1000428,
                    'invoice_key' => 'hyU2vcy3USvT5Tg',
                    'payment_data' => [
                        'redirectTo' => 'https://staging.fawaterk.com/link/I0PAH',
                    ],
                ],
            ], 200),
        ]);

        $endpoint = makeGatewayEndpoint();

        $request = new InitPayRequest(
            payment_method_id: 2,
            cartTotal: 50.0,
            currency: Currency::EGP,
            customer: new CustomerData('mohammad', 'hamza', 'test@fawaterk.com', '01xxxxxxxxx', 'test address'),
            cartItems: [
                new CartItemData('Item 1', 25.0, 1),
                new CartItemData('Item 2', 25.0, 1),
            ],
            redirectionUrls: new RedirectionUrlsData(
                successUrl: 'https://dev.fawaterk.com/success',
                failUrl: 'https://dev.fawaterk.com/fail',
                pendingUrl: 'https://dev.fawaterk.com/pending'
            ),
        );

        $response = $endpoint->initPay($request);

        expect($response)->toBeInstanceOf(InitPayRedirectResponse::class)
            ->and($response->invoiceId)->toBe(1000428)
            ->and($response->invoiceKey)->toBe('hyU2vcy3USvT5Tg')
            ->and($response->redirectTo)->toBe('https://staging.fawaterk.com/link/I0PAH');

        Http::assertSent(function ($httpRequest) use ($request) {
            return $httpRequest->url() === 'https://staging.fawaterk.com/api/v2/invoiceInitPay' &&
                $httpRequest->method() === 'POST' &&
                $httpRequest->data() === $request->toArray();
        });
    });

    it('initiates payment successfully with Fawry response', function (): void {
        Http::fake([
            '*invoiceInitPay*' => Http::response([
                'status' => 'success',
                'data' => [
                    'invoice_id' => 1000425,
                    'invoice_key' => 'QqgdnAB7Ad2kmIq',
                    'payment_data' => [
                        'fawryCode' => '981335305',
                        'expireDate' => '2021-07-06 15:53:41',
                    ],
                ],
            ], 200),
        ]);

        $endpoint = makeGatewayEndpoint();

        $request = new InitPayRequest(
            payment_method_id: 3, // Fawry payment method ID
            cartTotal: 50.0,
            currency: Currency::EGP,
            customer: new CustomerData('mohammad', 'hamza', 'test@fawaterk.com', '01xxxxxxxxx', 'test address'),
            cartItems: [
                new CartItemData('Item 1', 25.0, 1),
            ],
        );

        $response = $endpoint->initPay($request);

        expect($response)->toBeInstanceOf(InitPayFawryResponse::class)
            ->and($response->invoiceId)->toBe(1000425)
            ->and($response->invoiceKey)->toBe('QqgdnAB7Ad2kmIq')
            ->and($response->fawryCode)->toBe('981335305')
            ->and($response->expireDate)->toBe('2021-07-06 15:53:41');

        Http::assertSent(function ($httpRequest) use ($request) {
            return $httpRequest->url() === 'https://staging.fawaterk.com/api/v2/invoiceInitPay' &&
                $httpRequest->method() === 'POST' &&
                $httpRequest->data() === $request->toArray();
        });
    });

    it('initiates payment successfully with Meeza response', function (): void {
        Http::fake([
            '*invoiceInitPay*' => Http::response([
                'status' => 'success',
                'data' => [
                    'invoice_id' => 1000427,
                    'invoice_key' => '2vX8jSkmqbwJ4Ls',
                    'payment_data' => [
                        'meezaReference' => 4266311,
                        'meezaQrCode' => '00020101021226330016A00000073210000101096100559795204152053038185406106.565802EG5922Fawaterk Test Merchant6004Giza624505063424000105271000311116453477230707528640463047821',
                    ],
                ],
            ], 200),
        ]);

        $endpoint = makeGatewayEndpoint();

        $request = new InitPayRequest(
            payment_method_id: 4, // Meeza payment method ID
            cartTotal: 50.0,
            currency: Currency::EGP,
            customer: new CustomerData('mohammad', 'hamza', 'test@fawaterk.com', '01xxxxxxxxx', 'test address'),
            cartItems: [
                new CartItemData('Item 1', 25.0, 1),
            ],
            mobileWalletNumber: '01000000000', // Required for Mobile Wallet
        );

        $response = $endpoint->initPay($request);

        expect($response)->toBeInstanceOf(InitPayMeezaResponse::class)
            ->and($response->invoiceId)->toBe(1000427)
            ->and($response->invoiceKey)->toBe('2vX8jSkmqbwJ4Ls')
            ->and($response->meezaReference)->toBe(4266311)
            ->and($response->meezaQrCode)->toBe('00020101021226330016A00000073210000101096100559795204152053038185406106.565802EG5922Fawaterk Test Merchant6004Giza624505063424000105271000311116453477230707528640463047821');

        Http::assertSent(function ($httpRequest) use ($request) {
            return $httpRequest->url() === 'https://staging.fawaterk.com/api/v2/invoiceInitPay' &&
                $httpRequest->method() === 'POST' &&
                $httpRequest->data() === $request->toArray();
        });
    });
});
