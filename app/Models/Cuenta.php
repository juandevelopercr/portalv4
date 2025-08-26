<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
  protected $table = 'cuentas';

  protected $fillable = [
    'numero_cuenta',
    'nombre_cuenta',
    'moneda_id',
    'balance',
    'saldo',
    'ultimo_cheque',
    'mostrar_lugar',
    'lugar_fecha_y',
    'lugar_fecha_x',
    'beneficiario_y',
    'beneficiario_x',
    'monto_y',
    'monto_x',
    'monto_letras_y',
    'monto_letras_x',
    'detalles_y',
    'detalles_x',
    'is_cuenta_301',
    'calcular_pendiente_registro',
    'calcular_traslado_gastos',
    'calcular_traslado_honorarios',
    'banco_id',
    'perosna_sociedad',
    'traslados_karla',
    'certifondo_bnfa',
    'colchon',
    'tipo_cambio',
    'intruccionesPagoNacional',
    'intruccionesPagoInternacional'
  ];

  public function currency()
  {
    return $this->belongsTo(Currency::class, 'moneda_id');
  }

  public function banks()
  {
    return $this->belongsToMany(Bank::class, 'cuentas_has_bancos', 'cuenta_id', 'banco_id');
  }

  public function departments()
  {
    return $this->belongsToMany(Department::class, 'cuentas_has_departments', 'cuenta_id', 'department_id');
  }

  public function locations()
  {
    return $this->belongsToMany(BusinessLocation::class, 'cuentas_has_locations', 'cuenta_id', 'location_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'cuentas.id',
      'numero_cuenta',
      'nombre_cuenta',
      'moneda_id',
      'currencies.code as moneda',
      'balance',
      'saldo',
      'ultimo_cheque',
      'mostrar_lugar',
      'lugar_fecha_y',
      'lugar_fecha_x',
      'beneficiario_y',
      'beneficiario_x',
      'monto_y',
      'monto_x',
      'monto_letras_y',
      'monto_letras_x',
      'detalles_y',
      'detalles_x',
      'is_cuenta_301',
      'calcular_pendiente_registro',
      'calcular_traslado_gastos',
      'calcular_traslado_honorarios',
      'banco_id',
      'perosna_sociedad',
      'traslados_karla',
      'certifondo_bnfa',
      'colchon',
      'tipo_cambio',
      'intruccionesPagoNacional',
      'intruccionesPagoInternacional'
    ];

    $query->select($columns)
      ->join('currencies', 'cuentas.moneda_id', '=', 'currencies.id');

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_numero_cuenta'])) {
      $query->where('numero_cuenta', 'like', '%' . $filters['filter_numero_cuenta'] . '%');
    }

    if (!empty($filters['filter_nombre_cuenta'])) {
      $query->where('nombre_cuenta', 'like', '%' . $filters['filter_nombre_cuenta'] . '%');
    }

    if (!empty($filters['filter_perosna_sociedad'])) {
      $query->where('perosna_sociedad', 'like', '%' . $filters['filter_perosna_sociedad'] . '%');
    }

    if (!empty($filters['filter_moneda'])) {
      $query->where('currencies.id', '=', $filters['filter_moneda']);
    }

    if (!empty($filters['filter_saldo'])) {
      $query->where('saldo', 'like', '%' . $filters['filter_saldo'] . '%');
    }

    if (!empty($filters['filter_balance'])) {
      $query->where('balance', 'like', '%' . $filters['filter_balance'] . '%');
    }

    if (!empty($filters['filter_ultimo_cheque'])) {
      $query->where('ultimo_cheque', 'like', '%' . $filters['filter_ultimo_cheque'] . '%');
    }

    if (!empty($filters['filter_bank'])) {
      $query->whereHas('banks', function ($q) use ($filters) {
        $q->where('banks.name', 'like', '%' . $filters['filter_bank'] . '%');
      });
    }

    if (!empty($filters['filter_department'])) {
      $query->whereHas('departments', function ($q) use ($filters) {
        $q->where('departments.name', 'like', '%' . $filters['filter_department'] . '%');
      });
    }

    if (!empty($filters['filter_location'])) {
      $query->whereHas('locations', function ($q) use ($filters) {
        $q->where('business_locations.name', 'like', '%' . $filters['filter_location'] . '%');
      });
    }

    return $query;
  }

  public function getHtmlcolumnBank()
  {
    $htmlColumn = '';
    if ($this->banks->isNotEmpty())
      $htmlColumn = $this->banks->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
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

  public function getHtmlcolumnLocations()
  {
    $htmlColumn = '';
    if ($this->locations->isNotEmpty())
      $htmlColumn = $this->locations->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
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
