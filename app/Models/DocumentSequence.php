<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSequence extends TenantModel
{
  protected $table = 'document_sequences';

  protected $fillable = [
    'user_id',
    'emitter_id',
    'document_type',
    'current_sequence'
  ];

  public function businessLocation(): BelongsTo
  {
    return $this->belongsTo(BusinessLocation::class, 'emitter_id');
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
