<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled maintenance
|--------------------------------------------------------------------------
|
| These only run when the cPanel cron job documented in docs/DEPLOYMENT.md is
| active. Without that cron, nothing here executes and the commands have to be
| run by hand from the terminal.
|
*/

// Uploaded files are never deleted the moment a record is removed, because a
// failed save cannot be rolled back for a file. Sweeping nightly keeps storage
// tidy without ever risking a live invitation.
Schedule::command('jall:prune-orphans --force')
    ->dailyAt('03:00')
    ->withoutOverlapping();
