<?php

use App\Services\LatinFontPreload;

/**
 * The font swap was the site's entire remaining layout shift. Preloading the
 * latin files gets them in before first paint, so these guard both that the
 * links are emitted and that the set stays small — preloading every subset
 * would pull down megabytes the pages never render.
 */
test('it preloads one file per rendered family and style', function () {
    $files = app(LatinFontPreload::class)->files();

    expect($files)->toHaveCount(4)
        ->and($files)->each->toEndWith('.woff2')
        ->and($files)->toBe(array_values(array_unique($files)));
});

test('it preloads a file for each configured family', function () {
    $files = app(LatinFontPreload::class)->files();

    foreach (['cormorantgaramond', 'inter', 'jetbrainsmono'] as $family) {
        expect(implode(' ', $files))->toContain($family);
    }
});

test('it renders crossorigin font preload links', function () {
    $html = app(LatinFontPreload::class)->toHtml()->toHtml();

    expect(substr_count($html, '<link rel="preload"'))->toBe(4)
        ->and($html)->toContain('as="font"')
        ->toContain('type="font/woff2"')
        ->toContain('crossorigin');
});

test('it degrades to no links when the fonts have not been fetched', function () {
    config()->set('google-fonts.path', 'fonts-that-were-never-fetched');

    expect(app(LatinFontPreload::class)->files())->toBe([])
        ->and(app(LatinFontPreload::class)->toHtml()->toHtml())->toBe('');
});

test('every page preloads the fonts, not just the landing page', function () {
    foreach (['/', '/imprint', '/privacy-policy'] as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect(substr_count($html, 'rel="preload"'))->toBeGreaterThanOrEqual(4, "missing preloads on {$path}");
    }
});
