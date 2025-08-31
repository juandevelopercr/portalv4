<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends TenantModel
{
  use HasFactory;

  const DOLARES = 1;
  const COLONES = 16;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'country',
    'currency',
    'code',
    'symbol',
    'thousand_separator',
    'decimal_separator',
  ];
}
