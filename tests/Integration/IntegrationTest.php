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

        $apiKey = env('FAWATERK_API_KEY');

        if (! $apiKey) {
            $this->markTestSkipped('FAWATERK_API_KEY is not configured.');
        }

        config([
            'fawaterk.api_key' => $apiKey,
            'fawaterk.mode' => 'staging',
        ]);

        $config = [
            'api_key' => $apiKey,
            'mode' => 'staging',
            'staging_url' => 'https://staging.fawaterk.com/api/v2',
        ];

        $client = new FawaterakClient($config);

        $this->fawaterk = new Fawaterk($client);
    }

    public function testpaymentMethods()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\GatewayEndpoint($this->fawaterk->client());
        $methods = $endpoint->paymentMethods();
        $this->assertIsArray($methods);
        $this->assertGreaterThan(0, count($methods));
    }

    public function testcreate()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint($this->fawaterk->client());

        $customer = new \ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData(
            firstName: 'Mohammad',
            lastName: 'Hamza',
            email: 'test@fawaterk.com',
            phone: '0123456789',
            address: 'test address'
        );

        $cartItems = [
            new \ElFarmawy\Fawaterk\Data\Invoices\Shared\CartItemData(
                name: 'Test Product',
                price: 25.0,
                quantity: 1
            )
        ];

        $request = new \ElFarmawy\Fawaterk\Data\Invoices\Requests\CreateInvoiceRequest(
            cartTotal: 25.0,
            currency: \ElFarmawy\Fawaterk\Enums\Currency::EGP,
            customer: $customer,
            cartItems: $cartItems
        );

        $response = $endpoint->create($request);

        $this->assertNotNull($response->data->invoiceUrl);
        $this->assertNotNull($response->data->invoiceKey);
        $this->assertGreaterThan(0, $response->data->invoiceId);
    }

    public function testfind()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint($this->fawaterk->client());

        $customer = new \ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData(
            firstName: 'Mohammad',
            lastName: 'Hamza',
            email: 'test@fawaterk.com',
            phone: '0123456789',
            address: 'test address'
        );

        $cartItems = [
            new \ElFarmawy\Fawaterk\Data\Invoices\Shared\CartItemData(
                name: 'Test Product',
                price: 25.0,
                quantity: 1
            )
        ];

        $request = new \ElFarmawy\Fawaterk\Data\Invoices\Requests\CreateInvoiceRequest(
            cartTotal: 25.0,
            currency: \ElFarmawy\Fawaterk\Enums\Currency::EGP,
            customer: $customer,
            cartItems: $cartItems
        );

        $created = $endpoint->create($request);

        $retrieved = $endpoint->find($created->data->invoiceId);

        $this->assertEquals($created->data->invoiceId, $retrieved->data->invoiceId);
    }

    public function testVerifyInvoiceFromWebhook()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint($this->fawaterk->client());

        $invoiceId = 1099266;

        $response = $endpoint->verifyPaid($invoiceId);

        $this->assertTrue($response->data->invoiceStatus === 'paid' || $response->data->paid === true);
    }
}
