<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Data;

final class PaymentMethodResponse
{
    public function __construct(
        public readonly int $paymentId,
        public readonly string $nameEn,
        public readonly string $nameAr,
        public readonly string $logo,
        public readonly bool $redirect,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            paymentId: (int) $data['paymentId'],
            nameEn: (string) $data['name_en'],
            nameAr: (string) $data['name_ar'],
            logo: (string) $data['logo'],
            redirect: filter_var($data['redirect'], FILTER_VALIDATE_BOOLEAN),
        );
    }
}
