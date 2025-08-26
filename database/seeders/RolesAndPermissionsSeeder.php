<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
  public function run()
  {
    // Definir permisos organizados por módulos
    $permissionsByModule = [
      'users'        => ['view', 'create', 'edit', 'delete', 'export', 'assign-roles', 'reset-passwords'],
      'roles'        => ['view', 'create', 'edit', 'delete', 'export', 'manage-permissions'],
      'clients'      => ['view', 'create', 'edit', 'delete', 'export'],
      'services'     => ['view', 'create', 'edit', 'delete', 'export'],
      'proformas'    => ['view', 'create', 'edit', 'delete', 'export', 'send', 'download'],
      'invoices'     => ['view', 'create', 'edit', 'delete', 'export', 'send', 'download'],
      'classifiers'  => ['view', 'create', 'edit', 'delete', 'export'],
      'settings'     => ['view', 'update']
    ];

    // Crear permisos en la base de datos
    foreach ($permissionsByModule as $module => $actions) {
      foreach ($actions as $action) {
        Permission::firstOrCreate(['name' => "{$action}-{$module}"]);
      }
    }

    // Definir roles y asignar permisos
    $rolesWithPermissions = [
      'SuperAdmin'    => Permission::all()->pluck('name')->toArray(),
      'Administrador' => [
        // Users
        'view-users',
        'create-users',
        'edit-users',
        'delete-users',
        'export-users',
        'assign-roles-users',
        'reset-passwords-users',
        //roles
        'view-roles',
        'create-roles',
        'edit-roles',
        'delete-roles',
        'export-roles',
        'manage-permissions-roles',
        //clients
        'view-clients',
        'create-clients',
        'edit-clients',
        'delete-clients',
        'export-clients',
        //services
        'view-services',
        'create-services',
        'edit-services',
        'delete-services',
        'export-services',
        //proformas
        'view-proformas',
        'create-proformas',
        'edit-proformas',
        'delete-proformas',
        'export-proformas',
        'send-proformas',
        'download-proformas',
        //invoices
        'view-invoices',
        'create-invoices',
        'edit-invoices',
        'delete-invoices',
        'export-invoices',
        'send-invoices',
        'download-invoices',
        //classifiers
        'view-classifiers',
        'create-classifiers',
        'edit-classifiers',
        'delete-classifiers',
        'export-classifiers',
        //settings
        'view-settings',
        'edit-settings'
      ],
      'Abogado' => []
    ];

    // Crear roles y asignar permisos
    foreach ($rolesWithPermissions as $roleName => $permissions) {
      $role = Role::firstOrCreate(['name' => $roleName]);
      $role->syncPermissions($permissions);
    }
  }
}
