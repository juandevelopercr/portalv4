<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'name',
  ];
}
