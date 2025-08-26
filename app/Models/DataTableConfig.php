<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTableConfig extends Model
{
  use HasFactory;

  protected $table = 'datatable_configs';

  protected $fillable = ['user_id', 'datatable_name', 'columns', 'perPage'];

  protected $casts = [
    'columns' => 'array', // Parsear el JSON como array
  ];
}
