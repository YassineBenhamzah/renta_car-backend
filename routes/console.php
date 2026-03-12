<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('rentals:notify-ending', function () {
    $this->call(\App\Console\Commands\NotifyRentalsEndingSoon::class);
})->purpose('Notify users about rentals ending tomorrow');

use Illuminate\Support\Facades\Schedule;

Schedule::command('rentals:notify-ending')->dailyAt('09:00');
