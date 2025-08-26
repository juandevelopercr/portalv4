<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    const SERVICIO_PROFESIONAL = 1; // Servicio profesional

    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'active',
        'created_at',
        'updated_at',
    ];
}
