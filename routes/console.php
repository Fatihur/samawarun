<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run queue worker every minute (for shared hosting)
Schedule::command('queue:work --once --max-time=55 --sleep=3 --tries=1')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/queue.log'));
