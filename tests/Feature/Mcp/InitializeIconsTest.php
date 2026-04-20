<?php

use Illuminate\Support\Facades\URL;

test('initialize response includes server icon', function () {
    $response = $this->postJson('/mcp/mtg', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => (object) [],
            'clientInfo' => ['name' => 'pest', 'version' => '1.0'],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'mtg')
        ->assertJsonPath('result.serverInfo.version', '1.0.0')
        ->assertJsonPath('result.serverInfo.icons.0.src', URL::asset('icon.jpeg'))
        ->assertJsonPath('result.serverInfo.icons.0.mimeType', 'image/jpeg')
        ->assertJsonPath('result.serverInfo.icons.0.sizes', ['any']);

    $iconSrc = $response->json('result.serverInfo.icons.0.src');
    expect($iconSrc)
        ->toBeString()
        ->toStartWith('http')
        ->toEndWith('/icon.jpeg');
});
