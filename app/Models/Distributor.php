<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Distributor extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'contact_name',
    'phone',
    'email',
    'country_id',
    'state_id',
    'city_id',
    'address',
    'zipcode',
    'website',
    'tax_id_type',
    'tax_id',
    'photo',
    'latitude',
    'longitude',
    'active'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [];
  }

  /**
   * Get the country that owns the User
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function country(): BelongsTo
  {
    return $this->belongsTo(Country::class);
  }

  /**
   * Get the state that owns the User
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function state(): BelongsTo
  {
    return $this->belongsTo(State::class);
  }

  /**
   * Get the city that owns the User
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function city(): BelongsTo
  {
    return $this->belongsTo(City::class);
  }

  // En tu modelo User.php
  public function scopeSearch($query, $value)
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'distributors.id',
      'distributors.name as name',
      'contact_name',
      'email',
      'phone',
      'photo',
      'distributors.created_at',
      'countries.name as country_name',
      'distributors.active'
    ];

    return $query->select($columns)
      ->leftJoin('countries', 'distributors.country_id', '=', 'countries.id')
      ->where(function ($q) use ($value) {
        $q->where('distributors.name', 'like', "%{$value}%")
          ->orWhere('contact_name', 'like', "%{$value}%")
          ->orWhere('email', 'like', "%{$value}%")
          ->orWhere('phone', 'like', "%{$value}%")
          ->orWhere('distributors.active', 'like', "%{$value}%");
      });
  }
}
