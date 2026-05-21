<?php

declare(strict_types=1);

namespace ElFarmawy\Fawaterk;

use ElFarmawy\Fawaterk\Http\FawaterakClient;

/**
 * Main SDK entry point exposed via the Fawaterk facade.
 *
 * This class acts as a thin coordinator that holds a configured
 * FawaterakClient and will expose endpoint group accessors as they
 * are implemented (e.g. invoices(), paymentKeys(), refunds() …).
 *
 * Design constraints from RULES.md:
 *  - No god class: keep this class small; delegate to endpoint groups.
 *  - No endpoint logic here; each endpoint group handles its own domain.
 *  - No validation logic; DTOs are responsible for that.
 */
class Fawaterk
{
    public function __construct(private readonly FawaterakClient $client) {}

    /**
     * The underlying HTTP transport client.
     *
     * Useful for testing or advanced use-cases that require direct access.
     */
    public function client(): FawaterakClient
    {
        return $this->client;
    }

    /**
     * Whether the SDK is operating in sandbox mode.
     */
    public function isSandbox(): bool
    {
        return $this->client->isSandbox();
    }

    public function invoices(): \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint
    {
        return new \ElFarmawy\Fawaterk\Endpoints\InvoiceEndpoint($this->client);
    }
}
