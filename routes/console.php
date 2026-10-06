<?php

use Illuminate\Support\Facades\Schedule;

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

Schedule::command('rules:import-comprehensive')
    ->weekly()
    ->sundays()
    ->at('04:00')
    ->withoutOverlapping()
    ->runInBackground();
