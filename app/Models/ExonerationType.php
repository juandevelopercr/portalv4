<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExonerationType extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'description',
    'status',
    'created_at',
    'updated_at',
  ];
}
