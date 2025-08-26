<?php

namespace App\Models;

use App\Models\Department;
use App\Models\ProductHonorariosTimbre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
  use HasFactory;

  const TERCEROS   = 3;
  const LAFISE     = 4;
  const DAVIVIENDA = 5;
  const SANJOSE    = 6;

  protected $fillable = [
    'name',
    'iniciales',
    'email',
    'desglosar_servicio',
    'active',
  ];

  public function departments()
  {
    return $this->belongsToMany(Department::class, 'department_banks', 'bank_id', 'department_id');
  }

  public function honorariosTimbres()
  {
    return $this->hasMany(ProductHonorariosTimbre::class);
  }

  public function users()
  {
    return $this->belongsToMany(User::class, 'bank_user')->withTimestamps();
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'name',
      'iniciales',
      'email',
      'desglosar_servicio',
      'active',
    ];

    $query->select($columns)
      ->where(function ($q) use ($value) {
        $q->where('name', 'like', "%{$value}%")
          ->where('iniciales', 'like', "%{$value}%")
          ->where('email', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_iniciales'])) {
      $query->where('iniciales', 'like', '%' . $filters['filter_iniciales'] . '%');
    }

    if (!empty($filters['filter_email'])) {
      $query->where('email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_departments'])) {
      $query->whereHas('departments', function ($q) use ($filters) {
        $q->where('name', 'like', '%' . $filters['filter_departments'] . '%');
      });
    }

    if (isset($filters['filter_desglosar_servicio']) && !is_null($filters['filter_desglosar_servicio'])  && $filters['filter_desglosar_servicio'] !== '') {
      $query->where('desglosar_servicio', '=', $filters['filter_desglosar_servicio']);
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

  public function getHtmlColumnDesglose()
  {
    if ($this->desglosar_servicio)
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    else
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    return $output;
  }

  public function getHtmlcolumnDepartment()
  {
    $htmlColumn = '';
    if ($this->departments->isNotEmpty())
      $htmlColumn = $this->departments->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">Sin departamentos</span>";
    return $htmlColumn;
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
}
