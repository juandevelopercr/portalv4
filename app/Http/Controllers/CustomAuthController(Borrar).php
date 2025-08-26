<?php

namespace App\Http\Controllers;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;

class CustomAuthController extends FortifyAuthController
{
  public function store(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required',
      'role_id' => 'required|exists:roles,id',
      'department_id' => 'required|exists:departments,id'
    ]);

    if (Auth::attempt($request->only('email', 'password'))) {
      $user = Auth::user();

      // Verificar acceso al contexto
      $hasAccess = $user->roleDepartments()
        ->where('role_id', $request->role_id)
        ->where('department_id', $request->department_id)
        ->exists();

      if ($hasAccess) {
        // Guardar contexto en sesión
        session()->put([
          'current_role_id' => $request->role_id,
          'current_department_id' => $request->department_id,
          'current_bank_ids' => $user->getContextBanks($request->role_id)
        ]);

        return redirect()->intended(config('fortify.home'));
      }

      Auth::logout();
    }

    return back()->withErrors([
      'email' => 'Credenciales inválidas o acceso no autorizado',
    ]);
  }
}
