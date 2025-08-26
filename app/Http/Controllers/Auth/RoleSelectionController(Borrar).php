<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class RoleSelectionController extends Controller
{
  public function show()
  {
    $roles = Session::get('pending_roles');
    return view('auth.role-selection', compact('roles'));
  }

  public function select(Request $request)
  {
    $roleId = $request->input('role');
    $roles = Session::get('pending_roles');
    $selectedRole = $roles->firstWhere('id', $roleId);

    if (!$selectedRole) {
      return back()->withErrors(['role' => 'Rol inválido']);
    }

    // Guardar el rol seleccionado en sesión
    Session::put('pending_role', $selectedRole);
    Session::forget('pending_roles');

    $user = auth()->user();

    // Verificar si este rol necesita departamento
    if (in_array($selectedRole->name, User::ROLES_ALL_DEPARTMENTS)) {
      // Roles que no necesitan selección de departamento
      $banks = $user->getContextBanks($selectedRole->id);

      Session::put('context', [
        'role' => $selectedRole->name,
        'role_id' => $selectedRole->id,
        'banks' => $banks
      ]);

      return redirect()->intended(route('index'));
    }

    // Obtener departamentos para este rol
    $departments = $user->roleAssignments
      ->where('role_id', $selectedRole->id)
      ->pluck('department')
      ->unique()
      ->filter();

    // Si solo hay un departamento, asignarlo automáticamente
    if ($departments->count() === 1) {
      $department = $departments->first();

      $banks = $user->roleAssignments
        ->where('role_id', $selectedRole->id)
        ->where('department_id', $department->id)
        ->pluck('bank_id')
        ->unique()
        ->toArray();

      Session::put('context', [
        'role' => $selectedRole->name,
        'role_id' => $selectedRole->id,
        'department' => $department->id,
        'department_name' => $department->name,
        'banks' => $banks
      ]);

      return redirect()->intended(route('index'));
    }

    // Múltiples departamentos - redirigir a selección
    Session::put('pending_departments', $departments);
    return redirect()->route('department-selection');
  }
}
