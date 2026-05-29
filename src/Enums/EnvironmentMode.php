<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Enums;

enum EnvironmentMode: string
{
    case STAGING    = 'staging';
    case PRODUCTION = 'production';
}
