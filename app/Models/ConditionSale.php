<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;

class ConditionSale extends TenantModel
{
  const CREDIT      = '02';
  const SELLCREDIT  = '10';
  const OTHER       = '99';

  // Columnas asignables masivamente
  protected $fillable = [
    'name',
    'code',
    'active',
  ];
}
