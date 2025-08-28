<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalChargeType extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'active'
  ];

  protected $casts = [
    'active' => 'boolean',
  ];
}
