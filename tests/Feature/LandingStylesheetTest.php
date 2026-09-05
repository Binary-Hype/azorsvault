<?php

/**
 * Guards the paint-cost fixes in resources/css/landing.css: animating a
 * filter or box-shadow forces main-thread rasterisation every frame, which
 * pushed first contentful paint past six seconds on the landing page.
 */
function landingStylesheet(): string
{
    return file_get_contents(resource_path('css/landing.css'));
}

test('no keyframe animates a filter', function () {
    preg_match_all('/@keyframes[^{]+\{(?:[^{}]|\{[^{}]*\})*\}/', landingStylesheet(), $matches);

    expect($matches[0])->not->toBeEmpty();

    foreach ($matches[0] as $keyframes) {
        expect($keyframes)->not->toContain('filter:');
    }
});

test('no keyframe animates a box-shadow', function () {
    preg_match_all('/@keyframes[^{]+\{(?:[^{}]|\{[^{}]*\})*\}/', landingStylesheet(), $matches);

    foreach ($matches[0] as $keyframes) {
        expect($keyframes)->not->toContain('box-shadow:');
    }
});

test('the mist drift animates only compositable properties', function () {
    preg_match('/@keyframes codex-drift \{(?:[^{}]|\{[^{}]*\})*\}/', landingStylesheet(), $matches);

    expect($matches[0] ?? '')
        ->toContain('transform:')
        ->not->toContain('filter:');
});

/**
 * Brave on macOS blocks layout for ~2.8s before first paint while resolving
 * the ui-* generics, which Tailwind would otherwise supply by default.
 */
test('the theme font stacks avoid the ui-* generics', function () {
    /* Comments name the offending generics on purpose, so read declarations only. */
    $theme = preg_replace('#/\*.*?\*/#s', '', file_get_contents(resource_path('css/app.css')));

    expect($theme)->toContain('--font-sans:')
        ->toContain('--font-serif:')
        ->toContain('--font-mono:')
        ->not->toContain('ui-sans-serif')
        ->not->toContain('ui-serif')
        ->not->toContain('ui-monospace');
});
