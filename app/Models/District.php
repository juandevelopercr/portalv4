<?php

namespace App\Models;

use App\Models\Canton;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'active',
        'canton_id',
    ];

    /**
     * Get the canton that owns the disctrict.
     */
    public function canton()
    {
        return $this->belongsTo(Canton::class);
    }
}
