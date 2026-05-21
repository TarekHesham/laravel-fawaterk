<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Http;

/**
 * Typed wrapper around a Fawaterak API response.
 *
 * Normalises the raw HTTP response into a consistent structure that
 * all endpoint classes can rely on, keeping raw arrays confined to
 * the transport layer.
 *
 * @template TData of array<string, mixed>|null
 */
class ApiResponse
{
    /**
     * @param  bool                    $successful  Whether the API reported success.
     * @param  int                     $status      HTTP status code.
     * @param  string                  $message     Top-level message from the API payload.
     * @param  TData                   $data        Decoded response data section.
     * @param  array<string, mixed>    $raw         The full decoded response body.
     */
    public function __construct(
        private readonly bool $successful,
        private readonly int $status,
        private readonly string $message,
        private readonly mixed $data,
        private readonly array $raw,
    ) {}

    /**
     * Build an ApiResponse from the decoded JSON body and HTTP status code.
     *
     * Fawaterak envelopes look like:
     *   { "status": "success"|"fail", "message": "...", "data": { ... } }
     *
     * @param  array<string, mixed> $body
     */
    public static function fromArray(array $body, int $httpStatus): self
    {
        $apiStatus = (string) ($body['status'] ?? '');
        $successful = in_array($apiStatus, ['success', 'ok', '1', 'true'], strict: true)
            || ($httpStatus >= 200 && $httpStatus < 300 && $apiStatus === '');

        return new self(
            successful: $successful,
            status: $httpStatus,
            message: (string) ($body['message'] ?? ''),
            data: $body['data'] ?? null,
            raw: $body,
        );
    }

    public function __get(string $key): mixed
    {
        if (is_array($this->data)) {
            return data_get($this->data, $key);
        }

        if (is_object($this->data)) {
            return $this->data->{$key} ?? null;
        }

        return null;
    }

    public function object(): ?object
    {
        return is_array($this->data) ? (object) $this->data : null;
    }

    public function collect(?string $key = null): \Illuminate\Support\Collection
    {
        $targetData = $key ? data_get($this->data, $key) : $this->data;

        return collect($targetData);
    }

    /** Whether the API reported a successful result. */
    public function successful(): bool
    {
        return $this->successful;
    }

    /** Whether the API reported a failure. */
    public function failed(): bool
    {
        return !$this->successful;
    }

    /** HTTP status code of the response. */
    public function status(): int
    {
        return $this->status;
    }

    /** Top-level message string from the API. */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * The `data` section of the API payload.
     *
     * @return array<string, mixed>|null
     */
    public function data(): mixed
    {
        return $this->data;
    }

    /**
     * Retrieve a specific key from the `data` section using dot-notation.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!is_array($this->data)) {
            return $default;
        }

        return data_get($this->data, $key, $default);
    }

    /**
     * The full raw decoded response body.
     *
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->raw;
    }
}
