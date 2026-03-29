<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('scryfall:import-cards')
    ->daily()
    ->at('03:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('scryfall:import-rulings')
    ->daily()
    ->at('03:30')
    ->withoutOverlapping()
    ->runInBackground();
