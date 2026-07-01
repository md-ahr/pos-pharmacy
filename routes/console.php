<?php

use App\Console\Commands\BackupDatabaseCommand;
use App\Console\Commands\CheckExpiringBatchesCommand;
use App\Console\Commands\GenerateDailyReportCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(CheckExpiringBatchesCommand::class)
    ->dailyAt(config('pharmacy.schedule.expiry_check_time', '06:00'))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(BackupDatabaseCommand::class)
    ->dailyAt(config('pharmacy.schedule.backup_time', '02:00'))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(GenerateDailyReportCommand::class)
    ->dailyAt(config('pharmacy.schedule.daily_report_time', '23:55'))
    ->withoutOverlapping()
    ->onOneServer();
