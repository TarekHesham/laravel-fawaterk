<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Exceptions;

use RuntimeException;

/**
 * Base exception for all Fawaterak SDK errors.
 *
 * All SDK-specific exceptions extend this class so consumers can catch
 * either a specific exception or the entire SDK exception hierarchy.
 */
class ApiException extends RuntimeException
{
    /**
     * @param  string          $message   Human-readable error description.
     * @param  int             $code      HTTP status code (or 0 for transport-level errors).
     * @param  array<string, mixed> $context   Raw response payload for debugging.
     * @param  \Throwable|null $previous  Underlying exception if applicable.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        protected readonly array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Raw response payload that accompanied this error, if any.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
