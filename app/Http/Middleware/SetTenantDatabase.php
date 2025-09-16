<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetTenantDatabase
{
  public function handle($request, Closure $next)
  {
    $user = Auth::user();

    if ($user && $user->tenant) {
      $tenant = $user->tenant;
      /*
      dd([
        'user'=> $user,
        'tenant' => $tenant
      ]);
      */
      // Cerrar conexión previa si ya estaba abierta
      DB::purge('tenant');

      // Configurar conexión tenant dinámicamente
      Config::set('database.connections.tenant', [
        'driver'   => 'mysql',
        'host'     => $tenant->db_host ?? '127.0.0.1',
        'port'     => $tenant->db_port ?? '3306',
        'database' => $tenant->db_name,
        'username' => $tenant->db_user,
        'password' => $tenant->db_password,
        'charset'  => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
      ]);

      // Establecer conexión tenant como predeterminada
      DB::setDefaultConnection('tenant');

      Log::info('Conexión tenant activada', [
        'user' => $user->email,
        'tenant_id' => $tenant->id,
        'db_name' => DB::connection('tenant')->getDatabaseName(),
      ]);
    }

    return $next($request);
  }
}
