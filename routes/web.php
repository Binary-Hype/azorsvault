<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response(
    '<!doctype html><html lang="en"><head>'
    .'<meta charset="utf-8">'
    .'<title>'.e(config('app.name')).'</title>'
    .'<link rel="icon" type="image/jpeg" sizes="736x736" href="/icon.jpeg">'
    .'<link rel="apple-touch-icon" sizes="736x736" href="/icon.jpeg">'
    .'<link rel="shortcut icon" href="/favicon.ico">'
    .'<meta property="og:image" content="'.url('/icon.jpeg').'">'
    .'</head><body></body></html>',
    200, ['Content-Type' => 'text/html; charset=utf-8'])
);
