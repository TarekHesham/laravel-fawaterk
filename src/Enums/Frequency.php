<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Enums;

enum Frequency: string
{
    case ONCE = 'once';
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
}
