<?php

namespace ElFarmawy\Template\Tests;

use Orchestra\Testbench\TestCase;
use ElFarmawy\Template\Facades\API;
use Illuminate\Support\Facades\Config;
use ElFarmawy\Template\Providers\TemplateServiceProvider;

class TemplateServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TemplateServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'API' => API::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Configure the package
        Config::set('template.api_key',  'test-api-key');
        Config::set('template.base_url', 'https://example.com/api');
    }

    public function test_can_override_api_key_at_runtime()
    {
        API::setApiKey('new-key');

        $ref = new \ReflectionClass(API::getFacadeRoot());
        $prop = $ref->getProperty('apiKey');
        $prop->setAccessible(true);

        $this->assertSame('new-key', $prop->getValue(API::getFacadeRoot()));
    }
}
