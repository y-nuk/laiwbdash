<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 順位取得：毎日 03:00（取得後に自動でアラートチェック）
Schedule::command('rankings:fetch')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/rankings-fetch.log'));

// 月次レポートの自動配信：毎日 09:00 に配信予定の予約をスキャン
Schedule::command('reports:send-scheduled')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/reports-send.log'));
