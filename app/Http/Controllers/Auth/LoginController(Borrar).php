<?php
// app/Http/Controllers/Auth/LoginController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class LoginController extends Controller
{
  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => 'required|email',
      'password' => 'required',
      'role_id' => 'required|exists:roles,id'
    ]);

    // Verificar credenciales
    if (!Auth::attempt($request->only('email', 'password'))) {
      throw ValidationException::withMessages([
        'email' => __('auth.failed'),
      ]);
    }

    $user = Auth::user();
    $role = Role::find($request->role_id);

    // Verificar que el usuario tenga este rol
    if (!$user->roles->contains('id', $role->id)) {
      Auth::logout();
      return back()->withErrors([
        'role_id' => 'No tienes permiso para acceder con este rol'
      ])->withInput();
    }

    // Establecer contexto de sesión
    $this->setSessionContext($user, $role);

    return redirect()->intended(route('index'));
  }

  private function setSessionContext(User $user, Role $role)
  {
    dd("setSessionContext");
    // Obtener departamento asociado al rol (si aplica)
    $department = $user->roleAssignments()
      ->where('role_id', $role->id)
      ->with('department')
      ->first()
      ?->department;

    // Obtener bancos asociados al rol
    $banks = $user->roleAssignments()
      ->where('role_id', $role->id)
      ->pluck('bank_id')
      ->toArray();

    // Establecer contexto en sesión
    session([
      'context' => [
        'role' => $role->name,
        'role_id' => $role->id,
        'department' => $department->id ?? null,
        'department_name' => $department->name ?? null,
        'banks' => $banks
      ]
    ]);
  }
}
