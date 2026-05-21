<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Providers;

use Illuminate\Support\ServiceProvider;
use ElFarmawy\Fawaterk\Fawaterk;
use ElFarmawy\Fawaterk\Http\FawaterakClient;

/**
 * FawaterkServiceProvider
 *
 * Responsibilities:
 *  - Publish and merge the fawaterk config file.
 *  - Bind FawaterakClient as a singleton with its config resolved.
 *  - Bind the Fawaterk coordinator as a singleton.
 * 
 */
class FawaterkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/fawaterk.php',
            'fawaterk'
        );

        $this->app->singleton(FawaterakClient::class, static function ($app): FawaterakClient {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('fawaterk', []);

            return new FawaterakClient($config);
        });

        $this->app->singleton('fawaterk', static function ($app): Fawaterk {
            return new Fawaterk($app->make(FawaterakClient::class));
        });

        $this->app->alias('fawaterk', Fawaterk::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/fawaterk.php' => config_path('fawaterk.php'),
        ], 'fawaterk-config');
    }
}
