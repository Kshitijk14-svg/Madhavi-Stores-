<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('otp:cleanup', function () {
    $deleted = \Illuminate\Support\Facades\DB::table('otp_codes')
        ->where('expires_at', '<', now())
        ->delete();
    $this->info("Deleted {$deleted} expired OTP records.");
})->purpose('Delete expired OTP codes');

Schedule::command('otp:cleanup')->everyThirtyMinutes();
Schedule::command('session:gc')->daily();
// On shared hosting: process queued emails (OTP, invoice) once per minute via the scheduler cron
Schedule::command('queue:work --once --tries=3 --max-time=50')->everyMinute()->withoutOverlapping();

