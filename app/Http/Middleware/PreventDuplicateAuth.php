<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PreventDuplicateAuth
{
  public function handle($request, Closure $next)
  {
    Log::info('Se ejecuta PreventDuplicateAuth');
    // Si ya está autenticado y tiene rol en sesión, redirigir
    if (Auth::check() && session('current_role')) {
      Log::warning('Se ejecuta PreventDuplicateAuth y la session estaba iniciada se redirecciona al home', [
        'current_role' => session('current_role')
      ]);
      return redirect()->intended(config('fortify.home'));
    }

    return $next($request);
  }
}
