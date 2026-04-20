<?php

test('root serves HTML with icon metadata', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8')
        ->assertSee('rel="icon"', false)
        ->assertSee('/icon.png', false)
        ->assertSee('/icon.jpeg', false)
        ->assertSee('og:image', false);
});

test('mcp parent serves the same HTML stub', function () {
    $this->get('/mcp')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8')
        ->assertSee('rel="icon"', false);
});

test('icon aliases under the mcp path serve the asset', function (string $path, string $expectedMime) {
    $this->get($path)
        ->assertOk()
        ->assertHeader('Content-Type', $expectedMime);
})->with([
    ['/mcp/favicon.ico', 'image/vnd.microsoft.icon'],
    ['/mcp/mtg/favicon.ico', 'image/vnd.microsoft.icon'],
    ['/mcp/icon.png', 'image/png'],
    ['/mcp/mtg/icon.png', 'image/png'],
    ['/mcp/icon.jpeg', 'image/jpeg'],
    ['/mcp/mtg/icon.jpeg', 'image/jpeg'],
]);
