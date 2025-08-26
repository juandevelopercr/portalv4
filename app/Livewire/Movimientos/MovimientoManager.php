<?php

namespace App\Livewire\Movimientos;

use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\Cuenta;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Movimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class MovimientoManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'movimientos.fecha';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public string $action = 'list';
  public $recordId = '';

  // Listados
  public $cuentas;
  public $currencies;
  public $types;
  public $liststatus;
  public $listActives;

  public int $cuenta_id;
  public int $moneda_id;
  public ?string $tipo_movimiento = null;
  public ?string $lugar = null;
  public ?string $fecha = null;
  public $monto = 0;
  public string $monto_letras = '';

  public bool $tiene_retencion = false;
  public ?float $saldo_cancelar = 0;
  public float $diferencia = 0;
  public ?string $descripcion = null;
  public ?string $numero = null;
  public ?string $beneficiario = null;

  public bool $comprobante_pendiente = false;
  public bool $bloqueo_fondos = false;
  public float $impuesto = 0;
  public float $total_general = 0;

  public string $status;
  public bool $listo_para_aprobar = false;
  public ?string $comentarios = null;
  public ?string $concepto = null;
  public ?string $email_destinatario = null;
  public bool $clonando = false;
  public $recalcular_saldo = false;

  public $closeForm = false;

  public $columns;
  public $defaultColumns;

  public $filterFecha;
  public array $filterCuentas = [];

  public $saldo_final_crc;
  public $saldo_final_usd;
  public $movementType;
  public $defaultStatus = [];
  public $fondos = 0.00;

  public $centrocosto = 30;  // Vacio
  public $codigo_contable = 78; //--- Gastos de clientes por pagar -

  public $centrosCostosValidos;

  public array $expandedRows = [];

  protected function getModelClass(): string
  {
    return Movimiento::class;
  }

  public function mount($type)
  {
    $this->cuentas = Cuenta::orderBy('nombre_cuenta', 'ASC')->get();
    $this->currencies = Currency::orderBy('code', 'ASC')->get();
    $this->listActives = [['id' => 1, 'name' => 'Si'], ['id' => 0, 'name' => 'No']];
    $this->saldo_final_crc = 0;
    $this->saldo_final_usd = 0;
    //$this->filterFecha = '01-05-2025 to 31-05-2025';

    //Se define el tipo de movimiento para poder filtrar: MOVIMIENTOS, REVISIONES
    $this->movementType = $type;

    if ($this->movementType == 'MOVIMIENTOS') {

      $this->types = [
        [
          'id'  => Movimiento::TYPE_DEPOSITO,
          'name' => Movimiento::TYPE_DEPOSITO
        ],
        [
          'id'  => Movimiento::TYPE_ELECTRONICO,
          'name' => Movimiento::TYPE_ELECTRONICO
        ],
        [
          'id'  => Movimiento::TYPE_CHEQUE,
          'name' => Movimiento::TYPE_CHEQUE
        ]
      ];

      $this->defaultStatus = ['REGISTRADO', 'ANULADO'];
      $this->liststatus = [
        [
          'id'  => Movimiento::STATUS_REGISTRADO,
          'name' => Movimiento::STATUS_REGISTRADO
        ],
        [
          'id'  => Movimiento::STATUS_ANULADO,
          'name' => Movimiento::STATUS_ANULADO
        ]
      ];
    } else {

      $this->types = [
        [
          'id'  => Movimiento::TYPE_CHEQUE,
          'name' => Movimiento::TYPE_CHEQUE
        ]
      ];

      $this->defaultStatus = ['REVISION', 'RECHAZADO'];
      $this->liststatus = [
        [
          'id'  => Movimiento::STATUS_REVISION,
          'name' => Movimiento::STATUS_REVISION
        ],
        [
          'id'  => Movimiento::STATUS_RECHAZADO,
          'name' => Movimiento::STATUS_RECHAZADO
        ],
      ];
    }
    $this->filters = session('datatable_filters.movimientos', $this->filters);
    if (isset($this->filters['filterCuentas']))
      $this->filterCuentas = $this->filters['filterCuentas'];

    if (isset($this->filters['filterFecha']))
      $this->filterFecha = $this->filters['filterFecha'];

    $this->refresDatatable();
  }

  public function render()
  {
    // no quitar esto sino dejan de funcionar el filtro en el query
    $this->filters['filterCuentas'] = $this->filterCuentas;
    $this->filters['filterFecha'] = $this->filterFecha;

    $records = Movimiento::search($this->search, $this->filters, $this->defaultStatus)
      ->orderBy('fecha', 'desc')
      ->orderByRaw('CASE WHEN numero REGEXP "^[0-9]+$" THEN CAST(numero AS UNSIGNED) ELSE 99999999999 END DESC')
      ->paginate($this->perPage);

    return view('livewire.movimientos.movimientos-datatable', [
      'records' => $records,
    ]);
  }

  public function updatedTieneRetencion($value)
  {
    $this->tiene_retencion = (int) $value;

    $this->saldo_cancelar = Helpers::getSaldoCancelar($this->recordId, $this->tiene_retencion);
  }

  public function updatedBloqueoFondos($value)
  {
    $this->bloqueo_fondos = (int) $value;
  }

  public function updatedComprobantePendiente($value)
  {
    $this->comprobante_pendiente = (int) $value;
  }

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'dateRangeSelected' => 'dateRangeSelected',
    'datatableSettingChange' => 'refresDatatable',
  ];

  public function create()
  {
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    $this->status = Movimiento::STATUS_REGISTRADO;
    $this->lugar = 'ESCAZU';

    $today = Carbon::now()->toDateString();
    $this->fecha = Carbon::parse($today)->format('d-m-Y');

    $this->dispatch('scroll-to-top');
    $this->dispatch('reinitConvertNumbertoWord');
  }

  // Definir reglas, mensajes y atributos
  protected function rules(): array
  {
    return [
      'cuenta_id' => 'required|exists:cuentas,id',
      'moneda_id' => 'required|exists:currencies,id',
      'tipo_movimiento' => 'required|in:DEPOSITO,ELECTRONICO,CHEQUE',
      'lugar' => 'nullable|string|max:150',
      'fecha' => 'required|date',
      'monto' => 'required|numeric|min:0',
      'monto_letras' => 'required|string|max:150',
      'tiene_retencion' => 'boolean',
      'saldo_cancelar' => 'nullable|numeric|min:0',
      'diferencia' => 'nullable|numeric',
      'descripcion' => 'required|string',
      'numero' => 'required|string|max:100',
      'beneficiario' => 'required|string|max:150',
      'comprobante_pendiente' => 'boolean',
      'bloqueo_fondos' => 'boolean',
      'impuesto' => 'nullable|numeric',
      'total_general' => 'nullable|numeric',
      'status' => 'required|in:REVISION,ANULADO,REGISTRADO,RECHAZADO',
      'listo_para_aprobar' => 'boolean',
      'comentarios' => 'nullable|string',
      'concepto' => 'nullable|string|max:150',
      'email_destinatario' => 'nullable|email|max:100',
      'clonando' => 'boolean',
    ];
  }

  protected function messages(): array
  {
    return [
      'cuenta_id.required' => 'La cuenta es obligatoria.',
      'cuenta_id.exists' => 'La cuenta seleccionada no es válida.',

      'moneda_id.required' => 'La moneda es obligatoria.',
      'moneda_id.exists' => 'La moneda seleccionada no es válida.',

      'tipo_movimiento.in' => 'El tipo de movimiento debe ser DEPÓSITO, ELECTRÓNICO o CHEQUE.',

      'monto.required' => 'El monto es obligatorio.',
      'monto.numeric' => 'El monto debe ser un número.',
      'monto.min' => 'El monto debe ser mayor o igual a 0.',

      'monto_letras.required' => 'El monto en letras es obligatorio.',
      'monto_letras.max' => 'El monto en letras no debe exceder los 150 caracteres.',

      'saldo_cancelar.numeric' => 'El saldo a cancelar debe ser un número.',
      'saldo_cancelar.min' => 'El saldo a cancelar no puede ser negativo.',

      'email_destinatario.email' => 'El correo del destinatario debe ser válido.',
      'email_destinatario.max' => 'El correo del destinatario no debe exceder los 100 caracteres.',

      'status.required' => 'El estado es obligatorio.',
      'status.in' => 'El estado debe ser REVISION, ANULADO, REGISTRADO o RECHAZADO.',
    ];
  }

  protected function validationAttributes(): array
  {
    return [
      'cuenta_id' => 'cuenta',
      'moneda_id' => 'moneda',
      'tipo_movimiento' => 'tipo de movimiento',
      'lugar' => 'lugar',
      'fecha' => 'fecha',
      'monto' => 'monto',
      'monto_letras' => 'monto en letras',
      'tiene_retencion' => 'tiene retención',
      'saldo_cancelar' => 'saldo a cancelar',
      'diferencia' => 'diferencia',
      'descripcion' => 'descripción',
      'numero' => 'número',
      'beneficiario' => 'beneficiario',
      'comprobante_pendiente' => 'comprobante pendiente',
      'bloqueo_fondos' => 'bloqueo de fondos',
      'impuesto' => 'impuesto',
      'total_general' => 'total general',
      'status' => 'estado',
      'listo_para_aprobar' => 'listo para aprobar',
      'comentarios' => 'comentarios',
      'concepto' => 'concepto',
      'email_destinatario' => 'correo destinatario',
      'clonando' => 'clonando',
    ];
  }

  #[On('respuesta-validacion-centros')]
  public function setValidacionCentros($valido)
  {
    if (!$valido) {
      $this->addError('centros', 'Revise los centros de costo antes de guardar.');
      $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Revise los centros de costo antes de guardar']);
      return;
    }

    if ($this->recordId) {
      $this->updateMovimiento();
    } else {
      $this->crearMovimiento();
    }
  }

  public function store()
  {
    $this->monto = floatval(str_replace(',', '', $this->monto));
    // Validación inicial del propio formulario
    $validatedData = collect($this->validate())->except('recalcular_saldo')->toArray();
    //$this->validate();

    // Marcar como no válido por defecto
    $this->centrosCostosValidos = false;

    // Emitir evento para que el hijo realice su validación
    $this->dispatch('validar-centros-costo');

    // ✅ Aquí no hacemos nada más. Esperamos la respuesta del hijo.
    // Cuando el hijo termine su validación, se llamará automáticamente `setValidacionCentros()`
  }

  private function validaMovimiento()
  {
    $fechaMovimiento = Carbon::parse($this->fecha ?? now());

    $fondos = round(Helpers::getSaldoMesCuenta($this->cuenta_id, date('Y-m-d')), 2);
    $monto = floatval(str_replace(',', '', $this->monto));
    $impuesto = floatval(str_replace(',', '', $this->impuesto));
    $montoAplicar = round($monto + $impuesto, 2);

    if (in_array($this->tipo_movimiento, ['CHEQUE', 'ELECTRONICO']) && $montoAplicar > $fondos) {
      throw new \Exception(__('Fondos insuficientes para registrar el movimiento'));
    }

    if ($fechaMovimiento->isFuture() && !$this->bloqueo_fondos) {
      throw new \Exception(__('La fecha está fuera de rango, si desea guardarlo active la casilla de bloqueo de fondos'));
    }

    if (in_array($this->tipo_movimiento, ['CHEQUE', 'ELECTRONICO']) && floatval($this->diferencia) > 0) {
      throw new \Exception(__('Existe diferencia con respecto al monto total. Corrija la información e inténtelo de nuevo'));
    }
  }

  public function crearMovimiento()
  {
    $this->monto = floatval(str_replace(',', '', $this->monto));
    $this->fecha = Carbon::parse($this->fecha)->format('Y-m-d');

    //$validatedData = $this->validate();
    $validatedData = collect($this->validate())->except('recalcular_saldo')->toArray();

    try {
      DB::transaction(function () use ($validatedData) {

        // Validaciones extra
        $this->validaMovimiento();

        // Obtener consecutivo en modo exclusivo
        if ($this->tipo_movimiento === 'CHEQUE') {
          $cuenta = Cuenta::where('id', $this->cuenta_id)->lockForUpdate()->first();
          $consecutivo = (int) $cuenta->ultimo_cheque + 1;
          $this->numero = (string)$consecutivo;
          $cuenta->ultimo_cheque = (string) $consecutivo;
          $cuenta->save();
        }

        $record = Movimiento::create($validatedData);

        // Emite evento para que el componente hijo actualice centros de costo
        $this->dispatch('save-centros-costo', ['id' => $record->id]);

        // Reset de estado
        $closeForm = $this->closeForm;
        $this->resetControls();

        $this->action = $closeForm ? 'list' : 'edit';

        if (!$closeForm) {
          $this->edit($record->id);
          $this->dispatch('$refresh');
        }

        $this->dispatch('show-notification', [
          'type' => 'success',
          'message' => __('The record has been created')
        ]);
      });
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while creating the registro') . ' - ' . $e->getMessage()
      ]);
    }

    // Importante para que lo escuche el blade y actualize el sumary
    $this->dispatch('actualizarSumary');
  }

  public function updateMovimiento()
  {
    // Quitar separadores de miles si vienen como string
    $this->monto = floatval(str_replace(',', '', $this->monto));
    $this->impuesto = floatval(str_replace(',', '', $this->impuesto));
    $this->diferencia = floatval(str_replace(',', '', $this->diferencia));
    $this->saldo_cancelar = floatval(str_replace(',', '', $this->saldo_cancelar));
    $this->recalcular_saldo = false;

    $this->fecha = Carbon::parse($this->fecha)->format('Y-m-d');

    //$validatedData = $this->validate();
    $validatedData = collect($this->validate())->except('recalcular_saldo')->toArray();
    //dd($this);
    // Validaciones equivalentes a Yii2
    if ($this->diferencia > 0 && in_array($this->tipo_movimiento, ['ELECTRONICO', 'CHEQUE'])) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Existe diferencia con respecto al monto total. Haga la distribución completa del monto e inténtelo nuevamente.')
      ]);
      return;
    }

    $fechaMovimiento = Carbon::parse($this->fecha ?? now());
    if ($fechaMovimiento->greaterThan(now()) && !$this->bloqueo_fondos) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('La fecha está fuera de rango. Active la casilla de bloqueo de fondos para continuar.')
      ]);
      return;
    }

    try {
      DB::transaction(function () use ($validatedData) {
        $record = Movimiento::findOrFail($this->recordId);

        // Asignar valores
        $record->fill($validatedData);

        // Si es anulado, sobreescribe campos
        if ($record->status === 'ANULADO') {
          $record->monto = 0;
          $record->monto_letras = '';
          $record->saldo_cancelar = 0;
          $record->diferencia = 0;
          $record->total_general = 0;
          $record->impuesto = 0;
          $record->descripcion = 'NULO: ' . $record->descripcion;
        }

        $record->save(); // Llama automáticamente al observer

        // ✅ Aplica el pago a facturas si es un depósito
        if ($record->tipo_movimiento === 'DEPOSITO') {
          $this->aplicarPago($record);
        }

        // Emite evento para que el componente hijo actualice centros de costo
        $this->dispatch('save-centros-costo', ['id' => $record->id]);

        // Reset de estado
        $closeForm = $this->closeForm;
        $this->resetControls();
        $this->dispatch('scroll-to-top');

        $this->dispatch('show-notification', [
          'type' => 'success',
          'message' => __('The record has been updated')
        ]);

        $this->action = $closeForm ? 'list' : 'edit';
        if (!$closeForm) {
          $this->edit($record->id);
        }
      });
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while updating the registro') . ': ' . $e->getMessage()
      ]);
    }
    // Importante para que lo escuche el blade y actualize el sumary
    $this->dispatch('actualizarSumary');
  }

  protected function aplicarPago(Movimiento $movimiento)
  {
    foreach ($movimiento->transactions as $transaction) {
      if ($transaction->tipo_recibo == 1) {
        $transaction->is_retencion = $movimiento->tiene_retencion ? 1 : 0;
      }

      if ($movimiento->tipo_movimiento === 'DEPOSITO') {
        $transaction->fecha_deposito_pago = $movimiento->fecha;
        $transaction->numero_deposito_pago = $movimiento->numero;
      }

      $transaction->save();
    }
  }

  public function edit($recordId = null)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Movimiento::findOrFail($recordId);
    $this->recordId = $recordId;

    $this->cuenta_id = $record->cuenta_id;
    $this->moneda_id = $record->moneda_id;
    $this->tipo_movimiento = $record->tipo_movimiento;
    $this->lugar = $record->lugar;
    $this->fecha = Carbon::parse($record->fecha)->format('d-m-Y');

    $this->monto = empty($record->monto) ? 0 : $record->monto;
    $this->monto_letras = $record->monto_letras;
    $this->tiene_retencion = $record->tiene_retencion;
    $this->saldo_cancelar = empty($record->saldo_cancelar) ? 0 : $record->saldo_cancelar;
    $this->diferencia = empty($record->diferencia) ? 0 : $record->diferencia;
    $this->descripcion = $record->descripcion;
    $this->numero = $record->numero;
    $this->beneficiario = $record->beneficiario;

    $this->comprobante_pendiente = $record->comprobante_pendiente;
    $this->bloqueo_fondos = $record->bloqueo_fondos;
    $this->impuesto = empty($record->impuesto) ? 0 : $record->impuesto;
    $this->total_general = empty($record->total_general) ? 0 : $record->total_general;

    $this->status = $record->status;
    $this->listo_para_aprobar = $record->listo_para_aprobar;
    $this->comentarios = $record->comentarios;
    $this->concepto = $record->concepto;
    $this->email_destinatario = $record->email_destinatario;
    $this->clonando = $record->clonando;

    $this->calculaFondosDisponibles($record->cuenta_id);

    $this->resetErrorBag();
    $this->resetValidation();

    $this->action = 'edit';
    $this->dispatch('reinitConvertNumbertoWord');
  }

  public function update()
  {
    // Validación inicial del propio formulario
    //$this->validate();
    $validatedData = collect($this->validate())->except('recalcular_saldo')->toArray();

    // Marcar como no válidos por defecto
    $this->centrosCostosValidos = false;

    // Emitir evento para que el hijo realice su validación
    $this->dispatch('validar-centros-costo');

    // ✅ No continuar aquí. La lógica sigue en setValidacionCentros()
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
  }

  public function resetControls()
  {
    $this->reset(
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
      'fondos',
      'closeForm'
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function setSortBy($sortByField)
  {
    if ($this->sortBy === $sortByField) {
      $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
      return;
    }

    $this->sortBy = $sortByField;
    $this->sortDir = 'DESC';
  }

  protected function calculaFondosDisponibles($cuentaId)
  {
    $this->fondos = 0;
    if ((int)$cuentaId > 0) {
      $this->moneda_id = 0;

      $cuenta = Cuenta::find($cuentaId);

      if ($cuenta) {
        $this->moneda_id = $cuenta->moneda_id;

        //$this->dispatch('select2:refresh', ['id' => 'moneda_id']);

        $fechaActual = Carbon::now()->toDateString(); // Formato 'Y-m-d'
        $saldo = Helpers::getSaldoMesCuenta($cuenta->id, $fechaActual);

        $codigoMoneda = $cuenta->currency->symbol ?? '';
        $this->fondos = $codigoMoneda . ' ' . number_format($saldo, 2, '.', ',');

        $this->dispatch('select2:refresh', ['id' => 'moneda_id']);
      }
    }
  }

  #[On('fondos-actualizados')]
  public function actualizarFondos($cuentaId)
  {
    $this->calculaFondosDisponibles($cuentaId);
  }

  public function updatedTipoMovimiento($value)
  {
    $this->setNumeroCheque();
  }

  public function setNumeroCheque()
  {
    if (!empty($this->cuenta_id)) {
      $cuenta = Cuenta::find($this->cuenta_id);

      if ($cuenta) {
        if ($this->tipo_movimiento == 'CHEQUE')
          $this->numero = $cuenta->ultimo_cheque + 1;
        else
          $this->numero = null;
      }
    }
  }

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->filters['filterCuentas'] = $this->filterCuentas;
    $this->filters['filterFecha'] = $this->filterFecha;

    //$this->dispatch('select2:refresh', ['id' => 'moneda_id']);
    $this->dispatch('updateExportFilters', [
      'search' => $this->search,
      'filters' => $this->filters,
      //'filterFecha' => $this->filterFecha,
      //'filterCuentas' => $this->filterCuentas,
      'selectedIds' => $this->selectedIds,
      'defaultStatus' => $this->defaultStatus,
    ]);
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function updatedCuentaId($value)
  {
    $this->calculaFondosDisponibles($value);
    $this->setNumeroCheque();
    $this->updatedFilters();
  }

  public function updatedFilters()
  {
    session()->put('datatable_filters.movimientos', $this->filters);
  }

  public function updatedFilterCuentas($cuentas)
  {
    $this->filterCuentas = is_array($cuentas) ? $cuentas : [$cuentas];
    $this->updatedFilters();

    $this->dispatchUpdateSummary();
  }

  public function dateRangeSelected($id, $range)
  {
    $this->dispatchUpdateSummary();
    $this->updatedFilters();
  }

  public function updatedFiltersFilterStatus($value)
  {
    $this->dispatchUpdateSummary();
  }

  public function dispatchUpdateSummary()
  {
    //$status = $this->movementType == 'MOVIMIENTOS' ? 'REGISTRADO' : 'REGISTRADO';
    $status = 'REGISTRADO';
    $this->dispatch('updateSummary', [
      'cuentasid' => $this->filterCuentas,
      'dateRange' => $this->filterFecha,
      'status'    => $status,
    ])->to('movimientos.sumary');
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'movimientos-datatable')
      ->first();

    if ($config) {
      // Verifica si ya es un array o si necesita decodificarse
      $columns = is_array($config->columns) ? $config->columns : json_decode($config->columns, true);
      $this->columns = array_values($columns); // Asegura que los índices se mantengan correctamente
      $this->perPage = $config->perPage  ?? 10; // Valor por defecto si viene null
    } else {
      $this->columns = $this->getDefaultColumns();
      $this->perPage = 10;
    }
  }

  public $filters = [
    'filter_no_usar0' => NULL,
    'filter_no_usar' => NULL,
    'filter_nombre_cuenta' => NULL,
    'filter_numero' => NULL,
    'filter_no_usar1' => NULL,
    'filter_beneficiario' => NULL,
    'filter_currency' => NULL,
    'filter_monto' => NULL,
    'filter_type' => NULL,
    'filter_description' => NULL,
    'filter_codigo_contable' => NULL,
    'filter_centro_costo' => NULL,
    'filter_status' => NULL,
    'filter_bloqueo_fondos' => NULL,
    'filter_clonando' => NULL,
    'filter_comprobante_pendiente' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => '__expand',
        'orderName' => '',
        'label' => '',
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'expand',
        'columnAlign' => 'center',
        'columnClass' => 'expand-column',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 30,
        'visible' => true,
        'expand_view' => 'livewire.movimientos.partials._expand', // o la que necesites
        'expand_condition' => 'centrosCostos', // 👈 nombre de propiedad del modelo a evaluar
      ],
      [
        'field' => 'numero_cuenta',
        'orderName' => 'cuentas.numero_cuenta',
        'label' => __('Cuenta'),
        'filter' => '',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnNumeroCuenta',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'nombre_cuenta',
        'orderName' => 'cuentas.nombre_cuenta',
        'label' => __('Nombre cuenta'),
        'filter' => 'filter_nombre_cuenta',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'numero',
        'orderName' => 'numero',
        'label' => __('Número'),
        'filter' => 'filter_numero',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'fecha',
        'orderName' => 'fecha',
        'label' => __('Fecha'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'beneficiario',
        'orderName' => 'beneficiario',
        'label' => __('Beneficiario'),
        'filter' => 'filter_beneficiario',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-200',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'code',
        'orderName' => 'currencies.code',
        'label' => __('Currency'),
        'filter' => 'filter_currency',
        'filter_type' => 'select',
        'filter_sources' => 'currencies',
        'filter_source_field' => 'code',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'monto',
        'orderName' => 'monto',
        'label' => __('Monto'),
        'filter' => 'filter_monto',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => 'getColumnMonto',
        'parameters' => [],
        'sumary' => 'tMonto',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'tipo_movimiento',
        'orderName' => 'tipo_movimiento',
        'label' => __('Type'),
        'filter' => 'filter_type',
        'filter_type' => 'select',
        'filter_sources' => 'types',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'descripcion',
        'orderName' => 'descripcion',
        'label' => __('Descripción'),
        'filter' => 'filter_description',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-500',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => '',
        'orderName' => '',
        'label' => __('Código contable'),
        'filter' => 'filter_codigo_contable',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlCodigoContableColumn',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => '',
        'orderName' => '',
        'label' => __('Centro de costo'),
        'filter' => 'filter_centro_costo',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlCentroCostoColumn',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'status',
        'orderName' => 'status',
        'label' => __('Status'),
        'filter' => 'filter_status',
        'filter_type' => 'select',
        'filter_sources' => 'liststatus',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlStatus',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'bloqueo_fondos',
        'orderName' => 'bloqueo_fondos',
        'label' => __('Bloqueo de fondo'),
        'filter' => 'filter_bloqueo_fondos',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnBloqueo',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'clonando',
        'orderName' => 'clonando',
        'label' => __('Clonado'),
        'filter' => 'filter_clonando',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnClonado',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'comprobante_pendiente',
        'orderName' => 'comprobante_pendiente',
        'label' => __('Comprobante pendiente'),
        'filter' => 'filter_comprobante_pendiente',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnPendiente',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'action',
        'orderName' => '',
        'label' => __('Actions'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'action',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ]
    ];

    return $this->defaultColumns;
  }

  public function storeAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de almacenamiento
    $this->store();
  }

  public function updateAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function resetFilters()
  {
    foreach (array_keys($this->filters) as $key) {
      $this->filters[$key] = null;
    }

    $this->selectedIds = [];

    $this->reset('filterFecha');
    $this->reset('filterCuentas');
    $this->updatedFilters();
    $this->dispatch('clearFilterselect2');
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    // static::getName() devuelve automáticamente el nombre del componente Livewire actual, útil para dispatchTo.
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforedelete()
  {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $record = Movimiento::findOrFail($recordId);

      if ($record->delete()) {

        $this->selectedIds = array_filter(
          $this->selectedIds,
          fn($selectedId) => $selectedId != $recordId
        );

        // Opcional: limpiar "seleccionar todo" si ya no aplica
        if (empty($this->selectedIds)) {
          $this->selectAll = false;
        }

        // Emitir actualización
        $this->dispatch('updateSelectedIds', $this->selectedIds);

        // Puedes emitir un evento para redibujar el datatable o actualizar la lista
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
    }

    // Importante para que lo escuche el blade y actualize el sumary
    $this->dispatch('actualizarSumary');
  }

  #[On('saldoActualizado')]
  public function actualizarSaldo($data)
  {
    $this->saldo_final_crc = $data['saldoColones'];
    $this->saldo_final_usd = $data['saldoDolares'];
  }

  #[On('print-cheque')]
  public function printMovimiento($id)
  {
    $movimiento = \App\Models\Movimiento::findOrFail($id);
    $cuenta = $movimiento->cuenta;

    $html = view('livewire.movimientos.export.print', compact('movimiento', 'cuenta'))->render();

    $this->dispatch('trigger-print-cheque', $html); // Livewire 3
  }

  #[On('clonar')]
  public function clonar($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    DB::beginTransaction();

    try {
      $original = Movimiento::findOrFail($recordId);

      // Clonar el producto principal
      $cloned = $original->replicate();
      $cloned->numero = $original->numero . ' (clonado)';
      $cloned->fecha = Carbon::today(); // Solo la fecha, sin hora
      $cloned->diferencia = 0;
      $cloned->email_destinatario = '';
      $cloned->clonando = 1;

      if (in_array($original->tipo_movimiento, ['CHEQUE', 'ELECTRONICO'])) {
        $fondos = Helpers::getSaldoMesCuenta($original->cuenta_id, date('Y-m-d'));
        if ($fondos <= 0)
          throw new \Exception(__('Fondos insuficientes para registrar el movimiento'));
      }

      $cuenta = Cuenta::where('id', $original->cuenta_id)->lockForUpdate()->first();
      if ($cuenta && $original->tipo_movimiento == 'CHEQUE') {
        $consecutivo = (int) $cuenta->ultimo_cheque + 1;
        $cloned->numero = (string) $consecutivo;
        $cuenta->ultimo_cheque = $cloned->numero;
        $cuenta->save();
      }

      $cloned->save();

      // Clonar honorarios/timbres
      foreach ($original->centrosCostos as $item) {
        $copy = $item->replicate();
        $copy->movimiento_id = $cloned->id;
        $copy->save();
      }
      DB::commit();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The movement has been successfully cloned')]);

      $this->edit($cloned->id);
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. While cloning the movement') . ' ' . $e->getMessage()]);
      Log::error('Error al clonar movimiento.', ['error' => $e->getMessage()]);
    }
  }

  public function exportMovimientos()
  {
    $type = 'xls'; // puedes hacerlo dinámico si quieres

    // Obtener los datos usando el scope y sin paginar
    $datos = Movimiento::search($this->search, $this->filters, $this->defaultStatus)
      ->orderBy('fecha', 'DESC')
      ->get();

    // Elegir la vista según el tipo de exportación
    $view = 'livewire.movimientos.export.movimientos_xls';
    $filename = 'movimientos-' . now()->format('Ymd_His') . '.xls';
    $contentType = 'application/vnd.ms-excel';

    // Renderizar contenido
    $html = view::make($view, ['datos' => $datos])->render();

    // Devolver como archivo descargable
    return response()->streamDownload(function () use ($html) {
      echo $html;
    }, $filename, ['Content-Type' => $contentType]);
  }

  #[On('actualizarSumary')]
  public function actualizarSumary()
  {
    Log::debug('Entro al actualizarSumary');
    $this->dispatchUpdateSummary();
  }

  #[On('updateSaldoCancelar')]
  public function updateSaldoCancelar()
  {
    if ($this->recordId) {
      $saldoCancelar = Helpers::getSaldoCancelar($this->recordId, (int)$this->tiene_retencion);
      $diferencia = $this->monto - $saldoCancelar;
      $this->saldo_cancelar = $saldoCancelar;
      $this->diferencia = $diferencia;
      $this->updateMovimiento();
    }
  }

  public function sendComprobanteByEmail()
  {
    $movimiento = Movimiento::findOrFail($this->recordId);

    $sent = Helpers::sendComprobanteMovimientoEmail($movimiento, $this->concepto, $this->email_destinatario);

    if ($sent) {
      $menssage = __('An email has been sent to the following addresses:') . ' ' . $this->email_destinatario;

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => $menssage
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }

  public function beforeclonar()
  {
    $this->confirmarAccion(
      null,
      'clonar',
      '¿Está seguro que desea clonar este registro?',
      'Después de confirmar, el registro será clonado',
      __('Sí, proceed')
    );
  }

  /*
  function getRecordAction($recordId)
  {
    if (!isset($recordId) || is_null($recordId)) {
      if (empty($this->selectedIds)) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => 'Debe seleccionar un registro.'
        ]);
        return;
      }

      if (count($this->selectedIds) > 1) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => 'Solo se permite seleccionar un registro.'
        ]);
        return;
      }

      if (count($this->selectedIds) == 1) {
        $recordId = $this->selectedIds[0];
      }
    }

    return $recordId;
  }
  */

  public function toggleExpand($recordId)
  {
    if (in_array($recordId, $this->expandedRows)) {
      $this->expandedRows = array_filter($this->expandedRows, fn($id) => $id !== $recordId);
    } else {
      $this->expandedRows[] = $recordId;
    }
  }
}
