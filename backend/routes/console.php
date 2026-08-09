<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes - Scheduler
|--------------------------------------------------------------------------
|
| Cek timeout sesi setiap 1 menit.
| Jalankan: php artisan schedule:work (development)
| Atau tambahkan cron: * * * * * php artisan schedule:run
|
*/

Schedule::command('chat:check-timeout')->everyMinute();
