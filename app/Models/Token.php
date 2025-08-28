<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends TenantModel
{
  use HasFactory;

  protected $fillable = [
    'issuer',
    'access_token',
    'access_token_expires_at',
    'refresh_token',
    'refresh_token_expires_at'
  ];
}
