<?php

namespace App\Models;

use App\Models\Movimiento;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class MovimientoFactura extends Model
{
  protected $table = 'movimientos_facturas';

  public $incrementing = false;
  public $timestamps = false;

  //protected $primaryKey = ['movimiento_id', 'transaction_id'];

  protected $fillable = [
    'movimiento_id',
    'transaction_id',
  ];

  public function movimiento()
  {
    return $this->belongsTo(Movimiento::class, 'movimiento_id');
  }

  public function transaction()
  {
    return $this->belongsTo(Transaction::class, 'transaction_id');
  }
}
