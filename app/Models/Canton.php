<?php

namespace App\Models;

use App\Models\Disctrict;
use App\Models\Province;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canton extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'province_id',
    'active',
  ];

  /**
   * Get the province that owns the canton.
   */
  public function province()
  {
    return $this->belongsTo(Province::class);
  }

  /**
   * Get the disctricts for the canton.
   */
  public function disctricts()
  {
    return $this->hasMany(Disctrict::class);
  }
}
