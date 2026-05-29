<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Facades;

use Illuminate\Support\Facades\Facade;
use ElFarmawy\Fawaterk\Fawaterk as FawaterkService;
use ElFarmawy\Fawaterk\Http\FawaterakClient;

/**
 * Fawaterk Facade
 *
 * Provides a static-style access point to the Fawaterk SDK coordinator.
 *
 * @method static FawaterakClient client()
 * @method static bool isStaging()
 *
 * @see \ElFarmawy\Fawaterk\Fawaterk
 */
class Fawaterk extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fawaterk';
    }
}
