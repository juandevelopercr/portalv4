<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends TenantModel
{
  use HasFactory;

  protected $table = 'transactions_payments';

  protected $fillable = [
    'transaction_id',
    'tipo_medio_pago',
    'medio_pago_otros',
    'total_medio_pago',
    'banco',
    'referencia',
    'detalle',
  ];

  public function transaction()
  {
    return $this->belongsTo(Transaction::class);
  }
}
