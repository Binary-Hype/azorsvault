<?php

use App\Mcp\Servers\MtgServer;

test('the sitemap lists every indexable page', function () {
    $response = $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=utf-8');

    $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
        ->assertSee('http://www.sitemaps.org/schemas/sitemap/0.9', false);

    foreach (['home', 'imprint', 'privacy'] as $routeName) {
        $response->assertSee('<loc>'.route($routeName).'</loc>', false);
    }
});

test('robots.txt points crawlers at the sitemap', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)
        ->toContain('User-agent: *')
        ->toContain('Allow: /')
        ->toContain('Sitemap: https://azorsvault.cards/sitemap.xml');
});

test('llms.txt names the endpoint and every registered tool', function () {
    $response = $this->get(route('llms'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8');

    $response->assertSee('# Azorsvault', false)
        ->assertSee(url('/mcp/mtg'), false);

    foreach ((new ReflectionClass(MtgServer::class))->getDefaultProperties()['tools'] as $tool) {
        $response->assertSee(app($tool)->name(), false);
    }
});

test('llms.txt links every page the sitemap carries', function () {
    $response = $this->get(route('llms'))->assertOk();

    foreach (['home', 'imprint', 'privacy'] as $routeName) {
        $response->assertSee(route($routeName), false);
    }
});

test('pages carry a canonical url and indexing directives', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route($routeName).'">', false)
        ->assertSee('max-image-preview:large', false)
        ->assertSee('<meta name="robots" content="index, follow', false);
})->with(['home', 'imprint', 'privacy']);

test('pages carry social card metadata', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('og:site_name', false)
        ->assertSee('og:locale', false)
        ->assertSee('twitter:card', false)
        ->assertSee('summary_large_image', false);
})->with(['home', 'imprint', 'privacy']);

/*
 * Social platforms crop to roughly 1.91:1 and reject anything small, so the
 * card is a dedicated 1200x630 asset rather than the square site icon.
 */
test('pages share a correctly sized social card', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee(url('/og-image.png'), false)
        ->assertSee('<meta property="og:image:width" content="1200">', false)
        ->assertSee('<meta property="og:image:height" content="630">', false);
})->with(['home', 'imprint', 'privacy']);

test('the social card asset exists at the size it advertises', function () {
    $path = public_path('og-image.png');

    expect($path)->toBeFile();
    expect(getimagesize($path))->toMatchArray([0 => 1200, 1 => 630]);
});

test('every page carries the sitewide entity graph', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('"@type":"WebSite"', false);
})->with(['home', 'imprint', 'privacy']);

test('the landing page describes itself as a software application', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('"@type":"SoftwareApplication"', false)
        ->assertSee('"applicationSubCategory":"MCP server"', false)
        ->assertSee('"isAccessibleForFree":true', false);
});

test('the title and description name the primary subject', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<title>Azorsvault — Magic: The Gathering MCP Server for Claude</title>', false)
        ->assertSee('An MCP server for Magic: The Gathering', false);
});
