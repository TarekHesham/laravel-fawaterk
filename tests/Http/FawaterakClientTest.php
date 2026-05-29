<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Http\FawaterakClient;
use ElFarmawy\Fawaterk\Http\ApiResponse;
use ElFarmawy\Fawaterk\Exceptions\AuthenticationException;
use ElFarmawy\Fawaterk\Exceptions\ValidationException;
use ElFarmawy\Fawaterk\Exceptions\RequestException;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeClient(array $overrides = []): FawaterakClient
{
    $config = array_merge([
        'api_key'        => 'test-api-key',
        'mode'           => 'staging',
        'staging_url'    => 'https://staging.fawaterk.com/api/v2',
        'production_url' => 'https://app.fawaterk.com/api/v2',
        'timeout'        => 30,
        'retries'        => 0,
        'retry_delay'    => 500,
    ], $overrides);

    return new FawaterakClient($config);
}

// ---------------------------------------------------------------------------
// Base URL resolution
// ---------------------------------------------------------------------------

describe('FawaterakClient – base URL', function (): void {

    it('uses the staging URL in staging mode', function (): void {
        $client = makeClient(['mode' => 'staging', 'staging_url' => 'https://staging.fawaterk.com/api/v2']);

        expect($client->getBaseUrl())->toBe('https://staging.fawaterk.com/api/v2')
            ->and($client->isStaging())->toBeTrue();
    });

    it('uses the production URL in production mode', function (): void {
        $client = makeClient(['mode' => 'production', 'production_url' => 'https://app.fawaterk.com/api/v2']);

        expect($client->getBaseUrl())->toBe('https://app.fawaterk.com/api/v2')
            ->and($client->isStaging())->toBeFalse();
    });

    it('strips trailing slashes from base URL', function (): void {
        $client = makeClient(['mode' => 'staging', 'staging_url' => 'https://staging.fawaterk.com/api/v2/']);

        expect($client->getBaseUrl())->toBe('https://staging.fawaterk.com/api/v2');
    });
});

// ---------------------------------------------------------------------------
// Successful responses
// ---------------------------------------------------------------------------

describe('FawaterakClient – successful responses', function (): void {

    it('returns an ApiResponse on a successful GET', function (): void {
        Http::fake([
            '*' => Http::response([
                'status'  => 'success',
                'message' => 'OK',
                'data'    => ['id' => 1],
            ], 200),
        ]);

        $response = makeClient()->get('/invoices');

        expect($response)->toBeInstanceOf(ApiResponse::class)
            ->and($response->successful())->toBeTrue()
            ->and($response->get('id'))->toBe(1);
    });

    it('returns an ApiResponse on a successful POST', function (): void {
        Http::fake([
            '*' => Http::response([
                'status'  => 'success',
                'message' => 'Created',
                'data'    => ['invoice_id' => 99],
            ], 201),
        ]);

        $response = makeClient()->post('/invoices', ['amount' => 500]);

        expect($response)->toBeInstanceOf(ApiResponse::class)
            ->and($response->status())->toBe(201)
            ->and($response->get('invoice_id'))->toBe(99);
    });

    it('sends the Bearer token in the Authorization header', function (): void {
        Http::fake(['*' => Http::response(['status' => 'success', 'data' => null], 200)]);

        makeClient(['api_key' => 'my-secret-key'])->get('/ping');

        Http::assertSent(function ($request): bool {
            return $request->hasHeader('Authorization', 'Bearer my-secret-key');
        });
    });

    it('sends requests as JSON with Accept: application/json', function (): void {
        Http::fake(['*' => Http::response(['status' => 'success', 'data' => null], 200)]);

        makeClient()->post('/test', ['foo' => 'bar']);

        Http::assertSent(function ($request): bool {
            return $request->hasHeader('Content-Type', 'application/json')
                && $request->hasHeader('Accept', 'application/json');
        });
    });
});

// ---------------------------------------------------------------------------
// Error normalisation
// ---------------------------------------------------------------------------

describe('FawaterakClient – error normalisation', function (): void {

    it('throws AuthenticationException on HTTP 401', function (): void {
        Http::fake([
            '*' => Http::response(['status' => 'fail', 'message' => 'Unauthorized'], 401),
        ]);

        makeClient()->get('/secure');
    })->throws(AuthenticationException::class, 'Unauthorized');

    it('throws ValidationException on HTTP 422', function (): void {
        Http::fake([
            '*' => Http::response([
                'status'  => 'fail',
                'message' => 'The given data was invalid.',
                'errors'  => ['amount' => ['The amount field is required.']],
            ], 422),
        ]);

        makeClient()->post('/invoices', []);
    })->throws(ValidationException::class);

    it('exposes field errors from a 422 ValidationException', function (): void {
        Http::fake([
            '*' => Http::response([
                'status'  => 'fail',
                'message' => 'Validation failed',
                'errors'  => ['email' => ['Invalid email.']],
            ], 422),
        ]);

        try {
            makeClient()->post('/test', []);
        } catch (ValidationException $e) {
            expect($e->getErrors())->toHaveKey('email')
                ->and($e->getCode())->toBe(422);
        }
    });

    it('throws RequestException on HTTP 500', function (): void {
        Http::fake([
            '*' => Http::response(['status' => 'fail', 'message' => 'Server error'], 500),
        ]);

        makeClient()->get('/broken');
    })->throws(RequestException::class);

    it('throws RequestException on HTTP 404', function (): void {
        Http::fake([
            '*' => Http::response(['status' => 'fail', 'message' => 'Not Found'], 404),
        ]);

        makeClient()->get('/missing');
    })->throws(RequestException::class);

    it('attaches the raw response context to any ApiException', function (): void {
        Http::fake([
            '*' => Http::response([
                'status'  => 'fail',
                'message' => 'Unauthorized',
                'debug'   => 'token expired',
            ], 401),
        ]);

        try {
            makeClient()->get('/secure');
        } catch (AuthenticationException $e) {
            expect($e->getContext())->toHaveKey('debug')
                ->and($e->getContext()['debug'])->toBe('token expired');
        }
    });
});
