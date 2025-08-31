<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define las tareas programadas del sistema.
     */
    protected function schedule(Schedule $schedule)
    {
      // Nota esto no se usa en laravel 11 se usa routes\console.php
      // Ejemplo de tarea programada
      // $schedule->command('inspire')->hourly();
      $schedule->command('logs:clean')->yearly();
    }


    /**
     * Registra los comandos de la aplicación.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
