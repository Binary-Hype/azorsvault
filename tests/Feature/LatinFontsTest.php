<?php

use App\Services\LatinFonts;

/**
 * The font swap was the site's entire remaining layout shift. Preloading the
 * latin files gets them in before first paint, so these guard both that the
 * links are emitted and that the set stays small — preloading every subset
 * would pull down megabytes the pages never render.
 */
test('it preloads one file per rendered family and style', function () {
    $files = app(LatinFonts::class)->files();

    expect($files)->toHaveCount(4)
        ->and($files)->each->toEndWith('.woff2')
        ->and($files)->toBe(array_values(array_unique($files)));
});

test('it preloads a file for each configured family', function () {
    $files = app(LatinFonts::class)->files();

    foreach (['cormorantgaramond', 'inter', 'jetbrainsmono'] as $family) {
        expect(implode(' ', $files))->toContain($family);
    }
});

test('it renders crossorigin font preload links', function () {
    $html = app(LatinFonts::class)->toHtml()->toHtml();

    expect(substr_count($html, '<link rel="preload"'))->toBe(4)
        ->and($html)->toContain('as="font"')
        ->toContain('type="font/woff2"')
        ->toContain('crossorigin');
});

test('it degrades to no links when the fonts have not been fetched', function () {
    config()->set('google-fonts.path', 'fonts-that-were-never-fetched');

    expect(app(LatinFonts::class)->files())->toBe([])
        ->and(app(LatinFonts::class)->toHtml()->toHtml())->toBe('');
});

test('every page preloads the fonts, not just the landing page', function () {
    foreach (['/', '/imprint', '/privacy-policy'] as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect(substr_count($html, 'rel="preload"'))->toBeGreaterThanOrEqual(4, "missing preloads on {$path}");
    }
});

/*
 * The package's @googlefonts directive inlined all 82 @font-face rules (~34 KB)
 * into every response; these pages only ever render the basic-latin subset.
 */
test('it inlines only the basic-latin font faces', function () {
    $css = app(LatinFonts::class)->toStyleTag()->toHtml();

    expect($css)->toStartWith('<style>')
        ->and(substr_count($css, '@font-face'))->toBe(14)
        ->and($css)->toContain('U+0000-00FF')
        ->and(strlen($css))->toBeLessThan(12000);
});

test('the inlined font css names every rendered family', function () {
    $css = app(LatinFonts::class)->toStyleTag()->toHtml();

    foreach (['Cormorant Garamond', 'Inter', 'JetBrains Mono'] as $family) {
        expect($css)->toContain($family);
    }
});

test('it never references a third-party font host', function () {
    foreach (['/', '/imprint', '/privacy-policy'] as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect($html)->not->toContain('fonts.googleapis.com')
            ->and($html)->not->toContain('fonts.gstatic.com');
    }
});

test('it degrades to no stylesheet when the fonts have not been fetched', function () {
    config()->set('google-fonts.path', 'fonts-that-were-never-fetched');

    expect(app(LatinFonts::class)->toStyleTag()->toHtml())->toBe('');
});
