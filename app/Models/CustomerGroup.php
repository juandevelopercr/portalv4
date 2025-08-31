<?php

namespace App\Models;

use App\Models\Business;
use App\Models\SellingPriceGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TenantModel;

class CustomerGroup extends TenantModel
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'business_id',
    'name',
    'amount',
    'price_calculation_type',
    'selling_price_group_id',
    'created_by',
  ];

  /**
   * Get the business that owns the customer group.
   */
  public function business()
  {
    return $this->belongsTo(Business::class);
  }

  /**
   * Get the selling price group associated with the customer group.
   */
  public function sellingPriceGroup()
  {
    return $this->belongsTo(SellingPriceGroup::class);
  }

  /**
   * Get the user who created the customer group.
   */
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
