<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// گرتنی باکەپی ئۆتۆماتیکی داتابەیس هەموو شەوێک کاتژمێر 11:30
Schedule::command('backup:run --auto')->dailyAt('23:30');
