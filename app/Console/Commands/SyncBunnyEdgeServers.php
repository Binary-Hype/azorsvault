<?php

namespace App\Console\Commands;

use App\Services\BunnyEdgeServers;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

#[Signature('bunny:sync-edge-servers')]
#[Description('Download the addresses of the Bunny CDN edge servers that the origin trusts as proxies')]
class SyncBunnyEdgeServers extends Command
{
    private const IPV4_URL = 'https://api.bunny.net/system/edgeserverlist';

    private const IPV6_URL = 'https://api.bunny.net/system/edgeserverlist/ipv6';

    public function handle(BunnyEdgeServers $edgeServers): int
    {
        $ipv4 = $this->fetch(self::IPV4_URL, FILTER_FLAG_IPV4);
        $ipv6 = $this->fetch(self::IPV6_URL, FILTER_FLAG_IPV6);

        /*
         * An empty or failed download would stop trusting every edge server,
         * and with it collapse all CDN traffic onto a handful of rate-limit
         * buckets. Keep the previous list until a complete one arrives.
         */
        if ($ipv4 === null || $ipv6 === null || $ipv4 === []) {
            $this->error('Could not download the Bunny edge server list; keeping the stored one.');

            return self::FAILURE;
        }

        $edgeServers->store([...$ipv4, ...$ipv6]);

        $this->info(sprintf('Stored %d IPv4 and %d IPv6 edge server addresses.', count($ipv4), count($ipv6)));

        return self::SUCCESS;
    }

    /**
     * The valid addresses of one family, or null when the download failed.
     *
     * @return list<string>|null
     */
    private function fetch(string $url, int $family): ?array
    {
        try {
            $response = Http::acceptJson()->timeout(30)->retry(3, 1000, throw: false)->get($url);
        } catch (ConnectionException) {
            return null;
        }

        $addresses = $response->successful() ? $response->json() : null;

        if (! is_array($addresses)) {
            return null;
        }

        return array_values(array_filter(
            $addresses,
            fn (mixed $address): bool => is_string($address) && filter_var($address, FILTER_VALIDATE_IP, $family) !== false,
        ));
    }
}
