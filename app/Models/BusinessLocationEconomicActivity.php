<?php

namespace App\Models;

use App\Models\BusinessLocation;
use App\Models\EconomicActivity;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;

class BusinessLocationEconomicActivity extends TenantModel
{
  // Nombre de la tabla
  protected $table = 'business_locations_economic_activities';

  // Deshabilitar timestamps (no existen en la tabla)
  public $timestamps = false;

  // Campos clave
  protected $fillable = [
    'location_id',
    'economic_activity_id',
    'default'
  ];

  /**
   * Relación con el modelo BusinessLocation.
   */
  public function businessLocation()
  {
    return $this->belongsTo(BusinessLocation::class, 'location_id');
  }

  /**
   * Relación con el modelo EconomicActivity.
   */
  public function economicActivity()
  {
    return $this->belongsTo(EconomicActivity::class, 'economic_activity_id');
  }
}
