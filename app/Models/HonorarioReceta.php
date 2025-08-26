<?php

namespace App\Models;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HonorarioReceta extends Model
{
  use HasFactory;

  protected $table = 'honorarios_recetas';

  protected $fillable = [
    'honorario_id',
    'desde',
    'hasta',
    'porcentaje',
    'orden',
  ];

  public function honorario()
  {
    return $this->belongsTo(Honorario::class, 'honorario_id');
  }

  public function banks()
  {
    return $this->belongsToMany(Bank::class, 'honorarios_banks', 'honorario_id', 'bank_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'honorarios_recetas.id',
      'desde',
      'hasta',
      'porcentaje',
      'orden'
    ];

    $query->select($columns)
      ->join('honorarios', 'honorarios_recetas.honorario_id', '=', 'honorarios.id')
      ->where(function ($q) use ($value) {
        $q->where('desde', 'like', "%{$value}%")
          ->orWhere('hasta', 'like', "%{$value}%")
          ->orWhere('porcentaje', 'like', "%{$value}%")
          ->orWhere('orden', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('banks.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_desde'])) {
      $query->where('desde', 'like', '%' . $filters['filter_desde'] . '%');
    }

    if (!empty($filters['filter_hasta'])) {
      $query->where('hasta', 'like', '%' . $filters['filter_hasta'] . '%');
    }

    if (!empty($filters['filter_percent'])) {
      $query->where('porcentaje', 'like', '%' . $filters['filter_percent'] . '%');
    }

    if (!empty($filters['filter_orden'])) {
      $query->where('orden', 'like', '%' . $filters['filter_orden'] . '%');
    }

    return $query;
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
