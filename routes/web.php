<?php

use Illuminate\Support\Facades\Route;

$htmlStub = fn () => response(
    '<!doctype html><html lang="en"><head>'
    .'<meta charset="utf-8">'
    .'<title>'.e(config('app.name')).'</title>'
    .'<link rel="icon" type="image/png" sizes="128x128" href="/icon.png">'
    .'<link rel="icon" type="image/jpeg" sizes="736x736" href="/icon.jpeg">'
    .'<link rel="apple-touch-icon" sizes="128x128" href="/icon.png">'
    .'<link rel="shortcut icon" href="/favicon.ico">'
    .'<meta property="og:image" content="'.url('/icon.png').'">'
    .'</head><body></body></html>',
    200, ['Content-Type' => 'text/html; charset=utf-8'],
);

Route::get('/', $htmlStub);
Route::get('/mcp', $htmlStub);

Route::get('/mcp/favicon.ico', fn () => response()->file(public_path('favicon.ico')));
Route::get('/mcp/mtg/favicon.ico', fn () => response()->file(public_path('favicon.ico')));
Route::get('/mcp/icon.png', fn () => response()->file(public_path('icon.png')));
Route::get('/mcp/mtg/icon.png', fn () => response()->file(public_path('icon.png')));
Route::get('/mcp/icon.jpeg', fn () => response()->file(public_path('icon.jpeg')));
Route::get('/mcp/mtg/icon.jpeg', fn () => response()->file(public_path('icon.jpeg')));
