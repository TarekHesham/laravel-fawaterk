<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\PaymentMethodResponse;
use ElFarmawy\Fawaterk\Endpoints\GatewayEndpoint;
use ElFarmawy\Fawaterk\Http\FawaterakClient;
use Illuminate\Support\Facades\Http;

function makeGatewayEndpoint(): GatewayEndpoint
{
    $config = [
        'api_key'        => 'test-key',
        'mode'           => 'sandbox',
        'sandbox_url'    => 'https://staging.fawaterk.com/api/v2',
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

        $paymentMethods = $endpoint->getPaymentMethods();

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

        $paymentMethods = $endpoint->getPaymentMethods();

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
        expect(fn() => $endpoint->getPaymentMethods())->toThrow(\ElFarmawy\Fawaterk\Exceptions\AuthenticationException::class);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://staging.fawaterk.com/api/v2/getPaymentmethods' &&
                $request->method() === 'GET';
        });
    });
});
