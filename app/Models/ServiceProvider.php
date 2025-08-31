<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends TenantModel
{
  protected $table = 'services_providers';

  protected $fillable = [
    'name',
    'active'
  ];

  protected $casts = [
    'active' => 'boolean'
  ];

  // Opcional: si no quieres usar timestamps
  //public $timestamps = false;

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'name',
      'active',
    ];

    $query->select($columns)
      ->where(function ($q) use ($value) {
        $q->where('name', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (isset($filters['filter_active']) && !is_null($filters['filter_active'])  && $filters['filter_active'] !== '') {
      $query->where('active', '=', $filters['filter_active']);
    }

    return $query;
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

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-classifiers')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-primary"
                title="Editar"
                wire:click="edit({$this->id})"
                wire:loading.attr="disabled"
                wire:target="edit">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit({$this->id})"></i>
                <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit({$this->id})"></i>
            </button>
        HTML;
    }

    // Eliminar
    if ($user->can('delete-classifiers')) {
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
}
