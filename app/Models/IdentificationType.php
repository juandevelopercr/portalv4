<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentificationType extends TenantModel
{
  const CEDULA_FISICA = 1;
  const CEDULA_JURIDICA = 2;
  const DIMEX = 3;
  const NITE = 4;
  const EXTRANSJERO = 5;
  const NO_CONTRIBUYENTE = 6;

  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'active',
  ];
}
