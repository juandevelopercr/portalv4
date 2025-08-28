<?php

namespace App\Models;

use App\Models\TenantModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionCommission extends TenantModel
{
  use HasFactory;

  protected $table = 'transactions_commissions';

  protected $fillable = [
    'transaction_id',
    'centro_costo_id',
    'abogado_encargado',
    'comisionista_id',
    'percent',
    'commission_percent',
    'comision_pagada',
    'comision_pagada_date',
  ];

  protected $casts = [
    'comision_pagada' => 'boolean',
    'comision_pagada_date' => 'date',
  ];

  // Relaciones
  public function transaction()
  {
    return $this->belongsTo(Transaction::class);
  }

  public function centroCosto()
  {
    return $this->belongsTo(CentroCosto::class);
  }

  public function comisionista()
  {
    return $this->belongsTo(User::class, 'comisionista_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'transactions_commissions.id',
      'transaction_id',
      'centro_costo_id',
      'centro_costos.codigo',
      'centro_costos.descrip',
      'abogado_encargado',
      'comisionista_id',
      'users.name as comisionista',
      'percent',
      'commission_percent',
      'comision_pagada',
      'comision_pagada_date'
    ];

    $query->select($columns)
      ->join('centro_costos', 'centro_costo_id', '=', 'centro_costos.id')
      ->leftJoin('users', 'comisionista_id', '=', 'users.id')
      ->where(function ($q) use ($value) {
        $q->where('users.name', 'like', "%{$value}%")
          ->orWhere('centro_costos.descrip', 'like', "%{$value}%")
          ->orWhere('abogado_encargado', 'like', "%{$value}%")
          ->orWhere('percent', 'like', "%{$value}%")
          ->orWhere('commission_percent', 'like', "%{$value}%")
          ->orWhere('comision_pagada', 'like', "%{$value}%")
          ->orWhere('comision_pagada_date', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_descrip'])) {
      $query->where('centro_costos.descrip', 'like', '%' . $filters['filter_descrip'] . '%');
    }

    if (!empty($filters['filter_abogado_encargado'])) {
      $query->where('abogado_encargado', 'like', '%' . $filters['filter_abogado_encargado'] . '%');
    }

    if (!empty($filters['filter_percent'])) {
      $query->where('percent', 'like', '%' . $filters['filter_percent'] . '%');
    }

    if (!empty($filters['filter_comisionista'])) {
      $query->where('comisionista', 'like', '%' . $filters['filter_comisionista'] . '%');
    }

    if (!empty($filters['filter_commision_percent'])) {
      $query->where('commission_percent', 'like', '%' . $filters['filter_commision_percent'] . '%');
    }
    /*
    if (!empty($filters['filter_monto_distribucion'])) {
      $query->where('commission_percent', 'like', '%' . $filters['filter_monto_distribucion'] . '%');
    }
      */
    if (!empty($filters['filter_comision_pagada'])) {
      $query->where('comision_pagada', 'like', '%' . $filters['filter_comision_pagada'] . '%');
    }

    if (!empty($filters['filter_fechacomision_pagada'])) {
      $range = explode(' to ', $filters['filter_fechacomision_pagada']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('comision_pagada_date', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fechacomision_pagada'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('comision_pagada_date', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }
    /*
    if (!empty($filters['filter_monto_pagar'])) {
      $query->where('', 'like', '%' . $filters['filter_monto_pagar'] . '%');
    }
    */
    return $query;
  }

  public function calculateDistributionAmount()
  {
    return ($this->transaction->totalVentaNeta * $this->percent) / 100;
  }

  public function getHtmlcolumnName()
  {
    return $this->codigo . '-' . $this->descrip;
  }

  public function getHtmlColumnComisionPagada()
  {
    $html = '';
    if ($this->comision_pagada)
      $html = "<i class=\"bx fs-4 bx-check-shield text-success\" title=\"Pagada\"></i>";
    else
      $html = "<i class=\"bx fs-4 bx-shield-x text-danger\" title=\"No Pagada\"></i>";
    return $html;
  }

  public function calculateAmountToPay()
  {
    $distributorAmount = $this->calculateDistributionAmount();
    return ($distributorAmount * $this->commission_percent) / 100;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md'; // Tamaño uniforme, puedes usar bx-md si prefieres más grande
    $html = '<div class="d-flex align-items-center justify-content-start gap-2">';

    // Editar
    if ($user->can('edit-comision-proformas') && $this->transaction->proforma_status == Transaction::PROCESO) {
      $html .= <<<HTML
        <a href="#" class="text-primary" title="Editar"
           wire:click="edit({$this->id})"
           wire:loading.attr="disabled"
           wire:target="edit({$this->id})">
            <i class="bx bx-edit {$iconSize}"></i>
        </a>
        HTML;
    }

    // Eliminar
    if ($user->can('delete-comision-proformas') && $this->transaction->proforma_status == Transaction::PROCESO) {
      $html .= <<<HTML
        <a href="#" class="text-danger" title="Eliminar"
           wire:click.prevent="confirmarAccion({$this->id}, 'delete',
             '¿Está seguro que desea eliminar este registro?',
             'Después de confirmar, el registro será eliminado',
             'Sí, proceder')">
            <i class="bx bx-trash {$iconSize}"></i>
        </a>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }
}
