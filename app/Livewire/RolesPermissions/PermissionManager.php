<?php

namespace App\Livewire\RolesPermissions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionManager extends Component
{
  public $action;
  public $module;
  public $modules = [];
  public $permissions = [];
  public $editingPermissionId;
  public $editingPermissionName;
  public $showFormForModule = null; // Para controlar qué módulo está expandido

  public function mount()
  {
    $this->fetchPermissions();
  }

  // Obtener todos los permisos agrupados por módulo
  public function fetchPermissions()
  {
    $this->permissions = Permission::all()->groupBy(function ($permission) {
      $parts = explode('-', $permission->name);
      return count($parts) > 1 ? end($parts) : $permission->name;
    })->map(function ($group) {
      return $group->values();
    });

    // Obtener módulos únicos
    $this->modules = $this->permissions->keys()->toArray();
  }

  // Validación del formulario
  protected function rules()
  {
    return [
      'action' => 'required|string',
      'module' => 'required|string',
    ];
  }

  // Muestra el formulario de agregar permiso en el módulo seleccionado
  public function showAddPermissionForm($module)
  {
    $this->showFormForModule = $module;
    $this->module = $module;
    $this->action = '';
  }

  // Crear un nuevo permiso
  public function createPermission()
  {
    $this->validate();

    $permissionName = strtolower($this->action) . '-' . strtolower($this->module);

    // Evitar permisos duplicados
    if (Permission::where('name', $permissionName)->exists()) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Permission already exists!')
      ]);
      return;
    }

    Permission::create(['name' => $permissionName, 'guard_name' => 'web']);

    // Actualizar lista de permisos
    $this->fetchPermissions();
    $this->resetControls();

    $this->dispatch('show-notification', [
      'type' => 'success',
      'message' => __('Permission created successfully!')
    ]);
  }

  // Editar un permiso
  public function editPermission($permissionId)
  {
    $permission = Permission::findOrFail($permissionId);
    $this->editingPermissionId = $permissionId;
    $this->editingPermissionName = $permission->name;
  }

  public function updatePermission()
  {
    if (!$this->editingPermissionId) return;

    $permission = Permission::findOrFail($this->editingPermissionId);

    // Verificar si el nuevo nombre ya existe
    if (Permission::where('name', $this->editingPermissionName)->where('id', '!=', $this->editingPermissionId)->exists()) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Permission already exists!')
      ]);
      return;
    }

    $permission->update(['name' => $this->editingPermissionName]);
    $this->fetchPermissions();
    $this->reset(['editingPermissionId', 'editingPermissionName']);

    $this->dispatch('show-notification', [
      'type' => 'success',
      'message' => __('Permission updated successfully!')
    ]);
  }

  public function cancel()
  {
    $this->resetControls();
    //$this->dispatch('scroll-to-top');
  }

  public function resetControls()
  {
    $this->reset(['showFormForModule', 'module', 'action', 'module', 'editingPermissionId']);
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
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
      $permission = Permission::findOrFail($recordId);

      // Eliminar todas las relaciones antes de borrar el permiso
      DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();

      // 4️⃣ Limpiar la caché de permisos antes de eliminar
      app()[PermissionRegistrar::class]->forgetCachedPermissions();

      if ($permission->delete()) {
        // Emitir un evento de éxito si la eliminación es exitosa
        $this->dispatch('show-notification', [
          'type' => 'success',
          'message' => __('The record has been deleted')
        ]);

        $this->fetchPermissions();
      }
    } catch (QueryException $e) {
      // Capturar errores de integridad referencial (clave foránea)
      if ($e->getCode() == '23000') { // Código de error SQL para restricciones de integridad
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('The record cannot be deleted because it is related to other data.')
        ]);
      } else {
        // Otro tipo de error SQL
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage()
        ]);
      }
    } catch (\Exception $e) {
      // Capturar cualquier otro error general
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while deleting the record') . ' ' . $e->getMessage()
      ]);
    }
  }

  public function render()
  {
    return view('livewire.roles-permissions.permission-manager');
  }
}
