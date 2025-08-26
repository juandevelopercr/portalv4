<?php

namespace App\Models;

use App\Models\Caso;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasoSituacion extends Model
{
  use HasFactory;

  protected $table = 'casos_situaciones';

  protected $fillable = [
    'caso_id',
    'name',
    'responsable',
    'fecha',
    'tipo',
    'estado'
  ];

  // Relación con el modelo Caso
  public function caso()
  {
    return $this->belongsTo(Caso::class, 'caso_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'caso_id',
      'name',
      'responsable',
      'tipo',
      'fecha',
      'estado'
    ];

    $query->select($columns)->with('caso');

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_responsable'])) {
      $query->where('responsable', 'like', '%' . $filters['filter_responsable'] . '%');
    }

    if (!empty($filters['filter_fecha'])) {
      $range = explode(' to ', $filters['filter_fecha']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_estado'])) {
      $query->where('estado', '=',  $filters['filter_estado']);
    }

    return $query;
  }
}
