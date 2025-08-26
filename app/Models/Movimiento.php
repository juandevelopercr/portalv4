<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Models\Cuenta;
use App\Models\Currency;
use App\Models\MovimientoBalanceMensual;
use App\Models\MovimientoFactura;
use App\Models\Transaction;
use App\Observers\MovimientoObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Movimiento extends Model implements HasMedia
{
  use InteractsWithMedia;
  use LogsActivity;

  const TYPE_DEPOSITO = 'DEPOSITO';
  const TYPE_ELECTRONICO = 'ELECTRONICO';
  const TYPE_CHEQUE = 'CHEQUE';

  const STATUS_REVISION   = 'REVISION';
  const STATUS_REGISTRADO = 'REGISTRADO';
  const STATUS_RECHAZADO  = 'RECHAZADO';
  const STATUS_ANULADO    = 'ANULADO';

  protected $table = 'movimientos';

  protected $fillable = [
    'cuenta_id',
    'moneda_id',
    'tipo_movimiento',
    'lugar',
    'fecha',
    'monto',
    'monto_letras',
    'tiene_retencion',
    'saldo_cancelar',
    'diferencia',
    'descripcion',
    'numero',
    'beneficiario',
    'comprobante_pendiente',
    'bloqueo_fondos',
    'impuesto',
    'total_general',
    'status',
    'listo_para_aprobar',
    'comentarios',
    'concepto',
    'email_destinatario',
    'clonando',
    'recalcular_saldo',
  ];

  protected $casts = [
    'recalcular_saldo' => 'boolean',
    'fecha' => 'date',
    'tiene_retencion' => 'boolean',
    'comprobante_pendiente' => 'boolean',
    'bloqueo_fondos' => 'boolean',
    'listo_para_aprobar' => 'boolean',
    'clonando' => 'boolean',
    'monto' => 'decimal:5',
    'saldo_cancelar' => 'decimal:5',
    'diferencia' => 'decimal:5',
    'impuesto' => 'decimal:5',
    'total_general' => 'decimal:5',
  ];

  public function currency()
  {
    return $this->belongsTo(Currency::class, 'moneda_id');
  }

  public function cuenta()
  {
    return $this->belongsTo(Cuenta::class);
  }

  public function transactions()
  {
    return $this->belongsToMany(Transaction::class, 'movimientos_facturas', 'movimiento_id', 'transaction_id');
  }

  public function centrosCostos()
  {
    return $this->hasMany(MovimientoCentroCosto::class, 'movimiento_id');
  }

  public function scopeSearch($query, $value, $filters = [], $defaultStatus = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'movimientos.id',
      'cuenta_id',
      'cuentas.numero_cuenta',
      'cuentas.nombre_cuenta',
      'movimientos.moneda_id',
      'currencies.code',
      'tipo_movimiento',
      'lugar',
      'fecha',
      'monto',
      'monto_letras',
      'tiene_retencion',
      'saldo_cancelar',
      'diferencia',
      'descripcion',
      'numero',
      'beneficiario',
      'comprobante_pendiente',
      'bloqueo_fondos',
      'impuesto',
      'total_general',
      'status',
      'listo_para_aprobar',
      'comentarios',
      'concepto',
      'email_destinatario',
      'clonando',
    ];

    $query->select($columns)
      ->join('cuentas', 'movimientos.cuenta_id', '=', 'cuentas.id')
      ->join('currencies', 'movimientos.moneda_id', '=', 'currencies.id');


    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filterCuentas'])) {
      $query->whereIn('cuentas.id', $filters['filterCuentas']);
    }

    if (!empty($filters['filter_nombre_cuenta'])) {
      $query->where('cuentas.nombre_cuenta', 'like', '%' . $filters['filter_nombre_cuenta'] . '%');
    }

    if (!empty($filters['filter_numero'])) {
      $query->where('numero', '=', $filters['filter_numero']);
    }

    if (!empty($filters['filterFecha'])) {
      $range = explode(' to ', $filters['filterFecha']);

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
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filterFecha'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_beneficiario'])) {
      $query->where('beneficiario', 'like', '%' . $filters['filter_beneficiario'] . '%');
    }

    if (!empty($filters['filter_currency'])) {
      $query->where('currencies.id', '=',  $filters['filter_currency']);
    }

    if (!empty($filters['filter_monto'])) {
      $query->where('monto', 'like', '%' . $filters['filter_monto'] . '%');
    }

    if (!empty($filters['filter_type'])) {
      $query->where('tipo_movimiento', 'like', '%' . $filters['filter_type'] . '%');
    }

    if (!empty($filters['filter_description'])) {
      $query->where('descripcion', 'like', '%' . $filters['filter_description'] . '%');
    }

    /*
    if (!empty($filters['filter_codigo_contable'])) {
      $query->where('codigocontable', 'like', '%' . $filters['filter_codigo_contable'] . '%');
    }
    if (!empty($filters['filter_centro_costo'])) {
      $query->where('codigocontable', 'like', '%' . $filters['filter_centro_costo'] . '%');
    }
    */
    if (!empty($filters['filter_status'])) {
      $query->where('status', $filters['filter_status']);
    } elseif (!empty($defaultStatus)) {
      $query->whereIn('status', $defaultStatus);
    }

    if (isset($filters['filter_bloqueo_fondos']) && !is_null($filters['filter_bloqueo_fondos'])  && $filters['filter_bloqueo_fondos'] !== '') {
      $query->where('bloqueo_fondos', '=', $filters['filter_bloqueo_fondos']);
    }

    if (isset($filters['filter_clonando']) && !is_null($filters['filter_clonando'])  && $filters['filter_clonando'] !== '') {
      $query->where('clonando', '=', $filters['filter_clonando']);
    }

    if (isset($filters['filter_comprobante_pendiente']) && !is_null($filters['filter_comprobante_pendiente'])  && $filters['filter_comprobante_pendiente'] !== '') {
      $query->where('comprobante_pendiente', '=', $filters['filter_comprobante_pendiente']);
    }

    return $query;
  }

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('documents-movements')
      ->useDisk('public')
      ->acceptsMimeTypes([
        'application/pdf',
        'application/msword',  // .doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // .docx
        'application/vnd.ms-excel',  // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',  // .xlsx
        'image/jpeg',
        'image/png'
      ]);
    //->singleFile();  // Evita múltiples archivos si es necesario (quítalo si no aplica)
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['*'])
      ->setDescriptionForEvent(fn(string $eventName) => "El movimiento ha sido {$eventName}")
      ->useLogName('movimiento')
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs();
    // Chain fluent methods for configuration options
  }

  public function getHtmlColumnBloqueo()
  {
    if ($this->bloqueo_fondos) {
      // Icono de check redondo verde
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    } else {
      // Icono de X redonda roja
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    }
    return $output;
  }

  public function getHtmlColumnClonado()
  {
    if ($this->clonando) {
      // Icono de check redondo verde
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    } else {
      // Icono de X redonda roja
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    }
    return $output;
  }

  public function getHtmlColumnPendiente()
  {
    if ($this->comprobante_pendiente)
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    else
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    return $output;
  }

  public function getHtmlColumnListoAprobar()
  {
    if ($this->listo_para_aprobar)
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Sí"></i>';
    else
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="No"></i>';
    return $output;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md'; // Puedes cambiar a bx-md o bx-lg si deseas íconos más grandes
    $html = '<div class="d-flex align-items-center justify-content-start gap-2">';

    if ($this->tipo_movimiento == 'CHEQUE') {
      $html .= <<<HTML
        <div x-data="{ loading: false }">
          <a href="#"
            x-bind:class="{ 'disabled pointer-events-none opacity-50': loading }"
            x-on:click.prevent="
                loading = true; 
                \$dispatch('print-cheque', { id: {$this->id} }); 
                setTimeout(() => loading = false, 1000);
            "
            title="Imprimir Cheque">
            <i x-show="!loading" class='bx bx-printer text-danger'></i>
            <i x-show="loading" class="bx bx-loader-alt bx-spin text-secondary"></i>
          </a>
        </div>
      HTML;
    }

    /*
    // Eliminar
    if ($user->can('delete-movimientos')) {
      $html .= <<<HTML
        <a href="#" class="text-danger" title="Eliminar / Anular"
           wire:click.prevent="confirmarAccion({$this->id}, 'delete',
             '¿Está seguro que desea eliminar este registro?',
             'Después de confirmar, el registro será eliminado',
             'Sí, proceder')">
            <i class="bx bx-trash {$iconSize}"></i>
        </a>
        HTML;
    }
        */

    $html .= '</div>';
    return $html;
  }

  public static function getSaldoInicial(array $cuentasId, $fechaInicio = null, $fechaFin = null): array
  {
    $totalSaldoUsd = 0;
    $totalSaldoCrc = 0;

    if (!empty($cuentasId)) {
      if (is_null($fechaInicio) || is_null($fechaFin)) {
        $mes = now()->format('m');
        $anno = now()->format('Y');
      } else {
        $mes = date('m', strtotime($fechaInicio));
        $anno = date('Y', strtotime($fechaInicio));
      }

      foreach ($cuentasId as $cuentaId) {
        $saldo = MovimientoBalanceMensual::selectRaw("
                    CASE WHEN moneda_id = 1 THEN COALESCE(saldo_inicial, 0) ELSE 0 END AS total_saldo_usd,
                    CASE WHEN moneda_id = 16 THEN COALESCE(saldo_inicial, 0) ELSE 0 END AS total_saldo_crc
                ")
          ->where('cuenta_id', $cuentaId)
          ->where('anno', $anno)
          ->where('mes', $mes)
          ->first();

        if ($saldo && ($saldo->total_saldo_usd > 0 || $saldo->total_saldo_crc > 0)) {
          $totalSaldoUsd += $saldo->total_saldo_usd;
          $totalSaldoCrc += $saldo->total_saldo_crc;
        } else {
          $saldoAnterior = MovimientoBalanceMensual::selectRaw("
                        CASE WHEN moneda_id = 1 THEN COALESCE(saldo_final, 0) ELSE 0 END AS total_saldo_usd,
                        CASE WHEN moneda_id = 16 THEN COALESCE(saldo_final, 0) ELSE 0 END AS total_saldo_crc
                    ")
            ->where('cuenta_id', $cuentaId)
            ->where(function ($query) use ($anno, $mes) {
              $query->where('anno', '<', $anno)
                ->orWhere(function ($q) use ($anno, $mes) {
                  $q->where('anno', $anno)
                    ->where('mes', '<', $mes);
                });
            })
            ->orderByDesc('anno')
            ->orderByDesc('mes')
            ->first();

          $totalSaldoUsd += optional($saldoAnterior)->total_saldo_usd ?? 0;
          $totalSaldoCrc += optional($saldoAnterior)->total_saldo_crc ?? 0;
        }
      }
    }

    return [
      'total_saldo_usd' => $totalSaldoUsd,
      'total_saldo_crc' => $totalSaldoCrc,
    ];
  }

  public static function getDebito(array $ids, $dateStart, $dateEnd, $status = 'REGISTRADO', $bloqueado = false): array
  {
    $data = [
      'total_debito_usd' => 0,
      'total_debito_crc' => 0,
    ];

    if (!empty($ids)) {
      $tipos = ['ELECTRONICO', 'CHEQUE'];

      $query = Movimiento::selectRaw("
                SUM(CASE WHEN moneda_id = 1 THEN COALESCE(monto, 0) + COALESCE(impuesto, 0) ELSE 0 END) AS total_debito_usd,
                SUM(CASE WHEN moneda_id = 16 THEN COALESCE(monto, 0) + COALESCE(impuesto, 0) ELSE 0 END) AS total_debito_crc
            ")
        ->whereIn('cuenta_id', $ids)
        ->where('status', $status)
        ->where('clonando', 0)
        ->whereIn('tipo_movimiento', $tipos);

      // Condición para bloqueo de fondos
      if ($bloqueado) {
        $query->where('bloqueo_fondos', 1);
      } else {
        $query->where('bloqueo_fondos', '!=', 1);
      }

      // Condición de rango de fechas
      if (!$bloqueado && !is_null($dateStart) && !is_null($dateEnd)) {
        $query->whereBetween('fecha', [$dateStart, $dateEnd]);
      }

      $movimiento = $query->first();

      if ($movimiento) {
        $data = [
          'total_debito_usd' => $movimiento->total_debito_usd ?? 0,
          'total_debito_crc' => $movimiento->total_debito_crc ?? 0,
        ];
      }
    }

    return $data;
  }

  public static function getTransito(array $ids, $dateStart, $dateEnd, $status = 'REGISTRADO', $bloqueado = false): array
  {
    $data = [
      'total_transito_usd' => 0,
      'total_transito_crc' => 0,
    ];

    if (!empty($ids)) {
      $query = Movimiento::selectRaw("
                SUM(CASE WHEN moneda_id = 1 THEN COALESCE(monto, 0) + COALESCE(impuesto, 0) ELSE 0 END) AS total_transito_usd,
                SUM(CASE WHEN moneda_id = 16 THEN COALESCE(monto, 0) + COALESCE(impuesto, 0) ELSE 0 END) AS total_transito_crc
            ")
        ->whereIn('cuenta_id', $ids)
        ->where('status', $status)
        ->where('clonando', 0)
        ->where('tipo_movimiento', 'CHEQUE');

      // Condición de bloqueo de fondos
      if ($bloqueado) {
        $query->where('bloqueo_fondos', 1);
      } else {
        $query->where('bloqueo_fondos', '!=', 1);
      }

      // Condición de fechas
      if (!$bloqueado && !is_null($dateStart) && !is_null($dateEnd)) {
        $query->whereBetween('fecha', [$dateStart, $dateEnd]);
      }

      $movimiento = $query->first();

      if ($movimiento) {
        $data = [
          'total_transito_usd' => $movimiento->total_transito_usd ?? 0,
          'total_transito_crc' => $movimiento->total_transito_crc ?? 0,
        ];
      }
    }

    return $data;
  }

  public static function getCredito(array $ids, $dateStart, $dateEnd, $status, $bloqueado = false): array
  {
    $data = [
      'total_credito_usd' => 0,
      'total_credito_crc' => 0,
    ];

    if (!empty($ids)) {
      $query = Movimiento::selectRaw("
                SUM(CASE WHEN moneda_id = 1 THEN IFNULL(monto, 0) + IFNULL(impuesto, 0) ELSE 0 END) AS total_credito_usd,
                SUM(CASE WHEN moneda_id = 16 THEN IFNULL(monto, 0) + IFNULL(impuesto, 0) ELSE 0 END) AS total_credito_crc
            ")
        ->whereIn('cuenta_id', $ids)
        ->where('status', $status)
        ->where('clonando', 0)
        ->where('tipo_movimiento', 'DEPOSITO');

      // Condición de bloqueo
      if ($bloqueado) {
        $query->where('bloqueo_fondos', 1);
      } else {
        $query->where('bloqueo_fondos', '!=', 1);
      }

      // Rango de fechas solo si no está bloqueado
      if (!$bloqueado && !is_null($dateStart) && !is_null($dateEnd)) {
        $query->whereBetween('fecha', [$dateStart, $dateEnd]);
      }

      $movimiento = $query->first();

      if ($movimiento) {
        $data = [
          'total_credito_usd' => $movimiento->total_credito_usd ?? 0,
          'total_credito_crc' => $movimiento->total_credito_crc ?? 0,
        ];
      }
    }

    return $data;
  }

  public function getCentroCosto(): array
  {
    $codigoContable = [];
    $centroCosto = [];

    $datos = DB::table('movimientos_centro_costos')
      ->select([
        DB::raw('COALESCE(centro_costos.codigo, "") AS codigo_ccosto'),
        DB::raw('COALESCE(centro_costos.descrip, "") AS descrip_ccosto'),
        DB::raw('COALESCE(catalogo_cuentas.codigo, "") AS codigo_cuenta'),
        DB::raw('COALESCE(catalogo_cuentas.descrip, "") AS descrip_cuenta'),
      ])
      ->leftJoin('centro_costos', 'movimientos_centro_costos.centro_costo_id', '=', 'centro_costos.id')
      ->leftJoin('catalogo_cuentas', 'movimientos_centro_costos.codigo_contable_id', '=', 'catalogo_cuentas.id')
      ->where('movimientos_centro_costos.movimiento_id', $this->id)
      ->get();

    foreach ($datos as $data) {
      if (!empty($data->codigo_ccosto) || !empty($data->codigo_cuenta)) {
        $centroCosto[] = trim("{$data->codigo_ccosto} {$data->descrip_ccosto}");
        $codigoContable[] = trim("{$data->codigo_cuenta} {$data->descrip_cuenta}");
      }
    }

    return [
      'str_codigo_contable' => implode(',', $codigoContable),
      'str_centro_costo' => implode(',', $centroCosto),
    ];
  }

  function getColumnMonto()
  {
    $monto = 0;
    if ($this->tipo_movimiento == 'DEPOSITO')
      $monto = $this->monto;
    else
      $monto = $this->total_general;

    $monto = Helpers::formatDecimal($monto);
    return $monto;
  }

  function getHtmlCodigoContableColumn()
  {
    $truncatedText = '';
    if ($this->centrosCostos->isNotEmpty() && $this->centrosCostos->count() == 1) {
      $info = $this->getCentroCosto();
      $text = $info['str_codigo_contable'];
      $maxCharacters = 40; // Cambia este valor al número deseado de caracteres
      if (mb_strlen($text) > $maxCharacters) {
        $truncatedText = mb_substr($text, 0, $maxCharacters - 3) . '...'; // Agregar puntos suspensivos si el texto se ha truncado
      } else {
        $truncatedText = $text;
      }
    }
    return $truncatedText;
  }

  function getHtmlCentroCostoColumn()
  {
    $text = '';
    if ($this->centrosCostos->isNotEmpty() && $this->centrosCostos->count() == 1) {
      $info = $this->getCentroCosto();
      $text = $info['str_centro_costo'];
    }
    return $text;
  }

  public function getHtmlStatus()
  {
    $htmlData = '';

    switch ($this->status) {
      case Movimiento::STATUS_REVISION:
        $htmlData = "<span class=\"badge bg-secondary\">" . __('REVISIÓN') . "</span>";
        break;

      case Movimiento::STATUS_REGISTRADO:
        $htmlData = "<span class=\"badge bg-success\">" . __('REGISTRADO') . "</span>";
        break;

      case Movimiento::STATUS_RECHAZADO:
        $htmlData = "<span class=\"badge bg-danger\">" . __('RECHAZADO') . "</span>";
        break;

      case Movimiento::STATUS_ANULADO:
        $htmlData = "<span class=\"badge bg-warning text-danger\">" . __('ANULADO') . "</span>";
        break;
    }

    return $htmlData;
  }

  public function getHtmlColumnNumeroCuenta()
  {
    $iconSize = 'bx-md';
    $id = $this->id; // Guardar el ID en una variable para usarlo consistentemente

    $html = <<<HTML
        <a href="#"
            class="btn p-0 me-2 text-primary position-relative"
            title="Editar"
            wire:click.prevent="edit($id)"
            wire:loading.attr="disabled">

            <!-- Spinner (visible durante la carga) -->
            <span wire:loading wire:target="edit($id)" class="translate-left">
                <i class="bx bx-loader bx-spin $iconSize"></i>
            </span>
            $this->numero_cuenta
        </a>
    HTML;

    return $html;
  }
}
