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

test('root includes the analytics script', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('https://analytics.notonfire.systems/script.js', false)
        ->assertSee('data-website-id="01a0726d-09b5-711e-8e63-0579e92d9c4f"', false)
        ->assertSee('data-domains="azorsvault.cards"', false)
        ->assertSee('data-do-not-track="true"', false);
});

test('root serves fonts locally without contacting Google', function () {
    $response = $this->get('/')->assertOk();

    $response->assertDontSee('fonts.googleapis.com', false)
        ->assertDontSee('fonts.gstatic.com', false)
        ->assertSee('@font-face', false);
});

test('root preconnects to the analytics origin', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<link rel="preconnect" href="https://analytics.notonfire.systems"', false);
});

test('the blurred mist layer is pinned to the viewport', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('class="codex-mist fixed inset-0', false)
        ->assertDontSee('class="codex-mist absolute', false);
});

test('typewriter text is server-rendered so the card reserves its final height', function () {
    $response = $this->get('/')->assertOk();

    $response->assertSee('<span class="codex-typed" data-typewriter data-start-delay="900">find me a blue instant under 3 mana with flash that counters a spell</span>', false)
        ->assertDontSee('data-text=', false);
});
