<?php

use Gopos\Console\Commands\ImportDatabase;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('app.demo_mode')) {
    Schedule::command(ImportDatabase::class)->everyTwoHours();
}
