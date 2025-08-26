<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class CheckSession
{
  protected $excludedRoutes = [
    'login',
    'logout',
    'select-context',
    'password.request',
    'password.reset',
    'password.email',
    'password.update',
    'verification.notice',
    'verification.verify',
    'verification.send'
  ];

  public function handle($request, Closure $next)
  {
    $currentRoute = Route::currentRouteName();

    if (in_array($currentRoute, $this->excludedRoutes)) {
      return $next($request);
    }

    if (Auth::check()) {
      $user = Auth::user();

      // Para roles con acceso total, solo necesitamos el rol
      if ($user && session('is_full_access')) {
        if (!session('current_role')) {
          Log::warning('Sesión incompleta para acceso total', [
            'user_id' => $user->id
          ]);
          Auth::logout();
          return redirect()->route('login')->withErrors([
            'session' => 'Configuración de sesión incompleta'
          ]);
        }
        return $next($request);
      }

      // Para roles normales, verificar rol y departamento
      if (!session('current_role') || !session('current_department')) {
        Log::warning('Sesión incompleta para rol normal', [
          'user_id' => Auth::id(),
          'session' => session()->all()
        ]);

        Auth::logout();
        return redirect()->route('login')->withErrors([
          'session' => 'Configuración de sesión incompleta'
        ]);
      }
    }

    return $next($request);
  }
}
