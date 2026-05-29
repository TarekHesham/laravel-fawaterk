<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Enums;

enum WebhookType: string
{
    case PAID     = 'paid';
    case FAILED   = 'failed';
    case CANCELED = 'canceled';
    case REFUND   = 'refund';
}
