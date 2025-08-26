<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
  protected $fillable = [
    'code',
    'name',
    'active',
  ];

  protected $casts = [
    'active' => 'boolean',
  ];

  public function departments()
  {
    return $this->belongsToMany(Department::class, 'garantias_has_departments', 'garantia_id', 'department_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'code',
      'name',
      'active',
    ];

    $query->select($columns);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_code'])) {
      $query->where('code', 'like', '%' . $filters['filter_code'] . '%');
    }

    if (!empty($filters['filter_name'])) {
      $query->where('name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_department'])) {
      $query->whereHas('departments', function ($q) use ($filters) {
        $q->where('departments.name', 'like', '%' . $filters['filter_department'] . '%');
      });
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
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit"></i>
                <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit"></i>
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

  public function getHtmlcolumnDepartment()
  {
    $htmlColumn = '';
    if ($this->departments->isNotEmpty())
      $htmlColumn = $this->departments->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
  }
}
