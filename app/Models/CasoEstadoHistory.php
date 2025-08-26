<?php

namespace App\Models;

use App\Models\Caso;
use App\Models\CasoEstado;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CasoEstadoHistory extends Model
{
  // Nombre de la tabla
  protected $table = 'casos_estados_history';

  protected $fillable = [
    'caso_id',
    'caso_estado_id',
    'user_id',
  ];

  public function caso()
  {
    return $this->belongsTo(Caso::class, 'caso_id');
  }

  public function estado()
  {
    return $this->belongsTo(CasoEstado::class, 'caso_estado_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
