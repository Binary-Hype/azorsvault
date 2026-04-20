<?php

test('root serves HTML with icon metadata', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=utf-8')
        ->assertSee('rel="icon"', false)
        ->assertSee('/icon.jpeg', false)
        ->assertSee('og:image', false);
});
