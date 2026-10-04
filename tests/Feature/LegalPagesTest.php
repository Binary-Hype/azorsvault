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
 * notonfire/php adds NotOnFire's analytics tag to every page and reports
 * errors to it, so the privacy policy has to disclose both. It previously
 * claimed the site had "no analytics". NotOnFire is presented as one service,
 * without naming the software behind it.
 */
test('privacy policy discloses NotOnFire reach measurement and error tracking', function () {
    $response = $this->get(route('privacy'));

    $response->assertOk()
        ->assertSee('NotOnFire')
        ->assertSee('Reach Measurement')
        ->assertSee('analytics.notonfire.systems')
        ->assertSee('without cookies')
        ->assertSee('Error Tracking')
        ->assertSee('error.notonfire.systems')
        ->assertSee('Art. 6 (1) lit. f GDPR')
        ->assertDontSee('no analytics')
        ->assertDontSee('Umami')
        ->assertDontSee('GlitchTip');
});

test('every page that loads the measurement script is covered by the disclosure', function () {
    foreach (['/', route('imprint'), route('privacy')] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('analytics.notonfire.systems', false);
    }
});
