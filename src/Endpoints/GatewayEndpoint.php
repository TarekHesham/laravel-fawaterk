<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Endpoints;

use ElFarmawy\Fawaterk\Data\Gateway\Requests\CreateCardTokenizationRequest;
use ElFarmawy\Fawaterk\Data\Gateway\Requests\CreateTokenScreenRequest;
use ElFarmawy\Fawaterk\Data\Gateway\Requests\DeleteTokenRequest;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayFawryResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayMeezaResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayRedirectResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Requests\InitPayRequest;
use ElFarmawy\Fawaterk\Data\Gateway\Requests\TokenizationPayRequest;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayAmanResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\InitPayBastaResponse;
use ElFarmawy\Fawaterk\Data\Gateway\Responses\PaymentMethodResponse;
use ElFarmawy\Fawaterk\Http\BaseEndpoint;
use ElFarmawy\Fawaterk\Http\ApiResponse;
use ElFarmawy\Fawaterk\Exceptions\ApiException;

class GatewayEndpoint extends BaseEndpoint
{
    /**
     * Get all available and enabled payment methods.
     *
     * @return array<int, PaymentMethodResponse>
     */
    public function paymentMethods(): array
    {
        $response = $this->client->get('/getPaymentmethods');
        $paymentMethodsData = $response->data();
        if (! is_array($paymentMethodsData)) {
            $paymentMethodsData = [];
        }

        return array_map(fn(array $data) => PaymentMethodResponse::fromArray($data), $paymentMethodsData);
    }

    /**
     * Initiate a payment and get the appropriate response based on the payment method.
     */
    public function initPay(InitPayRequest $request): InitPayRedirectResponse|InitPayFawryResponse|InitPayMeezaResponse
    {
        $response = $this->client->post('/invoiceInitPay', $request->toArray());

        return $this->resolveInitPayResponse($response);
    }

    public function createCardTokenScreen(CreateTokenScreenRequest $request): string
    {
        $response = $this->client->post('/createCardTokenScreen', $request->toArray());

        return (string) $response->get('redirectUrl');
    }

    public function createCardTokenization(CreateCardTokenizationRequest $request): array
    {
        $response = $this->client->post('/createCardTokenization', $request->toArray());

        return $response->data();
    }

    public function createTokenizationPayRequest(TokenizationPayRequest $request): array
    {
        $response = $this->client->post('/createTokenizationPayRequest', $request->toArray());

        return $response->data();
    }

    public function deleteCustomerToken(DeleteTokenRequest $request): bool
    {
        $response = $this->client->post('/deleteCustomerToken', $request->toArray());

        return $response->get('status') === 'success';
    }

    /**
     * Resolve the InitPay response into the correct DTO.
     */
    private function resolveInitPayResponse(ApiResponse $response): InitPayRedirectResponse|InitPayFawryResponse|InitPayMeezaResponse|InitPayAmanResponse|InitPayBastaResponse
    {
        $paymentData = $response->get('payment_data');

        if (isset($paymentData['redirectTo'])) {
            return InitPayRedirectResponse::fromApiResponse($response);
        }

        if (isset($paymentData['fawryCode'])) {
            return InitPayFawryResponse::fromApiResponse($response);
        }

        if (isset($paymentData['meezaQrCode'])) {
            return InitPayMeezaResponse::fromApiResponse($response);
        }

        if (isset($paymentData['amanCode'])) {
            return InitPayAmanResponse::fromApiResponse($response);
        }

        if (isset($paymentData['masaryCode'])) {
            return InitPayBastaResponse::fromApiResponse($response);
        }

        throw new ApiException(
            message: 'Unknown InitPay response type',
            context: $response->raw(),
        );
    }
}
