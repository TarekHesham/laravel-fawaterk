<?php

declare(strict_types=1);

use Orchestra\Testbench\TestCase;
use ElFarmawy\Fawaterk\Providers\FawaterkServiceProvider;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| All Pest tests in this package use the Orchestra Testbench TestCase so
| they have access to a bootstrapped Laravel application.
*/

uses(TestCase::class)
    ->beforeEach(function (): void {
        // Load the package service provider for every test.
    })
    ->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Helper: resolve package service providers
|--------------------------------------------------------------------------
*/
function getPackageProviders(): array
{
    return [FawaterkServiceProvider::class];
}
