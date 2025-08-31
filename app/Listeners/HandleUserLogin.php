<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class HandleUserLogin
{
  public function handle(Login $event)
  {
    // Solo limpiar sesión al iniciar
    Log::info('Evento Login: Limpiando sesión para ' . $event->user->email);
    Session::forget('context');
    Session::forget('pending_roles');
    Session::forget('pending_role');
    Session::forget('pending_departments');
  }
}
