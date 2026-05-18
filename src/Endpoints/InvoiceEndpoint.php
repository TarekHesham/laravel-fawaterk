<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Endpoints;

use ElFarmawy\Fawaterk\Data\CreateInvoiceRequest;
use ElFarmawy\Fawaterk\Data\InvoiceResponse;
use ElFarmawy\Fawaterk\Http\BaseEndpoint;

class InvoiceEndpoint extends BaseEndpoint
{
    /**
     * Create a new invoice link.
     */
    public function createInvoiceLink(CreateInvoiceRequest $request): InvoiceResponse
    {
        $response = $this->client->post('/createInvoiceLink', $request->toArray());

        return InvoiceResponse::fromApiResponse($response);
    }

    /**
     * Get details for a specific invoice.
     */
    public function getInvoiceData(int $invoiceId): InvoiceResponse
    {
        $response = $this->client->get("/getInvoiceData/{$invoiceId}");

        return InvoiceResponse::fromApiResponse($response);
    }
}
