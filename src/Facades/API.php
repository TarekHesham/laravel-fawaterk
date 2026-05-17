<?php

namespace ElFarmawy\Template\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setApiKey(string $apiKey)
 * @method static void setBaseUrl(string $baseUrl)
 * 
 * @see \ElFarmawy\Template\Services\TemplateService
 */
class API extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'template';
    }
}
