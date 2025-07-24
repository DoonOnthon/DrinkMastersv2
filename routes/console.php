<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CleanupSessions;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean up old sessions
// Artisan::command('sessions:cleanup', CleanupSessions::class)
//     ->purpose('Clean up old and completed play sessions');

Schedule::command('sessions:cleanup')->hourly();
