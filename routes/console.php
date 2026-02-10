<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('backup:clean')->weeklyOn(0, '02:00');
\Illuminate\Support\Facades\Schedule::command('backup:run')->weeklyOn(0, '03:00');
\Illuminate\Support\Facades\Schedule::command('hr:auto-checkout')->dailyAt('20:30');
