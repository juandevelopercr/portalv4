<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
*/
//Log::info('Se ejecuta el schedule');

Schedule::command('logs:clean')->yearly();

// En routes/console.php, modifica temporalmente:
Schedule::command('comprobantes:process-emails')
  ->everyFiveMinutes()
  ->withoutOverlapping()
  ->runInBackground()
  ->before(function () {
    Log::info('Iniciando comando comprobantes:process-emails');
  })
  ->after(function () {
    Log::info('Finalizando comando comprobantes:process-emails');
  });

// Puedes agregar logging para depuración
Schedule::call(function () {
  \Illuminate\Support\Facades\Log::info('Scheduler ejecutado');
})->everyMinute();
