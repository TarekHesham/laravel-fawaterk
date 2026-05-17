<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Exceptions\ApiException;
use ElFarmawy\Fawaterk\Exceptions\AuthenticationException;
use ElFarmawy\Fawaterk\Exceptions\ValidationException;
use ElFarmawy\Fawaterk\Exceptions\RequestException;

describe('Exception hierarchy', function (): void {

    it('AuthenticationException extends ApiException', function (): void {
        $e = new AuthenticationException('Bad credentials', 401, ['debug' => 'token expired']);

        expect($e)->toBeInstanceOf(ApiException::class)
            ->and($e)->toBeInstanceOf(\RuntimeException::class)
            ->and($e->getMessage())->toBe('Bad credentials')
            ->and($e->getCode())->toBe(401)
            ->and($e->getContext())->toBe(['debug' => 'token expired']);
    });

    it('RequestException extends ApiException', function (): void {
        $e = new RequestException('Server error', 500, ['raw' => 'Internal Server Error']);

        expect($e)->toBeInstanceOf(ApiException::class)
            ->and($e->getCode())->toBe(500)
            ->and($e->getContext())->toHaveKey('raw');
    });

    it('ValidationException extends ApiException and exposes errors', function (): void {
        $errors = ['email' => ['Email is required.'], 'amount' => ['Must be positive.']];
        $e = new ValidationException($errors, 'Validation failed', 422);

        expect($e)->toBeInstanceOf(ApiException::class)
            ->and($e->getErrors())->toBe($errors)
            ->and($e->getCode())->toBe(422)
            ->and($e->getMessage())->toBe('Validation failed');
    });

    it('ValidationException has sensible defaults', function (): void {
        $e = new ValidationException();

        expect($e->getErrors())->toBe([])
            ->and($e->getCode())->toBe(422)
            ->and($e->getMessage())->toBe('The given data was invalid.');
    });

    it('ApiException context defaults to empty array', function (): void {
        $e = new ApiException('Something went wrong');

        expect($e->getContext())->toBe([]);
    });

    it('ApiException chains a previous exception', function (): void {
        $previous = new \RuntimeException('root cause');
        $e = new ApiException('Wrapped error', 0, [], $previous);

        expect($e->getPrevious())->toBe($previous);
    });
});
