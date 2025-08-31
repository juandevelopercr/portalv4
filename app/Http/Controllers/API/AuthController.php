<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
  public function getRoles(Request $request)
  {
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();
    if (!$user) {
      return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    return response()->json([
      'roles' => $user->roles->map(function ($role) {
        return [
          'id' => $role->id,
          'name' => $role->name,
          'requires_department' => !in_array($role->name, User::ROLES_ALL_DEPARTMENTS)
        ];
      }),
      'status' => 'success'
    ]);
  }

  public function getDepartments(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
      'role_id' => 'required|exists:roles,id'
    ]);

    $user = User::where('email', $request->email)->first();
    $departments = $user->roleAssignments()
      ->where('role_id', $request->role_id)
      ->with('department')
      ->get()
      ->pluck('department')
      ->unique()
      ->values();

    return response()->json([
      'departments' => $departments,
      'status' => 'success'
    ]);
  }
}
