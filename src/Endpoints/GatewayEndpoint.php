<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Endpoints;

use ElFarmawy\Fawaterk\Data\PaymentMethodResponse;
use ElFarmawy\Fawaterk\Http\BaseEndpoint;

class GatewayEndpoint extends BaseEndpoint
{
    /**
     * Get all available and enabled payment methods.
     *
     * @return array<int, PaymentMethodResponse>
     */
    public function getPaymentMethods(): array
    {
        // According to step-1-initiate-payment.md, the endpoint is /api/v2/getPaymentmethods
        $response = $this->client->get('/getPaymentmethods');

        // The API returns an array of payment method objects directly.
        // So, we need to iterate through each and map to our DTO.
        $paymentMethodsData = $response->data();

        // Ensure $paymentMethodsData is an array to prevent errors if the API returns something unexpected.
        if (! is_array($paymentMethodsData)) {
            $paymentMethodsData = [];
        }

        return array_map(fn (array $data) => PaymentMethodResponse::fromArray($data), $paymentMethodsData);
    }
}
