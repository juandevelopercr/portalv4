<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessCustomerCalculoRegistro extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'business_id',
    'contact_id',
  ];

  public function business()
  {
    return $this->belongsTo(Business::class);
  }

  public function contact()
  {
    return $this->belongsTo(Contact::class);
  }
}
