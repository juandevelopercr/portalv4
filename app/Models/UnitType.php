<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends TenantModel
{
  const SERVICIO_PROFESIONAL = 1; // Servicio profesional

  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'active',
    'created_at',
    'updated_at',
  ];
}
