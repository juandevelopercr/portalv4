<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentBank extends Model
{
  protected $table = 'department_banks';

  protected $fillable = [
    // Reemplaza con los campos reales de tu tabla:
    'department_id',
    'bank_id',
  ];

  public $timestamps = false; // si tu tabla no tiene created_at/updated_at

  // Relaciones (opcional)
  public function department()
  {
    return $this->belongsTo(Department::class);
  }

  public function banks()
  {
    return $this->belongsTo(Bank::class);
  }
}
