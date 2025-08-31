<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTableConfig extends TenantModel
{
  use HasFactory;

  protected $table = 'datatable_configs';

  protected $fillable = ['user_id', 'datatable_name', 'columns', 'perPage'];

  protected $casts = [
    'columns' => 'array', // Parsear el JSON como array
  ];
}
