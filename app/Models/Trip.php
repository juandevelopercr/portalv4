<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Añade esta línea

class Trip extends Model
{
  use SoftDeletes; // Añade este trait

  protected $table = 'trips';

  // Añade la propiedad dates para deleted_at
  protected $dates = ['deleted_at', 'date_service'];

  protected $fillable = [
    'contact_id',
    'contact_name',
    'customer_name',
    'consecutive',
    'town_id',
    'type',
    'pick_up',
    'destination',
    'bill_number',
    'pax',
    'rack_price',
    'net_cost',
    'date_service',
    'others',
    'vehicle_id',
    'status'
  ];

  protected $casts = [
    'date_service' => 'date',
    'rack_price' => 'decimal:2',
    'net_cost' => 'decimal:2',
  ];

  public function customer()
  {
    return $this->belongsTo(Contact::class, 'contact_id');
  }

  public function town()
  {
    return $this->belongsTo(Town::class);
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'trips.id',
      'contact_id',
      'contacts.name as company_name',
      'customer_name',
      'consecutive',
      'town_id',
      'towns.name as town_name',
      'trips.type',
      'pick_up',
      'destination',
      'bill_number',
      'pax',
      'rack_price',
      'net_cost',
      'date_service',
      'others',
      'status',
      'trips.deleted_at' // ¡AÑADE ESTA LÍNEA!
    ];

    $query->select($columns)
      ->join('contacts', 'trips.contact_id', '=', 'contacts.id')
      ->join('towns', 'trips.town_id', '=', 'towns.id');

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_type'])) {
      $query->where('trips.type', '=', $filters['filter_type']);
    }

    if (!empty($filters['filter_status'])) {
      $query->where('status', '=', $filters['filter_status']);
    }

    if (!empty($filters['filter_company_name'])) {
      $query->where('contacts.name', 'like', '%' . $filters['filter_company_name'] . '%');
    }

    if (!empty($filters['filter_date_service'])) {
      $range = explode(' to ', $filters['filter_date_service']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('date_service', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_date_service'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('date_service', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_town'])) {
      $query->where('town_id', '=', $filters['filter_town']);
    }

    if (!empty($filters['filter_pick_up'])) {
      $query->where('pick_up', 'like', '%' . $filters['filter_pick_up'] . '%');
    }

    if (!empty($filters['filter_destination'])) {
      $query->where('destination', 'like', '%' . $filters['filter_destination'] . '%');
    }

    if (!empty($filters['filter_bill_number'])) {
      $query->where('bill_number', 'like', '%' . $filters['filter_bill_number'] . '%');
    }

    if (!empty($filters['filter_pax'])) {
      $query->where('bill_number', '=', $filters['filter_pax']);
    }

    if (!empty($filters['filter_customer_name'])) {
      $query->where('customer_name', 'like', '%' . $filters['filter_customer_name'] . '%');
    }

    if (!empty($filters['filter_rack_price'])) {
      $query->where('rack_price', 'like', '%' . $filters['filter_rack_price'] . '%');
    }

    if (!empty($filters['filter_net_cost'])) {
      $query->where('net_cost', 'like', '%' . $filters['filter_net_cost'] . '%');
    }

    if (!empty($filters['filter_others'])) {
      $query->where('others', 'like', '%' . $filters['filter_others'] . '%');
    }

    if (!empty($filters['filter_consecutive'])) {
      $query->where('consecutive', 'like', '%' . $filters['filter_consecutive'] . '%');
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

    // Verificación directa usando deleted_at
    $isDeleted = !is_null($this->deleted_at);

    if ($isDeleted) {
      // Botón Restaurar
      if ($user->can('restore-trips')) {
        $html .= <<<HTML
                <button type="button"
                    class="btn p-0 me-2 text-success"
                    title="Restaurar"
                    wire:click.prevent="confirmarAccion({$this->id}, 'restore',
                        '¿Restaurar este registro?',
                        'El registro será recuperado y disponible nuevamente',
                        'Sí, restaurar')">
                    <i class="bx bx-undo {$iconSize}"></i>
                </button>
            HTML;
      }

      // Botón Eliminar Permanentemente
      if ($user->can('force-delete-trips')) {
        $html .= <<<HTML
                <button type="button"
                    class="btn p-0 me-2 text-danger"
                    title="Eliminar permanentemente"
                    wire:click.prevent="confirmarAccion({$this->id}, 'forceDelete',
                        '¿Eliminar permanentemente?',
                        'Esta acción no se puede deshacer. El registro será borrado definitivamente',
                        'Sí, eliminar')">
                    <i class="bx bx-trash {$iconSize}"></i>
                </button>
            HTML;
      }
    } else {
      // Botón Editar
      if ($user->can('edit-trips')) {
        $html .= <<<HTML
                <button type="button"
                    class="btn p-0 me-2 text-primary"
                    title="Editar"
                    wire:click="edit({$this->id})"
                    wire:loading.attr="disabled"
                    wire:target="edit({$this->id})">
                    <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit({$this->id})"></i>
                    <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit({$this->id})"></i>
                </button>
            HTML;
      }

      // Botón Eliminar (Soft Delete)
      if ($user->can('delete-trips')) {
        $html .= <<<HTML
                <button type="button"
                    class="btn p-0 me-2 text-danger"
                    title="Eliminar"
                    wire:click.prevent="confirmarAccion({$this->id}, 'delete',
                        '¿Eliminar este registro?',
                        'El registro se moverá a la papelera de reciclaje',
                        'Sí, eliminar')">
                    <i class="bx bx-trash {$iconSize}"></i>
                </button>
            HTML;
      }
    }

    $html .= '</div>';
    return $html;
  }

  public function getHtmlStatus()
  {
    switch ($this->status) {
      case 'INICIADO':
        $htmlData = "<span class=\"badge bg-secondary\">" . __('INICIADO') . "</span>";
        break;
      case 'FINALIZADO':
        $htmlData = "<span class=\"badge bg-success\">" . __('FINALIZADO') . "</span>";
        break;
      case 'ANULADA':
        $htmlData = "<span class=\"badge bg-danger\">" . __('ANULADA') . "</span>";
        break;
    }
    return $htmlData;
  }

  // AÑADE ESTE NUEVO MÉTODO PARA MOSTRAR ESTADO DE ELIMINACIÓN
  public function getHtmlDeleteStatus()
  {
    if ($this->trashed()) {
      return '<span class="badge bg-danger">ELIMINADO</span>';
    }
    return $this->getHtmlStatus();
  }
}
