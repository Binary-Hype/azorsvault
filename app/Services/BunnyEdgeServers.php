<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * The addresses Bunny's edge servers connect to the origin from.
 *
 * Bunny publishes the list through its API and changes it as the network
 * grows, so it is synced on a schedule and stored locally rather than
 * hardcoded or fetched during a request.
 */
class BunnyEdgeServers
{
    private const PATH = 'bunny/edge-servers.json';

    /**
     * The stored addresses, or an empty list when nothing has been synced yet.
     *
     * @return list<string>
     */
    public function addresses(): array
    {
        $json = $this->disk()->get(self::PATH);

        if ($json === null) {
            return [];
        }

        $addresses = json_decode($json, true);

        return is_array($addresses) ? array_values(array_filter($addresses, is_string(...))) : [];
    }

    /**
     * Replace the stored addresses.
     *
     * @param  list<string>  $addresses
     */
    public function store(array $addresses): void
    {
        $this->disk()->put(self::PATH, json_encode(array_values($addresses), JSON_THROW_ON_ERROR));
    }

    private function disk(): Filesystem
    {
        return Storage::disk('local');
    }
}
