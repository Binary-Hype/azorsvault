<?php

use App\Services\BunnyEdgeServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

const BUNNY_EDGE_ADDRESS = '185.111.111.156';

const VISITOR_ADDRESS = '203.0.113.7';

beforeEach(function () {
    Route::get('/_client-ip', fn (Request $request): string => (string) $request->ip());
});

test('it resolves the visitor address on requests from a Bunny edge server', function () {
    Storage::fake('local');
    app(BunnyEdgeServers::class)->store([BUNNY_EDGE_ADDRESS]);

    $response = $this->withServerVariables(['REMOTE_ADDR' => BUNNY_EDGE_ADDRESS])
        ->get('/_client-ip', ['X-Forwarded-For' => VISITOR_ADDRESS]);

    $response->assertSeeText(VISITOR_ADDRESS);
});

test('it ignores forwarded addresses from callers that are not edge servers', function () {
    Storage::fake('local');
    app(BunnyEdgeServers::class)->store([BUNNY_EDGE_ADDRESS]);

    $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20'])
        ->get('/_client-ip', ['X-Forwarded-For' => VISITOR_ADDRESS]);

    $response->assertSeeText('198.51.100.20');
});

test('it ignores forwarded addresses before the edge server list is synced', function () {
    Storage::fake('local');

    $response = $this->withServerVariables(['REMOTE_ADDR' => BUNNY_EDGE_ADDRESS])
        ->get('/_client-ip', ['X-Forwarded-For' => VISITOR_ADDRESS]);

    $response->assertSeeText(BUNNY_EDGE_ADDRESS);
});
