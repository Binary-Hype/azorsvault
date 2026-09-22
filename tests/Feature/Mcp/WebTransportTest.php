<?php

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The MCP 2026-07-28 protocol era drops the initialize handshake: clients send
 * the protocol version as a header on every request instead. These tests pin the
 * stateless path over real HTTP, which the tool-level tests never exercise.
 */
const MCP_PROTOCOL_HEADERS = ['MCP-Protocol-Version' => '2026-07-28'];

test('it lists tools without an initialize handshake', function () {
    $response = $this->withHeaders(MCP_PROTOCOL_HEADERS)->postJson('/mcp/mtg', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
    ]);

    $response->assertOk();

    $toolNames = collect($response->json('result.tools'))->pluck('name');

    expect($toolNames)->toContain(
        'search-card',
        'search-cards',
        'search-cards-advanced',
        'search-rules',
        'get-rule',
    );
});

test('it calls a tool over the web transport', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);

    $response = $this->withHeaders(MCP_PROTOCOL_HEADERS)->postJson('/mcp/mtg', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'search-card',
            'arguments' => ['name' => 'Lightning Bolt'],
        ],
    ]);

    $response->assertOk();

    expect($response->json('result.isError'))->not->toBeTrue();
    expect($response->json('result.content.0.text'))->toContain('Lightning Bolt');
});

test('it reports a failed tool call as an error result', function () {
    $response = $this->withHeaders(MCP_PROTOCOL_HEADERS)->postJson('/mcp/mtg', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'search-card',
            'arguments' => ['name' => 'Nonexistent Card'],
        ],
    ]);

    $response->assertOk();

    expect($response->json('result.isError'))->toBeTrue();
    expect($response->json('result.content.0.text'))->toContain('Card not found');
});

test('it advertises the server over server/discover', function () {
    $response = $this->withHeaders(MCP_PROTOCOL_HEADERS)->postJson('/mcp/mtg', [
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'server/discover',
    ]);

    $response->assertOk();

    expect($response->json('result.supportedVersions'))->toContain('2026-07-28');
    expect($response->json('result.instructions'))->toContain('Magic: The Gathering');
});

test('it still initializes for clients on the previous protocol', function () {
    $response = $this->postJson('/mcp/mtg', [
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => (object) [],
            'clientInfo' => ['name' => 'pest', 'version' => '1.0'],
        ],
    ]);

    $response->assertOk();

    expect($response->json('result.protocolVersion'))->toBe('2025-06-18');
    expect($response->json('result.serverInfo.name'))->toBe('mtg');
});

test('it attaches the icon Link header on the stateless path', function () {
    $response = $this->withHeaders(MCP_PROTOCOL_HEADERS)->postJson('/mcp/mtg', [
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'tools/list',
    ]);

    $response->assertOk();

    expect($response->headers->get('Link'))
        ->toContain('rel="icon"')
        ->toContain('/icon.png')
        ->toContain('/icon.jpeg');
});
