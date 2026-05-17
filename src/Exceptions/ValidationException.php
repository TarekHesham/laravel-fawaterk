<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Exceptions;

/**
 * Thrown when the API returns validation errors for a request payload.
 *
 * Typically corresponds to HTTP 422 Unprocessable Entity responses.
 * The validation errors array mirrors the structure returned by the API.
 */
class ValidationException extends ApiException
{
    /**
     * @param  array<string, string[]> $errors    Field-level validation errors from the API.
     * @param  array<string, mixed>    $context   Raw response payload.
     * @param  \Throwable|null         $previous
     */
    public function __construct(
        private readonly array $errors = [],
        string $message = 'The given data was invalid.',
        int $code = 422,
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $context, $previous);
    }

    /**
     * Field-level validation errors returned by the API.
     *
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
