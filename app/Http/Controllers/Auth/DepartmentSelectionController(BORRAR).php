<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserRoleDepartmentBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DepartmentSelectionController extends Controller
{
  public function show()
  {
    $assignments = Session::get('pending_assignments', []);
    $roleId = Session::get('pending_role_id');

    if (!$roleId || empty($assignments)) {
      return redirect()->route('login')->withErrors([
        'session' => 'Sesión inválida. Por favor inicie sesión nuevamente.'
      ]);
    }

    return view('auth.department-selection', [
      'assignments' => $assignments,
      'roleName' => Session::get('pending_role_name')
    ]);
  }

  public function select(Request $request)
  {
    $assignmentId = $request->input('assignment_id');
    $assignments = Session::get('pending_assignments', []);

    // Buscar la asignación seleccionada
    $selectedAssignment = collect($assignments)->firstWhere('id', $assignmentId);

    if (!$selectedAssignment) {
      return redirect()->route('login')->withErrors([
        'department' => 'Selección de departamento inválida'
      ]);
    }

    // Obtener usuario autenticado
    $user = auth()->user();

    // Obtener bancos para ESTA combinación rol-departamento
    $banks = UserRoleDepartmentBank::where('user_id', $user->id)
      ->where('role_id', Session::get('pending_role_id'))
      ->where('department_id', $selectedAssignment['department_id'])
      ->pluck('bank_id')
      ->unique()
      ->toArray();

    // Establecer sesión completa
    Session::put([
      'current_role' => Session::get('pending_role_id'),
      'current_department' => $selectedAssignment['department_id'],
      'current_banks' => $banks,
      'current_role_name' => Session::get('pending_role_name'),
      'current_department_name' => $selectedAssignment['department_name'],
      'is_full_access' => false
    ]);

    // Limpiar datos temporales
    Session::forget(['pending_role_id', 'pending_assignments', 'pending_role_name']);

    return redirect()->intended(config('fortify.home'));
  }
}
