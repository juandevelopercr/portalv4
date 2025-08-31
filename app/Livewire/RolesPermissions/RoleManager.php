<?php

namespace App\Livewire\RolesPermissions;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManager extends Component
{
  public $roles;
  public $permissions;
  public $rolePermissions = [];
  public $groupedPermissions = [];
  public $guardName = 'web';
  public $name;
  public $action = 'list';
  public $recordId = '';
  public $closeForm = false;

  public function mount()
  {
    $this->permissions = Permission::all(); // Cargar todos los permisos
    // Agrupar permisos por el último segmento del nombre
    // Agrupar permisos por el último segmento
    $this->groupedPermissions = $this->permissions->groupBy(function ($permission) {
      $parts = explode('-', $permission->name);
      return count($parts) > 1 ? $parts[count($parts) - 1] : $permission->name;
    })->map(function ($group) {
      return $group->values(); // Asegura que cada grupo es una colección válida
    });
    $this->fetchRoles();
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    //$this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    return [
      'name' => [
        'required',
        'string',
        $this->recordId ? "unique:roles,name,{$this->recordId}" : "unique:roles,name"
      ]
    ];
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'unique' => 'Ya existe un rol con ese nombre'
    ];
  }

  public function store()
  {
    // Validar primero
    $validatedData = $this->validate();

    try {
      $record = Role::create(['name' => $this->name, 'guard_name' => 'web']);

      // Sincronizar permisos
      $record->syncPermissions($this->rolePermissions);
      $this->name = '';
      $this->fetchRoles();

      $closeForm = $this->closeForm;

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    try {
      $record = Role::findOrFail($recordId);
      $this->name = $record->name;
      $this->recordId = $recordId;
      $this->rolePermissions = $record->permissions->pluck('name')->toArray(); // Cargar permisos asignados
    } catch (ModelNotFoundException $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. Record not found') . ' ' . $e->getMessage()]);
    }

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validar
    $validatedData = $this->validate();

    try {
      // Encuentra el registro existente
      $record = Role::findOrFail($recordId);

      // Actualizar
      $record->update($validatedData);

      // Sincronizar permisos
      $record->syncPermissions($this->rolePermissions);

      $closeForm = $this->closeForm;

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();

      //$this->dispatch('scroll-to-top');
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);

      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }

      $this->fetchRoles();
    } catch (\Exception $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }
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
      $record = Role::findOrFail($recordId);

      if ($record->delete()) {
        // Puedes emitir un evento para redibujar el datatable o actualizar la lista
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
        $this->fetchRoles();
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    //$this->dispatch('scroll-to-top');
  }

  public function resetControls()
  {
    $this->reset(['name', 'recordId', 'closeForm', 'rolePermissions']);
  }

  public function fetchRoles()
  {
    //$this->roles = Role::all();
    $this->roles = Role::where('name', '!=', 'SuperAdmin')
      ->get()
      ->map(function ($role) {
        return [
          'id' => $role->id,
          'name' => $role->name,
          'users' => User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role->name);
          })->limit(4)->get(), // 🔹 Agregar ->get() para ejecutar la consulta
          'users_count' => User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role->name);
          })->count()
        ];
      });
  }

  public function render()
  {
    return view('livewire.roles-permissions.role-manager');
  }
}
