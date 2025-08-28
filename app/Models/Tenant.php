<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
  use HasFactory;

  protected $table = 'tenants';

  protected $fillable = [
    'name',
    'db_name',
    'db_user',
    'db_password',
  ];

  // Relación con usuarios
  public function users()
  {
    return $this->hasMany(User::class);
  }
}
