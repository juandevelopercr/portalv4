<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timbre extends Model
{
  use HasFactory;

  protected $fillable = [
    'base',
    'porcada',
    'tipo',
    'orden',
  ];

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'base',
      'porcada',
      'tipo',
      'orden'
    ];

    $query->select($columns)
      ->where(function ($q) use ($value) {
        $q->where('base', 'like', "%{$value}%")
          ->where('porcada', 'like', "%{$value}%")
          ->where('tipo', 'like', "%{$value}%")
          ->where('orden', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_base'])) {
      $query->where('base', 'like', '%' . $filters['filter_base'] . '%');
    }

    if (!empty($filters['filter_porcada'])) {
      $query->where('porcada', 'like', '%' . $filters['filter_porcada'] . '%');
    }

    if (!empty($filters['filter_tipo'])) {
      $query->where('tipo', '=', $filters['filter_tipo']);
      //dd($filters['filter_tipo']);
    }

    if (!empty($filters['filter_orden'])) {
      $query->where('orden', 'like', '%' . $filters['filter_orden'] . '%');
    }

    return $query;
  }

  public function getHtmlColumnType()
  {
    if ($this->tipo == 1)
      $html = "Timbre Abogados Bienes Inmuebles";
    else
      $html = "Timbre Abogados Bienes Muebles";
    return $html;
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
