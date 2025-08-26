<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\Caratula;
use App\Models\CasoEstado;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Garantia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Caso extends Model implements HasMedia
{
  use InteractsWithMedia;
  use LogsActivity;
  use SoftDeletes;

  protected static $logName = 'caso';
  protected static $logAttributes = ['*']; // opcional si usas logOnly(['*'])

  protected $fillable = [
    'numero',
    'numero_gestion',
    'deudor',
    'abogado_cargo_id',
    'abogado_revisor_id',
    'abogado_formalizador_id',
    'asistente_id',
    'currency_id',
    'caratula_id',
    'garantia_id',
    'department_id',
    'estado_id',
    'numero_garantia',
    'nombre_formalizo',
    'bank_id',
    'sucursal',
    'monto',
    'numero_tomo',
    'asiento_presentacion',
    'fecha_creacion',
    'fecha_firma',
    'fecha_presentacion',
    'fecha_inscripcion',
    'fecha_entrega',
    'fecha_caratula',
    'fecha_precaratula',
    'costo_caso_retiro',
    'observaciones',
    'pendientes',
    'fiduciaria',
    'desarrollador',
    'cedula',
    'num_operacion',
    'cedula_deudor',
  ];

  public function abogadoCargo()
  {
    return $this->belongsTo(User::class, 'abogado_cargo_id');
  }

  public function abogadoRevisor()
  {
    return $this->belongsTo(User::class, 'abogado_revisor_id');
  }

  public function abogadoFormalizador()
  {
    return $this->belongsTo(User::class, 'abogado_formalizador_id');
  }

  public function asistente()
  {
    return $this->belongsTo(User::class, 'asistente_id');
  }

  public function currency()
  {
    return $this->belongsTo(Currency::class);
  }

  public function caratula()
  {
    return $this->belongsTo(Caratula::class);
  }

  public function garantia()
  {
    return $this->belongsTo(Garantia::class);
  }

  public function department()
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  public function estado()
  {
    return $this->belongsTo(CasoEstado::class, 'estado_id');
  }

  public function bank()
  {
    return $this->belongsTo(Bank::class, 'bank_id');
  }

  public function pendientes()
  {
    return $this->hasMany(CasoSituacion::class, 'caso_id')
      ->where('tipo', 'PENDIENTE');
  }

  public function defectuosos()
  {
    return $this->hasMany(CasoSituacion::class, 'caso_id')
      ->where('tipo', 'DEFECTUOSO');
  }

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('casos_general_documents')
      ->useDisk('public')
      ->acceptsMimeTypes([
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png'
      ]);
    //->singleFile();  // Evita múltiples archivos si es necesario (quítalo si no aplica)

    $this->addMediaCollection('casos_bank_documents')
      ->useDisk('public')
      ->acceptsMimeTypes([
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png'
      ]);
    //->singleFile();  // Evita múltiples archivos si es necesario (quítalo si no aplica)
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      //->logOnly(['*'])
      ->logOnly(['estado_id']) // solo este campo nos interesa
      ->setDescriptionForEvent(fn(string $eventName) => __('fields.caso_event', ['event' => __('fields.event_' . $eventName)]))
      ->useLogName('caso')
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs();
    // Chain fluent methods for configuration options
  }

  public function tapActivity(Activity $activity, string $eventName)
  {
    if ($eventName === 'updated' && ! $this->wasChanged('estado_id')) {
      // Anula el log si no cambió estado
      $activity->preventSubmit();
    }
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $estadoRetiro = CasoEstado::RETIRO;

    $columns = [
      'casos.id',
      'numero',
      'numero_gestion',
      'deudor',
      'abogado_cargo_id',
      'abogado_revisor_id',
      'abogado_formalizador_id',
      'asistente_id',
      'currency_id',
      'caratula_id',
      'garantia_id',
      'department_id',
      'estado_id',
      'numero_garantia',
      'nombre_formalizo',
      'bank_id',
      'sucursal',
      'monto',
      'numero_tomo',
      'asiento_presentacion',
      'fecha_creacion',
      'fecha_firma',
      'fecha_presentacion',
      'fecha_inscripcion',
      'fecha_entrega',
      'fecha_caratula',
      'fecha_precaratula',
      'costo_caso_retiro',
      'observaciones',
      'pendientes',
      'fiduciaria',
      'desarrollador',
      'cedula',
      'num_operacion',
      'cedula_deudor',
      'uc.name as abogado_cargo',
      'ur.name as abogado_revisor',
      'uf.name as abogado_formalizador',
      'ua.name as asistente',
      'departments.name as department',
      'banks.name as bank_name',
      'garantias.name as garantia',
      'caratulas.name as caratula',
      'casos_estados.name as estado',
      DB::raw("
        CASE 
            WHEN currency_id = 1 AND estado_id = {$estadoRetiro}
                THEN COALESCE(monto, 0) + COALESCE(costo_caso_retiro, 0)
            ELSE COALESCE(monto, 0)
        END as monto_usd
    "),
      DB::raw("
        CASE 
            WHEN currency_id = 16 AND estado_id = {$estadoRetiro}
                THEN COALESCE(monto, 0) + COALESCE(costo_caso_retiro, 0)
            ELSE COALESCE(monto, 0)
        END as monto_crc
    "),
    ];

    $query->select($columns)
      ->leftJoin('users as uc', 'casos.abogado_cargo_id', '=', 'uc.id')
      ->leftJoin('users as ur', 'casos.abogado_revisor_id', '=', 'ur.id')
      ->leftJoin('users as uf', 'casos.abogado_formalizador_id', '=', 'uf.id')
      ->leftJoin('users as ua', 'casos.asistente_id', '=', 'ua.id')
      ->join('currencies', 'casos.currency_id', '=', 'currencies.id')
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->leftJoin('caratulas', 'casos.caratula_id', '=', 'caratulas.id')
      ->leftJoin('garantias', 'casos.garantia_id', '=', 'garantias.id')
      ->join('departments', 'casos.department_id', '=', 'departments.id')
      ->join('casos_estados', 'casos.estado_id', '=', 'casos_estados.id');


    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_numero'])) {
      $query->where('casos.numero', 'like', '%' . $filters['filter_numero'] . '%');
    }

    if (!empty($filters['filter_numero_gestion'])) {
      $query->where('casos.numero_gestion', 'like', '%' . $filters['filter_numero_gestion'] . '%');
    }

    if (!empty($filters['filter_fecha_creacion'])) {
      $range = explode(' to ', $filters['filter_fecha_creacion']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_creacion', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_creacion'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_creacion', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_deudor'])) {
      $query->where('casos.deudor', 'like', '%' . $filters['filter_deudor'] . '%');
    }

    if (!empty($filters['filter_department'])) {
      $query->where('casos.department_id', '=', $filters['filter_department']);
    }

    if (!empty($filters['filter_abogado_cargo'])) {
      $query->where('uc.id', '=', $filters['filter_abogado_cargo']);
    }

    if (!empty($filters['filter_bank'])) {
      $query->where('casos.bank_id', '=', $filters['filter_bank']);
    }

    if (!empty($filters['filter_numero_tomo'])) {
      $query->where('casos.numero_tomo', 'like', '%' . $filters['filter_numero_tomo'] . '%');
    }

    if (!empty($filters['filter_asiento_presentacion'])) {
      $query->where('casos.asiento_presentacion', 'like', '%' . $filters['filter_asiento_presentacion'] . '%');
    }

    if (!empty($filters['filter_garantia'])) {
      $query->where('casos.garantia_id', '=', $filters['filter_garantia']);
    }

    if (!empty($filters['filter_estado'])) {
      $query->where('casos_estados.id', '=', $filters['filter_estado']);
    }

    if (!empty($filters['filter_fecha_firma'])) {
      $range = explode(' to ', $filters['filter_fecha_firma']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_firma', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_firma'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_firma', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_fecha_entrega'])) {
      $range = explode(' to ', $filters['filter_fecha_entrega']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_entrega', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_entrega'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_entrega', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_fecha_presentacion'])) {
      $range = explode(' to ', $filters['filter_fecha_presentacion']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_presentacion', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_presentacion'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_presentacion', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_fecha_inscripcion'])) {
      $range = explode(' to ', $filters['filter_fecha_inscripcion']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_inscripcion', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_inscripcion'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_inscripcion', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_monto_usd'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('casos.currency_id', 1)
          ->where('casos.monto', 'like', '%' . $filters['filter_monto_usd'] . '%');
      });
    }

    if (!empty($filters['filter_monto_crc'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('casos.currency_id', 16)
          ->where('casos.monto', 'like', '%' . $filters['filter_monto_crc'] . '%');
      });
    }

    return $query;
  }

  public function getHtmlStatus()
  {
    $htmlData = '';

    switch ((int) $this->estado_id) {
      case 1:
        $htmlData = "<span class=\"badge bg-secondary\">" . __('SIN ASIGNAR') . "</span>";
        break;
      case 2:
        $htmlData = "<span class=\"badge bg-info\">" . __('ASIGNADO') . "</span>";
        break;
      case 3:
        $htmlData = "<span class=\"badge bg-success\">" . __('FORMALIZADO') . "</span>";
        break;
      case 4:
        $htmlData = "<span class=\"badge bg-warning text-dark\">" . __('EN TRÁMITE') . "</span>";
        break;
      case 5:
        $htmlData = "<span class=\"badge bg-success text-light\">" . __('INSCRITO') . "</span>";
        break;
      case 6:
        $htmlData = "<span class=\"badge bg-danger\">" . __('DEFECTUOSO') . "</span>";
        break;
      case 7:
        $htmlData = "<span class=\"badge bg-dark\">" . __('RETIRO') . "</span>";
        break;
      case 8:
        $htmlData = "<span class=\"badge bg-primary\">" . __('ENTREGADO') . "</span>";
        break;
      case 9:
        $htmlData = "<span class=\"badge bg-warning text-dark\">" . __('REINGRESO') . "</span>";
        break;
      default:
        $htmlData = "<span class=\"badge bg-light text-dark\">" . __('DESCONOCIDO') . "</span>";
        break;
    }

    return $htmlData;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    if ($user->can('export-casos') && $this->pendientes()->exists()) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Caso pendiente"
                wire:click="downloadCasoPendiente({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadCasoPendiente">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadCasoPendiente({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadCasoPendiente({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }
}
