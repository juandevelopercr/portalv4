<?php

namespace App\Models;

use App\Models\BusinessLocation;
use App\Models\Contact;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EconomicActivity extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'code',
    'name',
    'description',
  ];

  public function contacts()
  {
    return $this->belongsToMany(Contact::class, 'contacts_economic_activities', 'economic_activity_id', 'contact_id');
  }

  public function businessLocations()
  {
    return $this->belongsToMany(BusinessLocation::class, 'business_locations_economic_activities', 'economic_activity_id', 'location_id');
  }
}
