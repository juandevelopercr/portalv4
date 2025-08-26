<?php

namespace App\Models;

use App\Models\CatalogoCuenta;
use App\Models\CentroCosto;
use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Model;

class MovimientoCentroCosto extends Model
{
  protected $table = 'movimientos_centro_costos';

  protected $fillable = [
    'movimiento_id',
    'centro_costo_id',
    'codigo_contable_id',
    'amount',
  ];

  public function movimiento()
  {
    return $this->belongsTo(Movimiento::class, 'movimiento_id');
  }

  public function centroCosto()
  {
    return $this->belongsTo(CentroCosto::class, 'centro_costo_id');
  }

  public function codigoContable()
  {
    return $this->belongsTo(CatalogoCuenta::class, 'codigo_contable_id');
  }
}
