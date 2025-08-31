<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetTenantDatabase
{
  public function handle($request, Closure $next)
  {
    $user = Auth::user();
    if ($user) {
      $tenant = $user->tenant;

      // Configurar conexión tenant
      config([
        'database.connections.tenant.database' => $tenant->db_name,
        'database.connections.tenant.username' => $tenant->db_user,
        'database.connections.tenant.password' => $tenant->db_password,
      ]);

      // Establecer por defecto
      DB::setDefaultConnection('tenant');

      Log::info('Conexión tenant activada', [
        'user' => $user->email,
        'tenant_id' => $tenant->id,
        'db_name' => DB::connection()->getDatabaseName()
      ]);
    }

    return $next($request);
  }
}
