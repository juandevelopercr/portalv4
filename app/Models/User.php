<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  use HasApiTokens;

  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory;
  use HasProfilePhoto;
  use Notifiable;
  use TwoFactorAuthenticatable;
  use HasRoles;
  use LogsActivity;
  use CausesActivity;

  protected $connection = 'mysql'; // fuerza BD maestra

  public const ROLES_ALL_DEPARTMENTS = [
    'SuperAdmin',
    'Administrador',
    'AdminContabilidad',
    'AdminFacturacion',
    'AdminPagos',
    'AdminCXC',
    'Socio',
  ];

  public const ROLES_ALL_CASOS = [
    'SuperAdmin',
    'Administrador',
    'Jefe',
    'JefeSenior',
    'Asistente',
    'AbogadoEditor',
    'AyudanteDeJefe'
  ];

  public const ROLES_REQUIRED_BANK = [
    'Banco'
  ];

  public const SUPERADMIN = 'SuperAdmin';
  public const ADMINISTRADOR = 'Administrador';
  public const JEFE_AREA = 'Jefe';
  public const JEFE_AREA_SENIOR = 'JefeSenior';
  public const ABOGADO = 'Abogado';
  public const ABOGADO_EDITOR = 'AbogadoEditor';
  public const ASISTENTE = 'Asistente';
  public const AYUDANTE_JEFE = 'AyudanteDeJefe';
  public const BANCO = 'Banco';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'initials',
    'password',
    'password_confirmation',
    'profile_photo_path',
    'tenant_id',
    'active'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array<int, string>
   */
  protected $appends = [
    'profile_photo_url',
  ];

  public function tenant()
  {
    return $this->belongsTo(Tenant::class);
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['*'])
      ->setDescriptionForEvent(fn(string $eventName) => "El usuario ha sido {$eventName}")
      ->useLogName('usuario')
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs();
    // Chain fluent methods for configuration options
  }

  public function getProfilePhotoUrlAttribute()
  {
    // Verifica si el valor de `profile_photo_path` existe y construye la URL completa
    return $this->profile_photo_path
      ? asset('storage/assets/img/avatars/' . $this->profile_photo_path)
      : asset('storage/assets/img/avatars/default.png');
  }

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  /**
   * Get the country that owns the User
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function country(): BelongsTo
  {
    return $this->belongsTo(Country::class);
  }

  /**
   * Get the state that owns the User
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function state(): BelongsTo
  {
    return $this->belongsTo(State::class);
  }

  /**
   * Get the city that owns the User
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function city(): BelongsTo
  {
    return $this->belongsTo(City::class);
  }

  /**
   * Relación con roles usando Spatie
   */
  public function roles()
  {
    return $this->belongsToMany(Role::class)
      ->select('roles.id', 'roles.name'); // Especificar tabla
  }

  public function hasAnyRole(array $roles)
  {
    return $this->roles()->whereIn('name', $roles)->exists();
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Selección de columnas básicas
    $query->select([
      'users.id',
      'users.name',
      'users.initials',
      'users.email',
      'users.profile_photo_path',
      'users.created_at',
      'users.active'
    ]);

    // Búsqueda principal (valor general)
    if (!empty($value)) {
      $query->where(function ($q) use ($value) {
        $q->where('users.name', 'like', "%{$value}%")
          ->orWhere('users.initials', 'like', "%{$value}%")
          ->orWhere('users.email', 'like', "%{$value}%")
          ->orWhereHas('roles', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
          });
      });
    }

    // Filtros específicos (sobreescriben la búsqueda general)
    if (!empty($filters['filter_name'])) {
      $query->where('users.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_departments'])) {
      $query->whereHas('departments', function ($q) use ($filters) {
        $q->where('departments.name', 'like', '%' . $filters['filter_departments'] . '%');
      });
    }

    if (!empty($filters['filter_email'])) {
      $query->where('users.email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_initials'])) {
      $query->where('users.initials', 'like', '%' . $filters['filter_initials'] . '%');
    }

    if (!empty($filters['filter_created_at'])) {
      $this->applyDateFilter($query, $filters['filter_created_at']);
    }

    if (isset($filters['filter_active']) && !is_null($filters['filter_active'])  && $filters['filter_active'] !== '') {
      $query->where('users.active', '=', $filters['filter_active']);
    }

    // NUEVO: Filtro por roles
    if (!empty($filters['filter_role'])) {
      $query->whereHas('roles', function ($q) use ($filters) {
        $q->where('name', 'like', '%' . $filters['filter_role'] . '%');
      });
    }

    // Agrupar por todas las columnas seleccionadas
    $query->groupBy([
      'users.id',
      'users.name',
      'users.initials',
      'users.email',
      'users.profile_photo_path',
      'users.created_at',
      'users.active'
    ]);

    return $query;
  }

  protected function applyDateFilter($query, $dateFilter)
  {
    $range = explode(' to ', $dateFilter);

    if (count($range) === 2) {
      try {
        $start = Carbon::createFromFormat('d-m-Y', $range[0])->startOfDay();
        $end = Carbon::createFromFormat('d-m-Y', $range[1])->endOfDay();
        $query->whereBetween('users.created_at', [$start, $end]);
      } catch (\Exception $e) {
        logger()->error('Error parsing date range filter', ['error' => $e->getMessage()]);
      }
    } else {
      try {
        $date = Carbon::createFromFormat('d-m-Y', $dateFilter);
        $query->whereDate('users.created_at', $date);
      } catch (\Exception $e) {
        logger()->error('Error parsing single date filter', ['error' => $e->getMessage()]);
      }
    }
  }

  public function getHtmlColumnName()
  {
    $user_img = $this->profile_photo_path;
    $name = $this->name;

    $roles = '';
    $listaroles = $this->getRoleNames();
    foreach ($listaroles as $r)
      $roles .= $r . "<br>";

    if ($user_img) {
      // Imagen de perfil del usuario
      $imageUrl = asset('storage/assets/img/avatars/' . $user_img);
    } else {
      // Avatar con iniciales
      $states = [' success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
      $stateNum = array_rand($states);
      $state = $states[$stateNum]; // Obtener iniciales
      preg_match_all('/\b\w/', $name, $matches);
      $initials = strtoupper(substr(implode('', $matches[0]), 0, 2)); // Toma las primeras dos letras
    }

    $output = "<div class=\"d-flex justify-content-start align-items-center user-name\">
                  <div class=\"avatar-wrapper\">
                    <div class=\"avatar me-2\">";
    if ($user_img)
      $output .= "<img src=\"" . $imageUrl . "\" alt=\"Avatar\" class=\"rounded-circle\">";
    else
      $output .= "<span class=\"avatar-initial rounded-circle bg-label-" . $state . "\">" . $initials . "</span>";
    $output .= "</div>
                  </div>
                  <div class=\"d-flex flex-column\">
                    <span class=\"emp_name text-truncate\">" . $this->name . "</span>
                    <small class=\"emp_post text-truncate text-muted\">" . $roles . "</small>
                  </div>
              </div>";

    return $output;
  }

  public function getHtmlColumnActive()
  {
    if ($this->active) {
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Activo"></i>';
    } else {
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="Inactivo"></i>';
    }
    return $output;
  }

  public function getHtmlcolumnDepartment()
  {
    if ($this->departments->isNotEmpty()) {
      return $this->departments->pluck('name')->join(', ');
    }
    return "<span class=\"text-gray-500\">-</span>";
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-users')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-primary"
                title="Editar"
                wire:click="edit({$this->id})"
                wire:loading.attr="disabled"
                wire:target="edit">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit"></i>
                <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit"></i>
            </button>
        HTML;
    }

    // Enviar credenciales
    $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2 text-success"
            title="Enviar credenciales de acceso"
            wire:click.prevent="confirmarAccion({$this->id}, 'credentialSend',
                '¿Está seguro que desea enviar las credenciales de acceso al sistema?',
                'Después de confirmar se generará una nueva contraseña y se enviará al correo del usuario',
                'Sí, proceder')">
            <i class="bx bx-envelope {$iconSize}"></i>
        </button>
    HTML;

    // Eliminar
    if ($user->can('delete-users')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Eliminar"
                wire:click.prevent="confirmarAccion({$this->id}, 'delete',
                    '¿Está seguro que desea eliminar este registro?',
                    'Después de confirmar, el registro será eliminado',
                    'Sí, proceder')">
                <i class="bx bx-trash {$iconSize}"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getHtmlcolumnRoles()
  {
    $html = '';
    foreach ($this->roles as $role) {
      $html .= '<span class="badge bg-primary">' . e($role->name) . '</span> ';
    }
    return $html;
  }
}
