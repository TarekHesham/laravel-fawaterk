<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\InvoiceResponse;
use ElFarmawy\Fawaterk\Http\ApiResponse;

describe('InvoiceResponse', function (): void {
    
    it('can be created from ApiResponse', function (): void {
        $apiResponse = new ApiResponse(
            successful: true,
            status: 200,
            message: 'Invoice created',
            data: [
                'invoice_id' => 123,
                'cartTotal' => '150.50',
                'customer' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe'
                ]
            ],
            raw: ['status' => 'success']
        );

        $response = InvoiceResponse::fromApiResponse($apiResponse);
        
        expect($response->successful)->toBeTrue()
            ->and($response->status)->toBe(200)
            ->and($response->message)->toBe('Invoice created')
            ->and($response->data->invoiceId)->toBe(123)
            ->and($response->data->cartTotal)->toBe(150.50)
            ->and($response->data->customer->firstName)->toBe('John');
    });

    it('handles missing data in ApiResponse', function (): void {
        $apiResponse = new ApiResponse(
            successful: false,
            status: 400,
            message: 'Error occurred',
            data: null,
            raw: ['status' => 'error']
        );

        $response = InvoiceResponse::fromApiResponse($apiResponse);
        
        expect($response->successful)->toBeFalse()
            ->and($response->status)->toBe(400)
            ->and($response->message)->toBe('Error occurred')
            ->and($response->data)->toBeNull();
    });

});
