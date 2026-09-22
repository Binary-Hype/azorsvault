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

/*
 * The redesign dropped the blurred, mouse-tracked mist layer: rasterising a
 * page-height blur cost seconds of main-thread paint. The atmosphere is now
 * flat gradients on .codex-bg, and nothing should reintroduce the overlay.
 */
test('the page paints its atmosphere in gradients, not a blurred overlay', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('class="codex-bg', false)
        ->assertDontSee('codex-mist', false)
        ->assertDontSee('data-mist', false);
});

/*
 * The typewriter is gone with the redesign. The incantations are plain text in
 * the markup, so they need no JavaScript to be readable or indexable.
 */
test('the incantations are server-rendered text', function () {
    $response = $this->get('/')->assertOk();

    $response->assertSee('Find me a blue instant under three mana that answers a spell on the stack.')
        ->assertDontSee('data-typewriter', false)
        ->assertDontSee('codex-typed', false);
});

test('root names every tool the mcp server exposes', function () {
    $response = $this->get('/')->assertOk();

    foreach (['search-card', 'search-cards', 'search-cards-advanced', 'search-rules', 'get-rule', 'check-legality', 'get-banned-list', 'validate-deck'] as $tool) {
        $response->assertSee($tool);
    }
});
