<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryLevelTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'active',
    ];
}
