<?php

use App\Http\Controllers\API\AuthController;
use App\Models\Department;
use App\Models\User;
use App\Models\UserRoleDepartmentBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

Route::get('/user-roles', function (Request $request) {
  $email = $request->query('email');
  $user = User::where('email', $email)->first();

  if (!$user) {
    return response()->json([]);
  }

  // Especificar explícitamente las columnas con nombres de tabla
  return response()->json(
    $user->roles()->get(['roles.id', 'roles.name'])->toArray()
  );
})->middleware('web'); // Usar middleware web para acceso desde la vista

Route::get('/user-assignments', function (Request $request) {
  $email = $request->query('email');
  $user = User::where('email', $email)->first();

  if (!$user) {
    return response()->json([]);
  }

  // Obtener asignaciones específicas (agrupadas por rol y departamento)
  $assignments = DB::table('user_role_department_banks')
    ->where('user_id', $user->id)
    ->join('roles', 'user_role_department_banks.role_id', '=', 'roles.id')
    ->join('departments', 'user_role_department_banks.department_id', '=', 'departments.id')
    ->select(
      'user_role_department_banks.role_id',
      'user_role_department_banks.department_id',
      'roles.name as role_name',
      'departments.name as department_name'
    )
    ->distinct()
    ->get()
    ->map(function ($item) {
      return [
        'id' => $item->role_id . '-' . $item->department_id,
        'display' => $item->role_name . ' - ' . $item->department_name,
        'role_id' => $item->role_id,
        'role_name' => $item->role_name,
        'department_id' => $item->department_id,
        'department_name' => $item->department_name
      ];
    });

  // Agregar roles con acceso total
  $fullAccessRoles = $user->roles()
    ->whereIn('name', User::ROLES_ALL_DEPARTMENTS)
    ->get()
    ->map(function ($role) {
      return [
        'id' => 'full-' . $role->id,
        'display' => $role->name . ' (Acceso Total)',
        'role_id' => $role->id,
        'role_name' => $role->name,
        'department_id' => null,
        'department_name' => 'Todos los Departamentos'
      ];
    });

  return response()->json($fullAccessRoles->concat($assignments));
});

/*
Route::get('/user-assignments', function (Request $request) {
  $email = $request->query('email');
  $user = User::where('email', $email)->first();

  if (!$user) {
    return response()->json([]);
  }

  // Obtener asignaciones específicas (agrupadas por rol y departamento)
  $assignments = UserRoleDepartmentBank::where('user_id', $user->id)
    ->with(['role', 'department'])
    ->get()
    ->groupBy(['role_id', 'department_id']) // Agrupa para eliminar duplicados
    ->map(function ($group) {
      $first = $group->first()->first(); // Primer registro del grupo
      return [
        'id' => $first->id,
        'display' => $first->role->name . ' - ' . $first->department->name,
        'role_id' => $first->role_id,
        'role_name' => $first->role->name,
        'department_id' => $first->department_id,
        'department_name' => $first->department->name
      ];
    })
    ->values(); // Reindexa el array

  // Agregar roles con acceso total
  $fullAccessRoles = $user->roles()
    ->whereIn('name', User::ROLES_ALL_DEPARTMENTS)
    ->get()
    ->map(function ($role) {
      return [
        'id' => 'full-' . $role->id,
        'display' => $role->name . ' (Acceso Total)',
        'role_id' => $role->id,
        'role_name' => $role->name,
        'department_id' => null,
        'department_name' => 'Todos los Departamentos'
      ];
    });

  return response()->json($fullAccessRoles->merge($assignments));
});
*/
