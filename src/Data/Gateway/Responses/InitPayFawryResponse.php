<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data\Gateway\Responses;

use ElFarmawy\Fawaterk\Http\ApiResponse;

final class InitPayFawryResponse
{
    public function __construct(
        public readonly int $invoiceId,
        public readonly string $invoiceKey,
        public readonly string $fawryCode,
        public readonly string $expireDate, // Assuming string as per doc
    ) {}

    public static function fromApiResponse(ApiResponse $response): self
    {
        $data = $response->data();

        return new self(
            invoiceId: (int) $data['invoice_id'],
            invoiceKey: (string) $data['invoice_key'],
            fawryCode: (string) $data['payment_data']['fawryCode'],
            expireDate: (string) $data['payment_data']['expireDate'],
        );
    }
}
