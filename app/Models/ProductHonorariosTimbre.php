<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductHonorariosTimbre extends Model
{
  use HasFactory;

  protected $table = 'product_honorarios_timbres'; // Nombre de la tabla

  protected $fillable = [
    'product_id',
    'description',
    'base',
    'porcada',
    'honorario_id',
    'tabla_abogado_inscripciones',
    'tabla_abogado_traspasos',
    'fincascada',
    'escalonado',
    'fijo',
    'tipo',
    'descuento_timbre',
    'otro_cheque',
    'redondear',
    'ajustar_honorario',
    'porciento',
    'monto_manual',
    'es_impuesto',
  ];

  // Relación con el modelo Product
  public function product()
  {
    return $this->belongsTo(Product::class);
  }

  // Relación con el modelo Bank
  public function bank()
  {
    return $this->belongsTo(Bank::class);
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'product_honorarios_timbres.id',
      'product_honorarios_timbres.description',
      'base',
      'porcada',
      'honorario_id',
      'honorarios.name as honorario_name',
      'tabla_abogado_inscripciones',
      'tabla_abogado_traspasos',
      'fincascada',
      'escalonado',
      'fijo',
      'tipo',
      'descuento_timbre',
      'otro_cheque',
      'redondear',
      'ajustar_honorario',
      'porciento',
      'monto_manual',
      'es_impuesto',
    ];

    $query->select($columns)
      ->leftJoin('honorarios', 'product_honorarios_timbres.honorario_id', '=', 'honorarios.id')
      ->leftJoin('products', 'product_honorarios_timbres.product_id', '=', 'products.id')
      ->where(function ($q) use ($value) {
        $q->where('honorarios.name', 'like', "%{$value}%")
          ->orWhere('base', 'like', "%{$value}%")
          ->orWhere('porcada', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_description'])) {
      $query->where('product_honorarios_timbres.description', 'like', '%' . $filters['filter_description'] . '%');
    }

    if (!empty($filters['filter_base'])) {
      $query->where('base', 'like', '%' . $filters['filter_base'] . '%');
    }

    if (!empty($filters['filter_porcada'])) {
      $query->where('porcada', 'like', '%' . $filters['filter_porcada'] . '%');
    }

    if (isset($filters['filter_inscripciones']) && !is_null($filters['filter_inscripciones'])  && $filters['filter_inscripciones'] !== '') {
      $query->where('tabla_abogado_inscripciones', '=', $filters['filter_inscripciones']);
    }

    if (isset($filters['filter_traspasos']) && !is_null($filters['filter_traspasos'])  && $filters['filter_traspasos'] !== '') {
      $query->where('tabla_abogado_traspasos', '=', $filters['filter_traspasos']);
    }

    if (isset($filters['filter_honorarios']) && !is_null($filters['filter_honorarios'])  && $filters['filter_honorarios'] !== '') {
      $query->whereNotNull('honorario_id');
    }

    if (isset($filters['filter_fijo']) && !is_null($filters['filter_fijo'])  && $filters['filter_fijo'] !== '') {
      $query->where('fijo', '=', $filters['filter_fijo']);
    }

    if (isset($filters['filter_porciento']) && !is_null($filters['filter_porciento'])  && $filters['filter_porciento'] !== '') {
      $query->where('porciento', '=', $filters['filter_porciento']);
    }

    if (isset($filters['filter_timbre']) && !is_null($filters['filter_timbre'])  && $filters['filter_timbre'] !== '') {
      $query->where('descuento_timbre', '=', $filters['filter_timbre']);
    }

    return $query;
  }

  public function getHtmlColumnActive($param)
  {
    if ($this->$param) {
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
    if ($user->can('edit-services')) {
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
    if ($user->can('delete-services')) {
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
