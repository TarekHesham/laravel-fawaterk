<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Fawaterk;
use ElFarmawy\Fawaterk\Facades\Fawaterk as FawaterkFacade;
use ElFarmawy\Fawaterk\Http\FawaterakClient;
use ElFarmawy\Fawaterk\Providers\FawaterkServiceProvider;

// Provide the package service provider to Testbench for every test in this file.
beforeEach(function (): void {
    $this->app->register(FawaterkServiceProvider::class);

    config()->set('fawaterk', [
        'api_key'        => 'test-api-key',
        'mode'           => 'staging',
        'staging_url'    => 'https://staging.fawaterk.com/api/v2',
        'production_url' => 'https://app.fawaterk.com/api/v2',
        'timeout'        => 30,
        'retries'        => 0,
        'retry_delay'    => 500,
    ]);
});

describe('FawaterkServiceProvider', function (): void {

    it('binds FawaterakClient as a singleton', function (): void {
        $a = $this->app->make(FawaterakClient::class);
        $b = $this->app->make(FawaterakClient::class);

        expect($a)->toBeInstanceOf(FawaterakClient::class)
            ->and($a)->toBe($b); // same instance
    });

    it('binds Fawaterk coordinator as a singleton under the "fawaterk" key', function (): void {
        $a = $this->app->make('fawaterk');
        $b = $this->app->make('fawaterk');

        expect($a)->toBeInstanceOf(Fawaterk::class)
            ->and($a)->toBe($b);
    });

    it('creates an alias so Fawaterk::class resolves to the same singleton', function (): void {
        $viaConcrete = $this->app->make(Fawaterk::class);
        $viaAlias    = $this->app->make('fawaterk');

        expect($viaConcrete)->toBe($viaAlias);
    });

    it('resolves config from the fawaterk key', function (): void {
        expect(config('fawaterk.api_key'))->toBe('test-api-key')
            ->and(config('fawaterk.mode'))->toBe('staging');
    });

    it('passes the config to FawaterakClient so it resolves the correct base URL', function (): void {
        /** @var FawaterakClient $client */
        $client = $this->app->make(FawaterakClient::class);

        expect($client->getBaseUrl())->toBe('https://staging.fawaterk.com/api/v2')
            ->and($client->isStaging())->toBeTrue();
    });

    it('switches to production URL when mode is production', function (): void {
        config()->set('fawaterk.mode', 'production');

        // Re-bind so the new config is picked up.
        $this->app->forgetInstance(FawaterakClient::class);
        $this->app->singleton(FawaterakClient::class, static function ($app): FawaterakClient {
            return new FawaterakClient($app['config']->get('fawaterk', []));
        });

        /** @var FawaterakClient $client */
        $client = $this->app->make(FawaterakClient::class);

        expect($client->getBaseUrl())->toBe('https://app.fawaterk.com/api/v2')
            ->and($client->isStaging())->toBeFalse();
    });
});

describe('Fawaterk facade', function (): void {

    it('facade resolves to the Fawaterk coordinator', function (): void {
        FawaterkFacade::setFacadeApplication($this->app);

        $instance = FawaterkFacade::getFacadeRoot();

        expect($instance)->toBeInstanceOf(Fawaterk::class);
    });

    it('facade isStaging() delegates to the underlying client', function (): void {
        FawaterkFacade::setFacadeApplication($this->app);

        expect(FawaterkFacade::isStaging())->toBeTrue();
    });
});
