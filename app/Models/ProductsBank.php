<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsBank extends Model
{
  use HasFactory;

  protected $table = 'products_banks'; // 👈 Importante porque la tabla no es en plural regular

  protected $fillable = [
    'product_id',
    'bank_id',
  ];

  public $timestamps = false;

  // Relaciones (opcional, si las quieres definir)
  public function product()
  {
    return $this->belongsTo(Product::class);
  }

  public function bank()
  {
    return $this->belongsTo(Bank::class);
  }
}
