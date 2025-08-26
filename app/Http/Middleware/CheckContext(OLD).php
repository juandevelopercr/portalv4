<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckContext
{
  public function handle(Request $request, Closure $next): Response
  {
    if (!Session::has('context')) {
      return redirect()->route('login')->withErrors([
        'session' => 'La sesión ha expirado, por favor inicie sesión nuevamente'
      ]);
    }

    return $next($request);
  }
}
