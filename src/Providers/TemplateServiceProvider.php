<?php

namespace ElFarmawy\Template\Providers;

use Illuminate\Support\ServiceProvider;
use ElFarmawy\Template\Services\TemplateService;
use ElFarmawy\Template\Contracts\TemplateInterface;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/template.php',
            'template'
        );

        // Register the service as a singleton
        $this->app->singleton('template', TemplateService::class);

        // Register interface binding
        $this->app->bind(TemplateInterface::class, TemplateService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/template.php' => config_path('template.php'),
        ], 'config');
    }
}
