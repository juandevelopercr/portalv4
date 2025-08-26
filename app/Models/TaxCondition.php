<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
