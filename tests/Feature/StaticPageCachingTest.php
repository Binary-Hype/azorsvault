<?php

dataset('static pages', ['home', 'imprint', 'privacy']);

test('static pages are publicly cacheable', function (string $routeName) {
    $cacheControl = $this->get(route($routeName))
        ->assertOk()
        ->assertHeader('ETag')
        ->headers->get('Cache-Control');

    expect($cacheControl)
        ->toContain('public')
        ->toContain('max-age=300')
        ->toContain('s-maxage=3600')
        ->toContain('stale-while-revalidate=86400')
        ->not->toContain('private')
        ->not->toContain('no-cache');
})->with('static pages');

test('static pages set no cookies', function (string $routeName) {
    $response = $this->get(route($routeName))->assertOk();

    expect($response->headers->getCookies())->toBeEmpty();
})->with('static pages');

test('static pages answer a matching ETag with 304', function (string $routeName) {
    $etag = $this->get(route($routeName))->assertOk()->headers->get('ETag');

    $this->get(route($routeName), ['If-None-Match' => $etag])
        ->assertStatus(304);
})->with('static pages');
