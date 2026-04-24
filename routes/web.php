<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');
Route::view('/imprint', 'legal.imprint')->name('imprint');
Route::view('/privacy-policy', 'legal.privacy-policy')->name('privacy');
