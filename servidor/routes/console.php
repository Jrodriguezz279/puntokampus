<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:vencer-pedidos')->hourly();
Schedule::command('app:vencer-premium')->daily();
Schedule::command('app:purgar-comprobantes')->daily();
