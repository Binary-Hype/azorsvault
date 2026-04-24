<?php

test('imprint page renders with legal notice content', function () {
    $this->get(route('imprint'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8')
        ->assertSee('Imprint')
        ->assertSee('Information according to § 5 TMG')
        ->assertSee('Tobias Kokesch')
        ->assertSee('Gartenstraße 8')
        ->assertSee('hello@binary-hype.com')
        ->assertSee('Azorsvault');
});

test('privacy policy page renders with GDPR disclosures', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8')
        ->assertSee('Privacy Policy')
        ->assertSee('Notice Concerning the Responsible Party')
        ->assertSee('Hetzner')
        ->assertSee('Server Log Files')
        ->assertSee('Scryfall')
        ->assertSee('MCP Endpoint', false);
});

test('landing footer links to the legal pages', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('imprint'), false)
        ->assertSee(route('privacy'), false)
        ->assertDontSee('>Community<', false);
});
