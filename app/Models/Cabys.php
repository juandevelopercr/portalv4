<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabys extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'category1',
    'description1',
    'category2',
    'description2',
    'category3',
    'description3',
    'category4',
    'description4',
    'category5',
    'description5',
    'category6',
    'description6',
    'category7',
    'description7',
    'category8',
    'description8',
    'code',
    'description_service',
    'tax',
    'status',
    'created_at',
    'updated_at'
  ];

  // En tu modelo User.php
  public function scopeSearch($query, $value)
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'category1',
      'description1',
      'category2',
      'description2',
      'category3',
      'description3',
      'category4',
      'description4',
      'category5',
      'description5',
      'category6',
      'description6',
      'category7',
      'description7',
      'category8',
      'description8',
      'code',
      'description_service',
      'tax',
      'active',
      'created_at',
      'updated_at'
    ];

    return $query->select($columns)
      ->where(function ($q) use ($value) {
        $q->where('code', 'like', "%{$value}%")
          ->orWhere('category1', 'like', "%{$value}%")
          ->orWhere('description1', 'like', "%{$value}%")
          ->orWhere('category2', 'like', "%{$value}%")
          ->orWhere('description2', 'like', "%{$value}%")
          ->orWhere('category3', 'like', "%{$value}%")
          ->orWhere('description3', 'like', "%{$value}%")
          ->orWhere('category4', 'like', "%{$value}%")
          ->orWhere('description4', 'like', "%{$value}%")
          ->orWhere('category5', 'like', "%{$value}%")
          ->orWhere('description5', 'like', "%{$value}%")
          ->orWhere('category6', 'like', "%{$value}%")
          ->orWhere('description6', 'like', "%{$value}%")
          ->orWhere('category7', 'like', "%{$value}%")
          ->orWhere('description7', 'like', "%{$value}%")
          ->orWhere('category8', 'like', "%{$value}%")
          ->orWhere('description8', 'like', "%{$value}%")
          ->orWhere('description_service', 'like', "%{$value}%");
      });
  }
}
