<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Http\ApiResponse;

describe('ApiResponse', function (): void {

    describe('fromArray()', function (): void {

        it('marks response as successful when status is "success"', function (): void {
            $response = ApiResponse::fromArray(['status' => 'success', 'message' => 'OK', 'data' => []], 200);

            expect($response->successful())->toBeTrue()
                ->and($response->failed())->toBeFalse();
        });

        it('marks response as successful when status is "ok"', function (): void {
            $response = ApiResponse::fromArray(['status' => 'ok', 'message' => 'OK', 'data' => []], 200);

            expect($response->successful())->toBeTrue();
        });

        it('marks response as failed when status is "fail"', function (): void {
            $response = ApiResponse::fromArray(['status' => 'fail', 'message' => 'Error', 'data' => null], 200);

            expect($response->successful())->toBeFalse()
                ->and($response->failed())->toBeTrue();
        });

        it('falls back to HTTP status when api status field is missing', function (): void {
            $response = ApiResponse::fromArray(['message' => 'OK'], 200);

            expect($response->successful())->toBeTrue();
        });

        it('captures the HTTP status code', function (): void {
            $response = ApiResponse::fromArray(['status' => 'success'], 201);

            expect($response->status())->toBe(201);
        });

        it('captures the message', function (): void {
            $response = ApiResponse::fromArray(['status' => 'success', 'message' => 'Invoice created'], 200);

            expect($response->message())->toBe('Invoice created');
        });

        it('exposes the data section', function (): void {
            $data = ['invoice_id' => 123, 'url' => 'https://pay.example.com'];
            $response = ApiResponse::fromArray(['status' => 'success', 'data' => $data], 200);

            expect($response->data())->toBe($data);
        });

        it('returns null data when not present in payload', function (): void {
            $response = ApiResponse::fromArray(['status' => 'success'], 200);

            expect($response->data())->toBeNull();
        });

        it('exposes the full raw body', function (): void {
            $body = ['status' => 'success', 'extra_field' => 'value'];
            $response = ApiResponse::fromArray($body, 200);

            expect($response->raw())->toBe($body);
        });
    });

    describe('get()', function (): void {

        it('retrieves a top-level key from data', function (): void {
            $response = ApiResponse::fromArray(['status' => 'success', 'data' => ['invoice_id' => 42]], 200);

            expect($response->get('invoice_id'))->toBe(42);
        });

        it('retrieves a nested key using dot notation', function (): void {
            $response = ApiResponse::fromArray([
                'status' => 'success',
                'data'   => ['payment' => ['amount' => 500]],
            ], 200);

            expect($response->get('payment.amount'))->toBe(500);
        });

        it('returns the default when the key is missing', function (): void {
            $response = ApiResponse::fromArray(['status' => 'success', 'data' => []], 200);

            expect($response->get('missing_key', 'default'))->toBe('default');
        });

        it('returns the default when data is null', function (): void {
            $response = ApiResponse::fromArray(['status' => 'success', 'data' => null], 200);

            expect($response->get('any_key', 'fallback'))->toBe('fallback');
        });
    });
});
