<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Exceptions;

/**
 * Thrown for transport-level or unexpected API errors.
 *
 * Covers:
 *  - Connection timeouts / network failures
 *  - HTTP 4xx errors other than 401 and 422
 *  - HTTP 5xx server errors
 *  - Malformed or non-JSON API responses
 */
class RequestException extends ApiException {}
