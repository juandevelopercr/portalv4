<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Bank;
use App\Models\Department;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Spatie\Permission\Models\Role;

class FortifyServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  /*
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
    */
  public function boot(): void
  {
    Fortify::createUsersUsing(CreateNewUser::class);
    Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
    Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
    Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

    // Solo para debug - remover en producción
    // Verificar si ya se inicializó para evitar múltiples ejecuciones
    if (app()->resolved('fortify')) {
      return;
    }

    Log::info('FortifyServiceProvider initialized - First time');


    // Personalización del proceso de login
    /*
    Fortify::authenticateUsing(function (Request $request) {
      $request->validate([
        'email' => 'required|email',
        'password' => 'required',
      ]);

      $user = User::where('email', $request->email)->first();

      if (!$user) {
        throw ValidationException::withMessages([
          'email' => __('Credenciales incorrectas. Verifique su correo y contraseña.'),
        ]);
      }

      // Verificar si el usuario está activo
      if (!$user->active) {
        throw ValidationException::withMessages([
          'email' => __('Su cuenta está desactivada. Contacte al administrador.'),
        ]);
      }

      // Determinar rol automáticamente para ciertos casos
      $role = $this->determineUserRole($user, $request->role_id);

      if (!$role) {
        throw ValidationException::withMessages([
          'role_id' => __('Seleccione un rol válido para continuar.'),
        ]);
      }

      // Determinar departamento
      $departmentId = $this->determineDepartment($user, $role, $request->department_id);

      if ($departmentId === null) {
        throw ValidationException::withMessages([
          'department_id' => __('No tiene acceso a este departamento con el rol seleccionado.'),
        ]);
      }

      if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password
      ], $request->remember)) {

        $departmentIds = $this->determineDepartment($user, $role, $request->department_id);

        // Establecer datos de sesión
        session([
          'current_role_id' => $role->id,
          'current_role_name' => $role->name,
          'current_department_ids' => $departmentIds, // Ahora es un array siempre
          'current_bank_ids' => $user->getContextBanks($role->id)
        ]);

        return $user;
      }

      throw ValidationException::withMessages([
        'email' => __('Credenciales incorrectas. Verifique su correo y contraseña.'),
      ]);
    });
    */
    Fortify::authenticateUsing(function (Request $request) {
      Log::info('Auth attempt for: ' . $request->email);

      // Validación básica
      $request->validate([
        'email' => 'required|email',
        'password' => 'required',
      ]);

      $user = User::where('email', $request->email)->first();

      if (!$user) {
        Log::warning('User not found');
        return null;
      }

      if (!$user->active) {
        Log::warning('User inactive');
        throw ValidationException::withMessages([
          'email' => __('Your account is inactive')
        ]);
      }

      // Verificación de contraseña sin triggerear el boot múltiple
      if (!Auth::guard('web')->validate([
        'email' => $request->email,
        'password' => $request->password
      ])) {
        Log::warning('Invalid credentials');
        return null;
      }

      // Resolución de rol mejorada
      $role = $this->resolveRole($user, $request->role_id);
      if (!$role) {
        Log::warning('No valid role selected');
        throw ValidationException::withMessages([
          'role_id' => __('Select a valid role')
        ]);
      }

      // Resolución de departamento
      $departmentId = $this->resolveDepartment($user, $role, $request->department_id);

      // Configurar sesión ANTES de autenticar
      session()->put([
        'current_role_id' => $role->id,
        'current_role_name' => $role->name,
        'current_department_id' => $departmentId,
        'current_bank_ids' => $user->getContextBanks($role->id)
      ]);

      Log::info('Auth successful for: ' . $user->email);
      return $user;
    });

    // Rate limiter mejorado
    RateLimiter::for('login', function (Request $request) {
      $throttleKey = Str::transliterate(Str::lower($request->email)) . '|' . $request->ip();
      Log::info("Login attempt from IP: " . $request->ip());
      return Limit::perMinute(5)->by($throttleKey);
    });

    /*
    RateLimiter::for('login', function (Request $request) {
      $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username()))) . '|' . $request->ip();
      Log::info("Se logueo en login de Forty");
      return Limit::perMinute(5)->by($throttleKey);
    });
    */

    RateLimiter::for('two-factor', function (Request $request) {
      return Limit::perMinute(5)->by($request->session()->get('login.id'));
    });
  }

  private function resolveRole($user, $requestedRoleId = null)
  {
    // 1. Verificar si es SuperAdmin/Administrador
    $specialRole = $user->roles->first(function ($role) {
      return in_array($role->name, User::ROLES_ALL_DEPARTMENTS);
    });

    if ($specialRole) {
      Log::info('Detected special role: ' . $specialRole->name);
      return $specialRole;
    }

    // 2. Usuario con un solo rol
    if ($user->roles->count() === 1) {
      return $user->roles->first();
    }

    // 3. Validar rol seleccionado
    if (!$requestedRoleId) {
      return null;
    }

    return $user->roles->firstWhere('id', $requestedRoleId);
  }

  private function resolveDepartment($user, $role, $requestedDeptId)
  {
    // Roles especiales no requieren departamento
    if (in_array($role->name, User::ROLES_ALL_DEPARTMENTS)) {
      return 0;
    }

    $validDepartments = $user->roleAssignments()
      ->where('role_id', $role->id)
      ->pluck('department_id')
      ->unique();

    if ($validDepartments->isEmpty()) {
      throw ValidationException::withMessages([
        'department_id' => __('No departments assigned for this role')
      ]);
    }

    if ($validDepartments->count() === 1) {
      return $validDepartments->first();
    }

    if (!$validDepartments->contains($requestedDeptId)) {
      throw ValidationException::withMessages([
        'department_id' => __('Select a valid department for this role')
      ]);
    }

    return $requestedDeptId;
  }
}
