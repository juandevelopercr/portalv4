<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentProduct extends Model
{
  protected $table = 'department_products';

  protected $fillable = [
    // Reemplaza con los campos reales de tu tabla:
    'department_id',
    'product_id',
  ];

  public $timestamps = false; // si tu tabla no tiene created_at/updated_at

  // Relaciones (opcional)
  public function department()
  {
    return $this->belongsTo(Department::class);
  }

  public function product()
  {
    return $this->belongsTo(Product::class);
  }
}
