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

        config(['fawaterk.api_key' => 'api-key']);
        config(['fawaterk.mode' => 'sandbox']);

        $config = [
            'api_key' => 'api-key',
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

    public function testCreateInvoiceLink()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint($this->fawaterk->client());

        $customer = new \ElFarmawy\Fawaterk\Data\CustomerData(
            firstName: 'Mohammad',
            lastName: 'Hamza',
            email: 'test@fawaterk.com',
            phone: '0123456789',
            address: 'test address'
        );

        $cartItems = [
            new \ElFarmawy\Fawaterk\Data\CartItemData(
                name: 'Test Product',
                price: 25.0,
                quantity: 1
            )
        ];

        $request = new \ElFarmawy\Fawaterk\Data\CreateInvoiceRequest(
            cartTotal: 25.0,
            currency: \ElFarmawy\Fawaterk\Enums\Currency::EGP,
            customer: $customer,
            cartItems: $cartItems
        );

        $response = $endpoint->createInvoiceLink($request);

        $this->assertNotNull($response->data->invoiceUrl);
        $this->assertNotNull($response->data->invoiceKey);
        $this->assertGreaterThan(0, $response->data->invoiceId);
    }

    public function testGetInvoiceData()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint($this->fawaterk->client());

        $customer = new \ElFarmawy\Fawaterk\Data\CustomerData(
            firstName: 'Mohammad',
            lastName: 'Hamza',
            email: 'test@fawaterk.com',
            phone: '0123456789',
            address: 'test address'
        );

        $cartItems = [
            new \ElFarmawy\Fawaterk\Data\CartItemData(
                name: 'Test Product',
                price: 25.0,
                quantity: 1
            )
        ];

        $request = new \ElFarmawy\Fawaterk\Data\CreateInvoiceRequest(
            cartTotal: 25.0,
            currency: \ElFarmawy\Fawaterk\Enums\Currency::EGP,
            customer: $customer,
            cartItems: $cartItems
        );

        $created = $endpoint->createInvoiceLink($request);

        $retrieved = $endpoint->getInvoiceData($created->data->invoiceId);

        $this->assertEquals($created->data->invoiceId, $retrieved->data->invoiceId);
    }

    public function testVerifyInvoiceFromWebhook()
    {
        $endpoint = new \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint($this->fawaterk->client());

        $invoiceId = 1099266;

        $response = $endpoint->verifyPaidInvoice($invoiceId);

        $this->assertTrue($response->data->invoiceStatus === 'paid' || $response->data->paid === true);
    }
}
