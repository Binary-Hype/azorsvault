<?php

namespace App\Http\Middleware;

use App\Services\BunnyEdgeServers;
use Illuminate\Http\Middleware\TrustProxies;

/**
 * Trusts the forwarded headers only on requests that arrive from a Bunny
 * edge server.
 *
 * Behind the CDN every request comes from an edge address, so without this
 * the per-IP rate limit on the MCP endpoint would be shared by everyone routed
 * through the same edge. Trusting every proxy instead would let anyone who
 * reaches the origin directly pick their own address via X-Forwarded-For.
 */
class TrustBunnyProxies extends TrustProxies
{
    public function __construct(private BunnyEdgeServers $edgeServers) {}

    /**
     * @return list<string>
     */
    protected function proxies(): array
    {
        return $this->edgeServers->addresses();
    }
}
