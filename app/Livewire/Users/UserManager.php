<?php

namespace App\Livewire\Users;

use App\Exports\UsersExport;
use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\DataTableConfig;
use App\Models\Tenant;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(as: 'userActive', history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'users.name';

  #[Url(as: 'userSort', history: true)]
  public $sortDir = 'ASC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $name;
  public $email;
  public $initials;
  public $password;
  public $password_confirmation;
  public $profile_photo_path;
  public $tenant_id;

  public $oldProfile_photo_path = NULL; // Imagen existente en la BD

  public $closeForm = false;

  public $columns;
  public $defaultColumns;
  public $listActives;
  public $tenants = [];

  #[Computed()]
  public function listroles()
  {
    if (Auth::user()->hasRole(User::SUPERADMIN)) {
      return Role::orderBy('id', 'DESC')->get();
    } else {
      return Role::where('name', '<>', User::SUPERADMIN)->orderBy('id', 'DESC')->get();
    }
  }

  //#[Url()]
  public $roles = [];

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  protected function getModelClass(): string
  {
    return User::class;
  }

  public function mount()
  {
    $this->refresDatatable();
    $this->listActives = [['id' => 1, 'name' => 'Activo'], ['id' => 0, 'name' => 'Inactivo']];
    if (auth()->user()->hasRole('SUPERADMIN'))
      $this->tenants = Tenant::get();
  }

  public function render()
  {
    $users = User::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->when($this->active !== '', function ($query) {
        $query->where('users.active', $this->active);
      })
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    $userCount = User::get()->count();
    $userActive = User::where('active', 1)->get()->count();
    $notActive = User::where('active', 0)->get()->count();
    $usersUnique = $users->unique(['email']);
    $userDuplicates = $users->diff($usersUnique)->count();

    $percentActive = $userCount > 0 ? $userActive / $userCount * 100 : 0;
    $percentActive = Helpers::formatDecimal($percentActive);

    $percentNoActive = $userCount > 0 ? $notActive / $userCount * 100 : 0;
    $percentNoActive = Helpers::formatDecimal($percentNoActive);

    $percentDuplicate = $userCount > 0 ? $userDuplicates / $userCount * 100 : 0;
    $percentDuplicate = Helpers::formatDecimal($percentDuplicate);

    return view('livewire.user-manager.user-manager', [
      'users' => $users,
      'totalUser' => $userCount,
      'userActive' => $userActive,
      'notActive' => $notActive,
      'userDuplicates' => $userDuplicates,
      'percentActive' => $percentActive,
      'percentNoActive' => $percentNoActive,
      'percentDuplicate' => $percentDuplicate
    ]);
  }

  public function updatedActive($value)
  {
    $this->active = (int) $value;
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->active = 1;
    $this->action = 'create';
    $this->dispatch('scroll-to-top');
    $this->dispatch('reinitFormControls');
  }

  public function rules()
  {
    $rules = [
      'name'          => 'required|string|max:255',
      'email'         => 'required|string|email|max:255|unique:users,email,' . $this->recordId,
      'initials'      => 'required|string|max:30',
      'password'      => 'nullable|string|min:8|confirmed',
      'roles'         => 'required|array|min:1',
      'roles.*'       => 'exists:roles,name',
      'tenant_id'     => 'nullable|integer',
      'active'        => 'required|integer|in:0,1',
    ];

    if (empty($this->recordId)) {
      $rules['password'] = 'required|string|min:8|confirmed';
    }

    return $rules;
  }

  public function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'email.unique' => 'El :attribute ya está registrado.',
      'password.confirmed' => 'Las contraseñas no coinciden.',
      'password.min' => 'La clave debe tener cómo mínimo 8 caracteres'
    ];
  }

  public function validationAttributes()
  {
    return [
      'name' => 'nombre',
      'email' => 'correo electrónico',
      'initials' => 'iniciales',
      'password' => 'contraseña',
      'password_confirmation' => 'confirmación de contraseña',
      'roles' => 'roles',
      'active' => 'estado',
    ];
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate();

    // Validar la imagen solo si existe una nueva imagen
    if ($this->profile_photo_path) {
      $this->validate([
        'profile_photo_path' => 'image|mimes:jpg,jpeg,png,gif|max:2048',
      ]);
    }
    try {
      if ($this->profile_photo_path) {
        $imageName = uniqid() . '.' . $this->profile_photo_path->extension();
        $this->profile_photo_path->storeAs('assets/img/avatars', $imageName, 'public');
        $validatedData['profile_photo_path'] = $imageName;
      }

      $password = $validatedData['password'];

      // Crear el usuario con la contraseña encriptada
      $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'initials' => $validatedData['initials'],
        'password' => Hash::make($validatedData['password']),
        'active' => $validatedData['active'],
        'profile_photo_path' => $validatedData['profile_photo_path'] ?? null,
      ]);

      $closeForm = $this->closeForm;

      if ($user) {
        $user->syncRoles($validatedData['roles']);
      }

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($user->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);

      // Enviar email al usuario con las credenciales
      $this->afterCreateUser($user->name, $user->email, $password);
    } catch (\Exception $e) {
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $user = User::find($recordId);
    $this->recordId = $recordId;

    $this->name = $user->name;
    $this->email = $user->email;
    $this->initials = $user->initials;
    $this->profile_photo_path = $user->profile_photo_path;
    $this->tenant_id = $user->tenant_id;
    $this->active = $user->active;

    $this->roles = $user->getRoleNames();

    // Cargar la imagen actual guardada en la base de datos
    $this->oldProfile_photo_path = $user->profile_photo_path;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
    $this->dispatch('reinitFormControls');
  }

  public function update()
  {
    $recordId = $this->recordId;

    //dd($this);
    // Valida los datos
    $validatedData = $this->validate();

    // Validar la imagen solo si existe una nueva imagen
    if ($this->profile_photo_path instanceof \Illuminate\Http\UploadedFile) {
      $this->validate([
        'profile_photo_path' => 'image|mimes:jpg,jpeg,png,gif|max:2048',
      ]);
    }
    try {
      // Encuentra el usuario existente
      $user = User::findOrFail($recordId);

      // Procesa la nueva imagen si se subió
      if ($this->profile_photo_path instanceof \Illuminate\Http\UploadedFile) {
        // Crear la carpeta si no existe
        $directory = 'assets/img/avatars';
        if (!Storage::disk('public')->exists($directory)) {
          Storage::disk('public')->makeDirectory($directory);
        }

        // Eliminar la imagen anterior si existe
        if ($this->oldProfile_photo_path) {
          Storage::disk('public')->delete($directory . '/' . $this->oldProfile_photo_path);
        }

        // Guardar la nueva imagen
        $imageName = uniqid() . '.' . $this->profile_photo_path->extension();
        $this->profile_photo_path->storeAs($directory, $imageName, 'public');
        $validatedData['profile_photo_path'] = $imageName;
      } else {
        // Mantener la imagen anterior
        $validatedData['profile_photo_path'] = $this->oldProfile_photo_path;
      }

      // Actualiza el usuario
      $user->update([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'initials' => $validatedData['initials'],
        'password' => $validatedData['password'] ? Hash::make($validatedData['password']) : $user->password,
        'active' => $validatedData['active'],
        'profile_photo_path' => $validatedData['profile_photo_path']
      ]);

      $closeForm = $this->closeForm;

      $roleIds = array_map(function ($role) {
        return is_array($role) ? $role['id'] : $role;
      }, $validatedData['roles'] ?? []);

      $user->syncRoles($roleIds);

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();
      $this->dispatch('scroll-to-top');
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);

      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($user->id);
      }
    } catch (\Exception $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . $e->getMessage()]);
    }
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    // static::getName() devuelve automáticamente el nombre del componente Livewire actual, útil para dispatchTo.
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforedelete()
  {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $user = User::findOrFail($recordId);

      if ($user->id == 1) {
        $this->dispatch('show-notification', ['type' => 'error', 'message' => __('The user superadmin cannot be deleted')]);
      } else {
        // Verifica si el usuario tiene una foto de perfil y si el archivo realmente existe en el disco
        if ($user->profile_photo_path && Storage::disk('public')->exists('assets/img/avatars/' . $user->profile_photo_path)) {
          // Elimina la imagen solo si existe
          Storage::disk('public')->delete('assets/img/avatars/' . $user->profile_photo_path);
        }

        if ($user->delete()) {

          $this->selectedIds = array_filter(
            $this->selectedIds,
            fn($selectedId) => $selectedId != $recordId
          );

          // Opcional: limpiar "seleccionar todo" si ya no aplica
          if (empty($this->selectedIds)) {
            $this->selectAll = false;
          }

          // Emitir actualización
          $this->dispatch('updateSelectedIds', $this->selectedIds);

          // Puedes emitir un evento para redibujar el datatable o actualizar la lista
          $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
        }
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }

  public function resetPhoto()
  {
    $this->profile_photo_path = null; // Limpia la propiedad `photo`
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
  }

  public function resetControls()
  {
    $this->reset(
      'name',
      'email',
      'initials',
      'password',
      'password_confirmation',
      'active',
      'roles',
      'profile_photo_path',
      'closeForm',
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
    $this->oldProfile_photo_path = '';
  }

  public function setSortBy($sortByField)
  {
    if ($this->sortBy === $sortByField) {
      $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
      return;
    }

    $this->sortBy = $sortByField;
    $this->sortDir = 'DESC';
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'user-datatable')
      ->first();

    if ($config) {
      // Verifica si ya es un array o si necesita decodificarse
      $columns = is_array($config->columns) ? $config->columns : json_decode($config->columns, true);
      $this->columns = array_values($columns); // Asegura que los índices se mantengan correctamente
      $this->perPage = $config->perPage  ?? 10; // Valor por defecto si viene null
    } else {
      $this->columns = $this->getDefaultColumns();
      $this->perPage = 10;
    }
  }

  public $filters = [
    'filter_name' => NULL,
    'filter_email' => NULL,
    'filter_role' => NULL,
    'filter_initials' => NULL,
    'filter_created_at' => NULL,
    'filter_active' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'name',
        'orderName' => 'users.name',
        'label' => __('Name'),
        'filter' => 'filter_name',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnName',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'email',
        'orderName' => 'email',
        'label' => __('Email'),
        'filter' => 'filter_email',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'roles',
        'orderName' => '',
        'label' => __('Roles'),
        'filter' => 'filter_role',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlcolumnRoles',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'initials',
        'orderName' => 'initials',
        'label' => __('Initials'),
        'filter' => 'filter_initials',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'created_at',
        'orderName' => 'users.created_at',
        'label' => __('Created at'),
        'filter' => 'filter_created_at',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'active',
        'orderName' => 'users.active',
        'label' => __('Active'),
        'filter' => 'filter_active',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'action',
        'orderName' => '',
        'label' => __('Actions'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'action',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ]
    ];

    return $this->defaultColumns;
  }

  public function storeAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de almacenamiento
    $this->store();
  }

  public function updateAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function resetFilters()
  {
    $this->reset('filters');
    //$this->filters['filter_active'] = 1;
    $this->selectedIds = [];
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  private function afterCreateUser($name, $email, $password)
  {
    $sent = Helpers::sendUserCredentialEmail($name, $email, $password);

    if ($sent) {
      $menssage = __('An email has been sent to the following address:') . ' ' . $email;

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __("The user's credentials have been sent successfully") . '. ' . $menssage
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }

  #[On('credentialSend')]
  public function credentialSend($recordId)
  {
    // 1. Obtener el usuario
    $user = User::find($recordId);

    if (!$user) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('User not found')
      ]);
      return;
    }

    // 2. Generar nueva contraseña segura
    $newPassword = Str::random(10); // Ej: aB8zXy9LmN

    // 3. Guardarla hasheada en BD
    $user->password = Hash::make($newPassword);
    $user->save();

    // 4. Enviar email con la clave en texto plano
    $sent = Helpers::sendUserCredentialEmail($user->name, $user->email, $newPassword);

    // 5. Notificación según éxito
    if ($sent) {
      $message = __('An email has been sent to the following address:') . ' ' . $user->email;

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __("The user's credentials have been sent successfully") . '. ' . $message
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }
}
