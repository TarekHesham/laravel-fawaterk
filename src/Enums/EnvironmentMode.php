<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Enums;

enum EnvironmentMode: string
{
    case SANDBOX = 'sandbox';
    case PRODUCTION = 'production';
}
