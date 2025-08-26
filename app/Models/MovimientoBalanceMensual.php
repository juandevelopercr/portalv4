<?php

namespace App\Models;

use App\Models\Cuenta;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;

class MovimientoBalanceMensual extends Model
{
  protected $table = 'movimientos_balance_mensual';

  protected $fillable = [
    'cuenta_id',
    'moneda_id',
    'mes',
    'anno',
    'saldo_inicial',
    'saldo_final',
  ];

  public function cuenta()
  {
    return $this->belongsTo(Cuenta::class, 'cuenta_id');
  }

  public function moneda()
  {
    return $this->belongsTo(Currency::class, 'moneda_id');
  }
}
