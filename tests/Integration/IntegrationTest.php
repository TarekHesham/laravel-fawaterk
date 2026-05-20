<?php

namespace Tests\Integration;

use Orchestra\Testbench\TestCase;
use ElFarmawy\Fawaterk\Fawaterk;
use ElFarmawy\Fawaterk\Http\FawaterakClient;

class IntegrationTest extends TestCase
{
    private Fawaterk $fawaterk;

    protected function getPackageProviders($app)
    {
        return ['ElFarmawy\Fawaterk\Providers\FawaterkServiceProvider'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock config for the client
        config(['fawaterk.api_key' => 'test-api']);
        config(['fawaterk.mode' => 'sandbox']);

        $config = [
            'api_key' => 'test-api',
            'mode' => 'sandbox',
            'sandbox_url' => 'https://staging.fawaterk.com/api/v2',
        ];

        $client = new FawaterakClient($config);
        $this->fawaterk = new Fawaterk($client);
    }

    public function testGetPaymentMethods()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\GatewayEndpoint($this->fawaterk->client());
        $methods = $endpoint->getPaymentMethods();
        $this->assertIsArray($methods);
        $this->assertGreaterThan(0, count($methods));
    }
}
