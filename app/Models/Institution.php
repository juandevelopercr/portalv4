<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends TenantModel
{
  use HasFactory;

  protected $table = 'institutions'; // Nombre de la tabla

  protected $fillable = [
    'name',
    'code',
  ];
}
