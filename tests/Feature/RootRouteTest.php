<?php

test('root serves HTML with icon metadata', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8')
        ->assertSee('rel="icon"', false)
        ->assertSee('/icon.png', false)
        ->assertSee('/icon.jpeg', false)
        ->assertSee('og:image', false)
        ->assertSee('/logo.svg', false);
});

test('root renders the Azorsvault landing hero', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Azorsvault')
        ->assertSee('An MCP server for Magic: The Gathering')
        ->assertSee('claude mcp add --transport http azorsvault', false)
        ->assertSee('/mcp/mtg', false)
        ->assertSee('search-cards-advanced');
});

test('logo.svg ships as a public asset matching the navbar mark', function () {
    $path = public_path('logo.svg');
    expect($path)->toBeFile();
    expect(file_get_contents($path))->toContain('<svg')->toContain('</svg>');
});
