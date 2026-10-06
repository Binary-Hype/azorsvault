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

/*
 * notonfire/php injects the analytics tag before </head>, and only outside
 * local and testing. A tag pasted into the layout as well would count every
 * visit twice.
 */
test('root carries the analytics tag once in its head in production', function () {
    app()->detectEnvironment(fn () => 'production');
    config([
        'notonfire.analytics.id' => '01a0726d-09b5-711e-8e63-0579e92d9c4f',
        'notonfire.analytics.domains' => 'azorsvault.cards',
    ]);

    $html = $this->get('/')->assertOk()->getContent();
    $head = substr($html, 0, strpos($html, '</head>'));

    expect(substr_count($html, 'analytics.notonfire.systems/script.js'))->toBe(1)
        ->and($head)->toContain('data-website-id="01a0726d-09b5-711e-8e63-0579e92d9c4f"')
        ->and($head)->toContain('data-domains="azorsvault.cards"');
});

test('root serves fonts locally without contacting Google', function () {
    $response = $this->get('/')->assertOk();

    $response->assertDontSee('fonts.googleapis.com', false)
        ->assertDontSee('fonts.gstatic.com', false)
        ->assertSee('@font-face', false);
});

/*
 * The layout used to hardcode a preconnect to the analytics host, even where
 * no analytics tag is injected. The tag sits at the end of <head> anyway, so
 * the hint bought nothing.
 */
test('root contacts no analytics host when no analytics tag is injected', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('analytics.notonfire.systems', false);
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
