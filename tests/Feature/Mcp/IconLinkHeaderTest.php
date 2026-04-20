<?php

test('mcp responses advertise the icon via Link header', function () {
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

    $response->assertOk();

    expect($response->headers->get('Link'))
        ->toContain('rel="icon"')
        ->toContain('/icon.png')
        ->toContain('/icon.jpeg');
});
