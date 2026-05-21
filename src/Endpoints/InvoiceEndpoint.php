<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Endpoints;

use ElFarmawy\Fawaterk\Data\Invoices\Requests\CreateInvoiceRequest;
use ElFarmawy\Fawaterk\Data\Invoices\Responses\InvoiceResponse;
use ElFarmawy\Fawaterk\Http\BaseEndpoint;
use ElFarmawy\Fawaterk\Exceptions\RequestException;
use ElFarmawy\Fawaterk\Exceptions\ApiException;

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

    public function verifyPaidInvoice(int $invoiceId): InvoiceResponse
    {
        $invoiceResponse = $this->getInvoiceData($invoiceId);

        if (! $invoiceResponse->data) {
            throw new RequestException("Invoice with ID {$invoiceId} not found or no data returned.");
        }

        if ($invoiceResponse->data->invoiceStatus !== 'paid' && $invoiceResponse->data->paid !== true) {
            throw new RequestException("Invoice with ID {$invoiceId} has a status of '{$invoiceResponse->data->invoiceStatus}', expected 'paid'.");
        }

        return $invoiceResponse;
    }
}
