<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoCuenta extends Model
{
  protected $table = 'catalogo_cuentas';

  protected $fillable = [
    'codigo',
    'descrip',
    'favorite',
  ];

  protected $casts = [
    'favorite' => 'boolean',
  ];

  public function getHtmlColumnFavorite()
  {
    if ($this->favorite)
      $output = "<i class=\"bx fs-4 bx-check-shield text-success\" title=\"Activo\"></i>";
    else
      $output = "<i class=\"bx fs-4 bx-shield-x text-danger\" title=\"Inactivo\"></i>";
    return $output;
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'catalogo_cuentas.id',
      'codigo',
      'descrip',
      'favorite'
    ];

    $query->select($columns);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_codigo'])) {
      $query->where('codigo', 'like', '%' . $filters['filter_codigo'] . '%');
    }

    if (!empty($filters['filter_descrip'])) {
      $query->where('descrip', 'like', '%' . $filters['filter_descrip'] . '%');
    }

    if (isset($filters['filter_favorite']) && !is_null($filters['filter_favorite'])  && $filters['filter_favorite'] !== '') {
      $query->where('favorite', '=', $filters['filter_favorite']);
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
