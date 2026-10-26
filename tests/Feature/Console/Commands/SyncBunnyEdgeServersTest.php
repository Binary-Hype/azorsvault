<?php

use App\Services\BunnyEdgeServers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function fakeBunnyEdgeServerLists(mixed $ipv4, mixed $ipv6 = ['2400:52e0:1500::714:1'], int $status = 200): void
{
    Http::preventStrayRequests();
    Http::fake([
        'api.bunny.net/system/edgeserverlist' => Http::response($ipv4, $status),
        'api.bunny.net/system/edgeserverlist/ipv6' => Http::response($ipv6, $status),
    ]);
}

test('it stores the IPv4 and IPv6 edge server addresses', function () {
    Storage::fake('local');
    fakeBunnyEdgeServerLists(['185.111.111.156', '89.187.188.227'], ['2400:52e0:1500::714:1']);

    $this->artisan('bunny:sync-edge-servers')->assertSuccessful();

    expect(app(BunnyEdgeServers::class)->addresses())
        ->toBe(['185.111.111.156', '89.187.188.227', '2400:52e0:1500::714:1']);
});

test('it drops entries that are not addresses of the expected family', function () {
    Storage::fake('local');
    fakeBunnyEdgeServerLists(['185.111.111.156', 'not-an-ip', '2400:52e0:1500::715:1', 42], ['2400:52e0:1500::714:1', '89.187.188.227']);

    $this->artisan('bunny:sync-edge-servers')->assertSuccessful();

    expect(app(BunnyEdgeServers::class)->addresses())
        ->toBe(['185.111.111.156', '2400:52e0:1500::714:1']);
});

test('it keeps the stored list when the download fails', function (mixed $ipv4, int $status) {
    Storage::fake('local');
    app(BunnyEdgeServers::class)->store(['185.111.111.156']);
    fakeBunnyEdgeServerLists($ipv4, status: $status);

    $this->artisan('bunny:sync-edge-servers')->assertFailed();

    expect(app(BunnyEdgeServers::class)->addresses())->toBe(['185.111.111.156']);
})->with([
    'server error' => [['89.187.188.227'], 500],
    'empty list' => [[], 200],
    'not a list' => ['<html>maintenance</html>', 200],
]);
