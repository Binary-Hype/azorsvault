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

/*
 * The layout loads a self-hosted Umami script on every page, so the privacy
 * policy has to disclose it. It previously claimed the site had "no analytics".
 */
test('privacy policy discloses the reach measurement that the layout loads', function () {
    $response = $this->get(route('privacy'));

    $response->assertOk()
        ->assertSee('Reach Measurement')
        ->assertSee('analytics.notonfire.systems')
        ->assertSee('without cookies')
        ->assertSee('Art. 6 (1) lit. f GDPR')
        ->assertDontSee('no analytics');
});

test('every page that loads the measurement script is covered by the disclosure', function () {
    foreach (['/', route('imprint'), route('privacy')] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('analytics.notonfire.systems', false);
    }
});
