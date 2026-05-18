<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data;

use ElFarmawy\Fawaterk\Http\ApiResponse;

final class InvoiceResponse
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly bool $successful,
        public readonly int $status,
        public readonly string $message,
        public readonly ?InvoiceDataResponse $data,
        public readonly array $raw,
    ) {}

    /**
     * @param ApiResponse<array<string, mixed>|null> $response
     */
    public static function fromApiResponse(ApiResponse $response): self
    {
        $data = $response->data();

        return new self(
            successful: $response->successful(),
            status: $response->status(),
            message: $response->message(),
            data: is_array($data) ? InvoiceDataResponse::fromArray($data) : null,
            raw: $response->raw(),
        );
    }
}
