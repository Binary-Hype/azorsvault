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
