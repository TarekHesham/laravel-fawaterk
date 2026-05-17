<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk\Http;

/**
 * Abstract base class for all Fawaterak endpoint groups.
 *
 * Endpoint classes extend this to gain access to the configured
 * FawaterakClient while keeping each class small and focused on a
 * single API domain (e.g. invoices, payment keys, refunds).
 *
 * Rules:
 *  - Never put endpoint logic in the service provider.
 *  - Never put validation logic here; that belongs in DTOs.
 *  - Keep each concrete endpoint class focused on one resource.
 */
abstract class BaseEndpoint
{
    public function __construct(protected readonly FawaterakClient $client) {}
}
