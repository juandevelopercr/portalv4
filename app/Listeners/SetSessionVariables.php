<?php

namespace App\Listeners;

use App\Models\Bank;
use App\Models\Department;
use App\Models\User;
use App\Models\UserRoleDepartmentBank;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class SetSessionVariables
{
  // Bandera estática para evitar doble ejecución
  private static $executed = false;

  //public function handle(Login $event)
  public function handle($user)
  {
    if (self::$executed) {
      return;
    }

    self::$executed = true;

    try {
      // Obtener ID de asignación
      /*
      $assignmentId = session('login_assignment_id');
      //$user = $event->user;

      // Limpiar sesión temporal
      Session::forget('login_assignment_id');

      // Manejar acceso total (IDs que empiezan con 'full-')
      if (str_starts_with($assignmentId, 'full-')) {
        $roleId = str_replace('full-', '', $assignmentId);
        $role = $user->roles()->where('roles.id', (int)$roleId)->first();

        if (!$role) {
          throw new \Exception("Rol con ID {$roleId} no encontrado");
        }

        $this->handleFullAccessRole($user, $roleId, $role);
        return;
      }

      // Obtener la asignación específica
      if (str_contains($assignmentId, '-')) {
        [$roleId, $departmentId] = explode('-', $assignmentId);

        // Verificar si existe la asignación en la tabla de departamentos
        $assignmentExists = DB::table('user_role_department')
          ->where('user_id', $user->id)
          ->where('role_id', $roleId)
          ->where('department_id', $departmentId)
          ->exists();

        if (!$assignmentExists) {
          throw new \Exception("Asignación no encontrada");
        }

        // Obtener bancos para esta asignación específica
        $banks = DB::table('user_role_department_banks')
          ->where('user_id', $user->id)
          ->where('role_id', $roleId)
          ->where('department_id', $departmentId)
          ->pluck('bank_id')
          ->toArray();

        // Obtener nombres
        $role = Role::find($roleId);
        $department = Department::find($departmentId);

        // Establecer sesión
        Session::put([
          'current_role' => $roleId,
          'current_department' => [$departmentId],
          'current_banks' => $banks,
          'current_role_name' => $role ? $role->name : 'Rol no encontrado',
          'current_department_name' => $department ? $department->name : 'Departamento no encontrado',
          'is_full_access' => false
        ]);
      } else {
        throw new \Exception("Formato de ID de asignación no reconocido");
      }
        */
    } catch (\Exception $e) {
      // Manejar error y redirigir
      Auth::logout();
      Session::flash('error', 'Error al establecer el contexto de seguridad: ' . $e->getMessage());
      return redirect()->route('login');
    }
  }

  private function handleFullAccessRole(User $user, $roleId, $role)
  {
    // Para roles de acceso completo, no necesitamos bancos específicos
    // Obtener todos los departamentos activos
    $departments = Department::where('active', 1)->pluck('id')->toArray();

    // Obtener todos los bancos activos
    $banks = Bank::where('active', 1)->pluck('id')->toArray();

    // Establecer sesión
    Session::put([
      'current_role' => $roleId,
      'current_department' => $departments, // Todos los departamentos
      'current_banks' => $banks, // Todos los bancos activos
      'current_role_name' => $role->name,
      'current_department_name' => 'Todos los Departamentos',
      'is_full_access' => true
    ]);
  }
}
