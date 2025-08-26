<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProviderAndSeller extends Model
{
  protected $table = 'providers_sellers';

  protected $fillable = [
    'fecha_venta',
    'service_provider_id',
    'seller_id',
    'fecha_servicio',
    'company_provider_id',
    'num_pax',
    'cliente',
    'precio_rank',
    'precio_neto',
    'num_recibo',
    'pick_up_time',
    'pick_up_place',
    'comment',
    'dop_off',
  ];

  protected $casts = [
    'fecha_venta' => 'date',
    'fecha_servicio' => 'date',
  ];

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'providers_sellers.id',
      'fecha_venta',
      'service_provider_id',
      'seller_id',
      'sellers.name as seller_name',
      'fecha_servicio',
      'company_provider_id',
      'providers_companies.name as company_name',
      'num_pax',
      'cliente',
      'precio_rank',
      'precio_neto',
      'num_recibo',
      'pick_up_time',
      'pick_up_place',
      'comment',
      'dop_off',
    ];

    $query->select($columns)
      ->join('sellers', 'seller_id', '=', 'sellers.id')
      ->join('providers_companies', 'company_provider_id', '=', 'providers_companies.id')
      ->join('services_providers', 'service_provider_id', '=', 'services_providers.id');

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_seller'])) {
      $query->where('sellers.name', '=', $filters['filter_seller']);
    }

    if (!empty($filters['filter_fecha_venta'])) {
      $range = explode(' to ', $filters['filter_fecha_venta']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_venta', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_venta'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_venta', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_seller'])) {
      $query->where('sellers.name', '=', $filters['filter_seller']);
    }

    if (!empty($filters['filter_company'])) {
      $query->where('providers_companies.name', 'like', '%' . $filters['filter_company'] . '%');
    }

    if (!empty($filters['filter_pick_up_place'])) {
      $query->where('pick_up_place', 'like', '%' . $filters['filter_pick_up_place'] . '%');
    }

    if (!empty($filters['filter_pick_up_time'])) {
      $query->where('pick_up_time', 'like', '%' . $filters['filter_pick_up_time'] . '%');
    }

    if (!empty($filters['filter_num_pax'])) {
      $query->where('num_pax', '=',  $filters['filter_num_pax']);
    }

    if (!empty($filters['filter_cliente'])) {
      $query->where('cliente', 'like', '%' . $filters['filter_cliente'] . '%');
    }

    if (!empty($filters['filter_fecha_servicio'])) {
      $range = explode(' to ', $filters['filter_fecha_servicio']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_servicio', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_servicio'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_servicio', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_num_recibo'])) {
      $query->where('num_recibo', 'like', '%' . $filters['filter_num_recibo'] . '%');
    }

    if (!empty($filters['filter_precio_rank'])) {
      $query->where('precio_rank', 'like', '%' . $filters['filter_precio_rank'] . '%');
    }

    if (!empty($filters['filter_precio_neto'])) {
      $query->where('precio_neto', 'like', '%' . $filters['filter_precio_neto'] . '%');
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
