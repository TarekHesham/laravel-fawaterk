<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Invoices\Responses;

use ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData;

final class InvoiceDataResponse
{
    /**
     * @param array<string, mixed>|null $paymentData
     * @param array<string, mixed>|null $payLoad
     * @param array<mixed>|null $invoiceTransactions
     */
    public function __construct(
        public readonly ?int $invoiceId = null,
        public readonly ?string $invoiceKey = null,
        public readonly ?string $invoiceUrl = null,
        public readonly ?string $invoiceStatus = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $referenceNumber = null,
        public readonly ?float $cartTotal = null,
        public readonly ?string $currency = null,
        public readonly ?CustomerData $customer = null,
        public readonly ?array $paymentData = null,
        public readonly ?string $dueDate = null,
        public readonly ?array $payLoad = null,
        public readonly ?string $frequency = null,
        public readonly ?float $commission = null,
        public readonly ?bool $paid = null,
        public readonly ?string $paidAt = null,
        public readonly ?string $invoiceCreatedAt = null,
        public readonly ?array $invoiceTransactions = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $cartTotal = null;
        if (isset($data['cartTotal'])) {
            $cartTotal = (float) $data['cartTotal'];
        } elseif (isset($data['total'])) {
            $cartTotal = (float) $data['total'];
        }

        $invoiceId = null;
        if (isset($data['invoice_id'])) {
            $invoiceId = (int) $data['invoice_id'];
        } elseif (isset($data['invoiceId'])) {
            $invoiceId = (int) $data['invoiceId'];
        }

        $invoiceKey = null;
        if (isset($data['invoice_key'])) {
            $invoiceKey = (string) $data['invoice_key'];
        } elseif (isset($data['invoiceKey'])) {
            $invoiceKey = (string) $data['invoiceKey'];
        }

        $invoiceUrl = null;
        if (isset($data['invoice_url'])) {
            $invoiceUrl = (string) $data['invoice_url'];
        } elseif (isset($data['url'])) {
            $invoiceUrl = (string) $data['url'];
        }

        return new self(
            invoiceId: $invoiceId,
            invoiceKey: $invoiceKey,
            invoiceUrl: $invoiceUrl,
            invoiceStatus: isset($data['invoice_status']) ? (string) $data['invoice_status'] : (isset($data['status_text']) ? (string) $data['status_text'] : null),
            paymentMethod: isset($data['payment_method']) ? (string) $data['payment_method'] : null,
            referenceNumber: isset($data['referenceNumber']) ? (string) $data['referenceNumber'] : null,
            cartTotal: $cartTotal,
            currency: isset($data['currency']) ? (string) $data['currency'] : null,
            customer: isset($data['customer']) && is_array($data['customer']) ? CustomerData::fromArray($data['customer']) : null,
            paymentData: isset($data['payment_data']) && is_array($data['payment_data']) ? $data['payment_data'] : null,
            dueDate: isset($data['due_date']) ? (string) $data['due_date'] : null,
            payLoad: isset($data['pay_load']) && is_array($data['pay_load']) ? $data['pay_load'] : null,
            frequency: isset($data['frequency']) ? (string) $data['frequency'] : null,
            commission: isset($data['commission']) ? (float) $data['commission'] : null,
            paid: isset($data['paid']) ? (bool) $data['paid'] : null,
            paidAt: isset($data['paid_at']) ? (string) $data['paid_at'] : null,
            invoiceCreatedAt: isset($data['invoice_created_at']) ? (string) $data['invoice_created_at'] : null,
            invoiceTransactions: isset($data['invoice_transactions']) && is_array($data['invoice_transactions']) ? $data['invoice_transactions'] : null,
        );
    }
}
