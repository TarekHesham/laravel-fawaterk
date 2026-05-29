<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\Invoices\Responses\InvoiceResponse;
use ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint;
use ElFarmawy\Fawaterk\Exceptions\ApiException;
use ElFarmawy\Fawaterk\Exceptions\RequestException;
use ElFarmawy\Fawaterk\Http\ApiResponse;
use ElFarmawy\Fawaterk\Http\FawaterakClient;

beforeEach(function () {
    $this->mockFawaterakClient = Mockery::mock(FawaterakClient::class);
    // Create a partial mock for InvoiceEndpoint to mock its public methods directly
    $this->invoiceEndpoint = Mockery::mock(InvoiceEndpoint::class, [$this->mockFawaterakClient])->makePartial();
});

it('can verify a paid invoice from webhook', function () {
    $invoiceId = 123;
    $responseBody = [
        'status' => 'success',
        'message' => 'Invoice retrieved successfully',
        'data' => [
            'invoice_id' => $invoiceId,
            'invoice_key' => 'test_key',
            'invoice_status' => 'paid',
            'cartTotal' => 100.0,
        ],
    ];
    $mockApiResponse = ApiResponse::fromArray($responseBody, 200);
    $mockInvoiceResponse = InvoiceResponse::fromApiResponse($mockApiResponse);

    $this->invoiceEndpoint->shouldReceive('find')
        ->with($invoiceId)
        ->andReturn($mockInvoiceResponse);

    $invoiceResponse = $this->invoiceEndpoint->verifyPaid($invoiceId);

    expect($invoiceResponse)->toBeInstanceOf(InvoiceResponse::class)
        ->and($invoiceResponse->data)->not->toBeNull()
        ->and($invoiceResponse->data->invoiceId)->toBe($invoiceId)
        ->and($invoiceResponse->data->invoiceStatus)->toBe('paid');
});

it('throws RequestException if invoice is not found', function () {
    $invoiceId = 456;
    // Simulate empty data scenario
    $responseBody = ['status' => 'success', 'message' => 'No data', 'data' => null];
    $mockApiResponse = ApiResponse::fromArray($responseBody, 200);
    $mockInvoiceResponse = InvoiceResponse::fromApiResponse($mockApiResponse);

    $this->invoiceEndpoint->shouldReceive('find')
        ->with($invoiceId)
        ->andReturn($mockInvoiceResponse);

    $this->invoiceEndpoint->verifyPaid($invoiceId);
})->throws(RequestException::class, "Invoice with ID 456 not found or no data returned.");

it('throws RequestException if invoice status is not paid', function () {
    $invoiceId = 789;
    $responseBody = [
        'status' => 'success',
        'message' => 'Invoice retrieved successfully',
        'data' => [
            'invoice_id' => $invoiceId,
            'invoice_key' => 'test_key',
            'invoice_status' => 'pending', // Mismatched status
            'cartTotal' => 50.0,
        ],
    ];
    $mockApiResponse = ApiResponse::fromArray($responseBody, 200);
    $mockInvoiceResponse = InvoiceResponse::fromApiResponse($mockApiResponse);

    $this->invoiceEndpoint->shouldReceive('find')
        ->with($invoiceId)
        ->andReturn($mockInvoiceResponse);

    $this->invoiceEndpoint->verifyPaid($invoiceId);
})->throws(RequestException::class, "Invoice with ID 789 has a status of 'pending', expected 'paid'.");

it('propels ApiException when find fails', function () {
    $invoiceId = 101;

    $this->invoiceEndpoint->shouldReceive('find')
        ->with($invoiceId)
        ->andThrow(new ApiException('API Error', 400, []));

    $this->invoiceEndpoint->verifyPaid($invoiceId);
})->throws(ApiException::class, "API Error");
