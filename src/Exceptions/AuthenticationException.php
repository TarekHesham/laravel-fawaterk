<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Exceptions;

/**
 * Thrown when the API rejects the request due to invalid credentials.
 *
 * Typically corresponds to HTTP 401 Unauthorized responses.
 */
class AuthenticationException extends ApiException {}
