<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use ElFarmawy\Fawaterk\Exceptions\ApiException;
use ElFarmawy\Fawaterk\Exceptions\AuthenticationException;
use ElFarmawy\Fawaterk\Exceptions\RequestException;
use ElFarmawy\Fawaterk\Exceptions\ValidationException;

/**
 * Core HTTP transport client for the Fawaterak API.
 *
 * Responsibilities:
 *  - Builds a pre-configured Laravel HTTP client instance
 *  - Resolves the correct base URL (staging vs production)
 *  - Attaches Bearer authentication on every request
 *  - Applies timeout and retry configuration
 *  - Sends GET / POST requests with JSON encoding
 *  - Normalises HTTP errors into typed SDK exceptions
 *  - Returns typed ApiResponse objects
 *
 * This class is intentionally kept small. It knows nothing about specific
 * Fawaterak endpoints, that concern belongs to endpoint classes.
 */
class FawaterakClient
{
    private readonly string $baseUrl;

    /**
     * @param  array<string, mixed> $config  The resolved 'fawaterk' config array.
     */
    public function __construct(private readonly array $config)
    {
        $this->baseUrl = $this->resolveBaseUrl();
    }

    /**
     * Send a GET request to the given endpoint path.
     *
     * @param  string               $path
     * @param  array<string, mixed> $query  URL query parameters.
     * @return ApiResponse
     *
     * @throws ApiException
     */
    public function get(string $path, array $query = []): ApiResponse
    {
        $response = $this->buildRequest()->get($this->url($path), $query);

        return $this->parseResponse($response);
    }

    /**
     * Send a POST request to the given endpoint path with a JSON body.
     *
     * @param  string               $path
     * @param  array<string, mixed> $payload
     * @return ApiResponse
     *
     * @throws ApiException
     */
    public function post(string $path, array $payload = []): ApiResponse
    {
        $response = $this->buildRequest()->post($this->url($path), $payload);

        return $this->parseResponse($response);
    }

    /** The base URL currently in use (staging or production). */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /** Whether the client is operating in staging mode. */
    public function isStaging(): bool
    {
        return ($this->config['mode'] ?? 'staging') === 'staging';
    }

    /**
     * Build a fully-configured PendingRequest ready for sending.
     */
    private function buildRequest(): PendingRequest
    {
        $timeout    = (int)    ($this->config['timeout']     ?? 30);
        $retries    = (int)    ($this->config['retries']     ?? 1);
        $retryDelay = (int)    ($this->config['retry_delay'] ?? 500);
        $apiKey     = (string) ($this->config['api_key']     ?? '');

        $request = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout($timeout);

        if ($retries > 0) {
            $request = $request->retry($retries, $retryDelay, throw: false);
        }

        return $request;
    }

    /**
     * Resolve the appropriate base URL based on the configured mode.
     */
    private function resolveBaseUrl(): string
    {
        $mode = (string) ($this->config['mode'] ?? 'staging');

        return $mode === 'production'
            ? rtrim((string) ($this->config['production_url'] ?? 'https://app.fawaterk.com/api/v2'), '/')
            : rtrim((string) ($this->config['staging_url'] ?? 'https://staging.fawaterk.com/api/v2'), '/');
    }

    /**
     * Prepend the base URL to a path segment.
     */
    private function url(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Parse the raw HTTP response into an ApiResponse or throw a typed exception.
     *
     * @throws AuthenticationException  On HTTP 401.
     * @throws ValidationException      On HTTP 422.
     * @throws RequestException         On all other non-successful HTTP statuses.
     * @throws ApiException             On non-JSON or unexpected responses.
     */
    private function parseResponse(Response $response): ApiResponse
    {
        $status = $response->status();

        // Attempt to decode the body as JSON regardless of status code so we
        // can include the raw payload in any exception context.
        /** @var array<string, mixed> $body */
        $body = $this->decodeBody($response);

        // Normalise HTTP errors into typed exceptions.
        if ($response->failed()) {
            $apiMessage = $body['message'] ?? null;

            if (is_array($apiMessage)) {
                $message = json_encode($apiMessage, JSON_UNESCAPED_UNICODE);
            } else {
                $message = (string) ($apiMessage ?? $response->reason() ?? 'Unknown error');
            }

            match (true) {
                $status === 401 => throw new AuthenticationException(
                    message: $message,
                    code: $status,
                    context: $body,
                ),
                $status === 422 => throw new ValidationException(
                    errors: (array) ($body['errors'] ?? []),
                    message: $message,
                    code: $status,
                    context: $body,
                ),
                default => throw new RequestException(
                    message: $message,
                    code: $status,
                    context: $body,
                ),
            };
        }

        return ApiResponse::fromArray($body, $status);
    }

    /**
     * Decode the response body to an associative array.
     *
     * Falls back to an empty array when the body is empty or non-JSON,
     * instead of throwing — the caller decides what to do with the result.
     *
     * @return array<string, mixed>
     */
    private function decodeBody(Response $response): array
    {
        try {
            /** @var array<string, mixed>|null $decoded */
            $decoded = $response->json();

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
