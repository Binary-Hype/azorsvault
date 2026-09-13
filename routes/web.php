<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
 * These pages are static and never read the session, so they skip the cookie
 * and session middleware. A response that sets cookies is private, and no
 * browser or proxy cache will store it.
 */
Route::withoutMiddleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    PreventRequestForgery::class,
])->middleware('cache.headers:public;max_age=300;s_maxage=3600;stale_while_revalidate=86400;etag')->group(function () {
    Route::view('/', 'landing')->name('home');
    Route::view('/imprint', 'legal.imprint')->name('imprint');
    Route::view('/privacy-policy', 'legal.privacy-policy')->name('privacy');
});
