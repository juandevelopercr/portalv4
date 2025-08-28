<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class TenantModel extends Model
{
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);

    // Forzar siempre la conexión tenant si está configurada
    if (Config::get('database.default') === 'tenant') {
      $this->setConnection('tenant');
    }
  }
}
