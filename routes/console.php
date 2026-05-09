<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('wa:sync-media')->hourly();
Schedule::command('broadcast:process-scheduled')->everyMinute();
// Backup otomatis ke Telegram
try {
    if (Schema::hasTable('settings')) {
        $backupTime = \App\Models\Setting::get('backup_time', '01:00');
        Schedule::command('db:backup-telegram')->dailyAt($backupTime);
    }
} catch (\Exception $e) {
    // Skip if DB not ready
}
