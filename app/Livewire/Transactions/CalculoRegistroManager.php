<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Bank;
use App\Models\Business;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\DocumentSequenceService;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class CalculoRegistroManager extends TransactionManager
{
  public $customers;
  public $bank_name;
  public $currency_code;
  public $issuer_name;
  public $codigo_contable_descrip;
  public $user_name;
  public $infoCaso = '';
  public $invoice;
  public $monedas;
  public $message;

  public $sortDir = 'DESC';
  public $sortBy = 'transactions.transaction_date';

  public array $selectedNormal = [];
  public array $selectedIva = [];
  public array $selectedNoIva = [];

  public bool $selectAllNormal = false;
  public bool $selectAllIva = false;
  public bool $selectAllNoIva = false;

  // esto para poder hacer refresh del componente
  public int $refreshCounter = 0;

  public array $lines = []; // ✅ Debe ser array para Livewire

  public $filters = [
    'filter_consecutivo' => NULL,
    'filter_proforma_no' => NULL,
    'filter_customer_name' => NULL,
    'filter_user_name' => NULL,
    'filter_transaction_date' => NULL,
    'filter_nombre_caso' => NULL,
    'filter_registro_change_type' => NULL,
    'filter_issuer_name' => NULL,
    'filter_bank_name' => NULL,
    'filter_currency_code' => NULL,
    'filter_proforma_type' => NULL,
    'filter_fecha_envio_email' => NULL,
    'filter_totalComprobante' => NULL,
    'filter_status' => NULL,
    'filter_action' => NULL,
  ];

  public $listaUsuarios;

  public function mount()
  {
    parent::mount();
    // Aquí puedes agregar lógica específica para proformas
    $this->statusOptions = collect([['id' => 'FACTURADA', 'name' => __('FACTURADA')]]);
    $this->monedas = Currency::orderBy('code', 'ASC')->get();
    $this->listaUsuarios = User::where('active', 1)->orderBy('name', 'ASC')->get();
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'calculo-registro-datatable')
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

  public function getDefaultColumns(): array
  {
    $this->defaultColumns = [
      [
        'field' => 'consecutivo',
        'orderName' => 'consecutivo',
        'label' => __('Consecutivo'),
        'filter' => 'filter_consecutivo',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
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
        'field' => 'proforma_no',
        'orderName' => 'proforma_no',
        'label' => __('No. Proforma'),
        'filter' => 'filter_proforma_no',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
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
        'field' => 'customer_name',
        'orderName' => 'contacts.name',
        'label' => __('Customer'),
        'filter' => 'filter_customer_name',
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
        'field' => 'user_name',
        'orderName' => 'users.name',
        'label' => __('User'),
        'filter' => 'filter_user_name',
        'filter_type' => 'select',
        'filter_sources' => 'users',
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
        'field' => 'transaction_date',
        'orderName' => 'transactions.transaction_date',
        'label' => __('Emmision Date'),
        'filter' => 'filter_transaction_date',
        'filter_type' => 'date',
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
        'field' => 'caso_info',
        'orderName' => 'caso_info',
        'label' => __('Nombre de caso o referencia'),
        'filter' => 'filter_nombre_caso',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
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
        'field' => 'registro_change_type',
        'orderName' => 'registro_change_type',
        'label' => __('Tipo de cambio'),
        'filter' => 'filter_registro_change_type',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
        'field' => 'issuer_name',
        'orderName' => 'business_locations.name',
        'label' => __('Issuer'),
        'filter' => 'filter_issuer_name',
        'filter_type' => 'select',
        'filter_sources' => 'issuers',
        'filter_source_field' => 'name',
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
        'field' => 'bank_name',
        'orderName' => 'banks.name',
        'label' => __('Bank'),
        'filter' => 'filter_bank_name',
        'filter_type' => 'select',
        'filter_sources' => 'banks',
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
        'field' => 'currency_code',
        'orderName' => 'currencies.code',
        'label' => __('Currency'),
        'filter' => 'filter_currency_code',
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
        'field' => 'proforma_type',
        'orderName' => 'transactions.proforma_type',
        'label' => __('Type of Notarial Act'),
        'filter' => 'filter_proforma_type',
        'filter_type' => 'select',
        'filter_sources' => 'proformaTypes',
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
        'field' => 'fecha_envio_email',
        'orderName' => 'transactions.fecha_envio_email',
        'label' => __('Fecha de envio de email'),
        'filter' => 'filter_fecha_envio_email',
        'filter_type' => 'date',
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
        'field' => 'totalComprobante',
        'orderName' => '',
        'label' => __('Total'),
        'filter' => 'filter_totalComprobante',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tComprobante',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],

      [
        'field' => 'status',
        'orderName' => 'transactions.proforma_status',
        'label' => __('Status'),
        'filter' => 'filter_status',
        'filter_type' => 'select',
        'filter_sources' => 'statusOptions',
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
        'function' => 'getCalculoRegistroHtmlColumnAction',
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

  protected function getFilteredQuery()
  {
    $business = Business::find(1);

    $customersId = $business ? $business->customerCalculoRegistros->pluck('id') : collect();

    $query = Transaction::search($this->search, $this->filters)
      ->join('transactions_commissions', 'transactions_commissions.transaction_id', '=', 'transactions.id')
      ->where('document_type', $this->document_type)
      ->where('proforma_type',  'GASTO')
      ->where('proforma_status', Transaction::FACTURADA)
      ->where('transactions_commissions.centro_costo_id', $business->centro_costo_calculo_registro_id)
      ->where(function ($q) use ($customersId) {
        $q->where(function ($sub) {
          $sub->where('proforma_status', Transaction::FACTURADA)
            ->whereNotNull('fecha_deposito_pago')
            ->whereNotNull('numero_deposito_pago');
        })->orWhere(function ($sub) use ($customersId) {
          $sub->where('proforma_status', Transaction::FACTURADA)
            ->whereIn('contact_id', $customersId); // ✅ si es colección
        });
      });

    // Condiciones según el rol del usuario
    $allowedRoles = User::ROLES_ALL_DEPARTMENTS;
    if (in_array(Session::get('current_role_name'), $allowedRoles)) {
    } else {
      // Obtener departamentos y bancos de la sesión
      $departments = Session::get('current_department', []);
      $banks = Session::get('current_banks', []);

      // Filtrar por departamento y banco
      if (!empty($departments)) {
        $query->whereIn('transactions.department_id', $departments);
      }

      if (!empty($banks)) {
        $query->whereIn('transactions.bank_id', $banks);
      }
    }
    return $query;
  }

  public function render()
  {
    $query = $this->getFilteredQuery();
    Log::warning("Se ejecuta el render", [
      '$refreshCounter' => $this->refreshCounter
    ]);

    $records = [];

    // Ordenamiento y paginación final
    $records = $query
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.transactions.calculo-registro-datatable', [
      'records' => $records,
    ]);
  }

  public function store()
  {
    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    // Eliminar comas del número en el servidor
    //$this->proforma_change_type = str_replace(',', '', $this->proforma_change_type);

    // Validar
    $validatedData = $this->validate();

    $validatedData['created_by'] = Auth::user()->id;

    // Generar consecutivo
    $consecutive = DocumentSequenceService::generateConsecutive(
      $validatedData['document_type'],
      $validatedData['location_id'] ?? null
    );

    $this->proforma_no = $consecutive;
    $validatedData['proforma_no'] = $consecutive;

    // Validar nuevamente para asegurar que el campo correcto esté presente
    $this->validate([
      'proforma_no' => 'required|string|max:20',
    ]);

    $this->totalPagado = collect($this->payments)->sum(fn($p) => floatval($p['total_medio_pago']));

    try {
      // Iniciar la transacción
      DB::beginTransaction();

      // Determinar estado de pago
      if ($this->totalPagado <= 0) {
        $this->payment_status = 'due';
      } elseif ($this->pendientePorPagar == 0) {
        $this->payment_status = 'paid';
      } else {
        $this->payment_status = 'partial';
      }

      // Crear la transacción
      $transaction = Transaction::create($validatedData);

      foreach ($this->payments as $pago) {
        $transaction->payments()->create($pago);
      }

      $closeForm = $this->closeForm;

      if ($transaction) {
        // Commit: Confirmar todos los cambios
        DB::commit();
      }

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($transaction->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {
      // Rollback: Revertir los cambios en caso de error
      DB::rollBack();
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Transaction::with(['location', 'currency', 'caso', 'bank', 'codigoContable', 'createdBy', 'lines'])
      ->where('id', $recordId)
      ->first();

    $this->recordId = $recordId;
    //$this->transaction = $record;
    $this->invoice = $record;

    $this->lines = $record->lines->map(function ($line) {
      $line->registro_cantidad = empty($line->registro_cantidad) ? 1 : $line->registro_cantidad;
      $monto_escritura_colones = $line->getMontoEscrituraColones($this->invoice->currency_id, $this->invoice->proforma_change_type);
      //dd($monto_escritura_colones);
      $moneda = $this->invoice->currency_id == Currency::COLONES ? 'COLONES' : 'DOLARES';
      if (!$line->product->enable_registration_calculation) {
        $registro_monto_escritura = (float)$line->getMontoOriginalValorEscritura($moneda, $this->invoice->proforma_change_type);
      } else
        $registro_monto_escritura = (float)$line->registro_monto_escritura;

      $estado_escritura = "PENDIENTE";
      if (!is_null($line->fecha_reporte_gasto) && !empty($line->fecha_reporte_gasto))
        $estado_escritura = "PAGADO";


      return [
        'id' => $line->id,
        'registro_currency_id' => $line->registro_currency_id,
        'registro_change_type' => Helpers::formatDecimal($line->registro_change_type, 2),
        'registro_monto_escritura' => Helpers::formatDecimal($registro_monto_escritura, 2),
        'registro_cantidad' => empty($line->registro_cantidad) ? 1 : $line->registro_cantidad,
        'registro_valor_fiscal' => Helpers::formatDecimal($line->registro_valor_fiscal, 2),
        'numero_pago_registro' => $line->numero_pago_registro,
        'fecha_pago_registro' => $line->fecha_pago_registro ? Carbon::parse($line->fecha_pago_registro)->format('Y-m-d') : null,
        'fecha_reporte_gasto' => $line->fecha_reporte_gasto ? Carbon::parse($line->fecha_reporte_gasto)->format('Y-m-d') : null,
        'monto_escritura_colones' => $monto_escritura_colones,
        'monto_escritura_colones_grid' => $line->monto_escritura_colones_grid,
        'monto_timbre_escritura' => $line->getMontoTimbreEscritura($this->invoice),
        'monto_eddi' => $line->getMontoEddi(),
        'monto_estado_registro' => $line->getEstadoRegistro(),
        'estado_escritura' => $estado_escritura,
        'enable_registration_calculation' => $line->product->enable_registration_calculation,
        'monto_registro_cobrar' => $line->getMontoRegistroCobrar(),
        'impuesto_and_timbres_separados' => $line->product->impuesto_and_timbres_separados,
        'detail' => $line->detail,
        'price' => $line->price,
        'timbres' => $line->timbres,
        'quantity' => $line->quantity,
        'enable_quantity' => $line->product->enable_quantity,
        'calculo_registro_normal' => $line->calculo_registro_normal,
        'calculo_registro_iva' => $line->calculo_registro_iva,
        'calculo_registro_no_iva' => $line->calculo_registro_no_iva,
      ];
    })->toArray();

    $this->business_id            = $record->business_id;
    $this->location_id            = $record->location_id;
    $this->location_economic_activity_id = $record->location_economic_activity_id;
    $this->contact_id             = $record->contact_id;
    $this->contact_economic_activity_id = $record->contact_economic_activity_id;
    $this->currency_id            = $record->currency_id;
    $this->department_id          = $record->department_id;
    $this->area_id                = $record->area_id;
    $this->bank_id                = $record->bank_id;
    $this->caso_id                = $record->caso_id;
    $this->codigo_contable_id     = $record->codigo_contable_id;
    $this->created_by             = $record->created_by;
    $this->document_type          = $record->document_type;
    $this->proforma_type          = $record->proforma_type;
    $this->proforma_status        = $record->proforma_status;
    $this->status                 = $record->status;
    $this->payment_status         = $record->payment_status;
    $this->pay_term_type          = $record->pay_term_type;
    $this->customer_name          = $record->customer_name;
    $this->customer_comercial_name = $record->customer_comercial_name;
    $this->customer_email         = $record->customer_email;
    $this->email_cc               = $record->email_cc;
    $this->proforma_no            = $record->proforma_no;
    $this->consecutivo            = $record->consecutivo;
    $this->key                    = $record->key;
    $this->access_token           = $record->access_token;
    $this->response_xml           = $record->response_xml;
    $this->filexml                = $record->filexml;
    $this->filepdf                = $record->filepdf;
    $this->transaction_reference  = $record->transaction_reference;
    $this->transaction_reference_id = $record->transaction_reference_id;
    $this->condition_sale         = $record->condition_sale;
    $this->condition_sale_other   = $record->condition_sale_other;
    $this->numero_deposito_pago   = $record->numero_deposito_pago;
    $this->numero_traslado_honorario = $record->numero_traslado_honorario;
    $this->numero_traslado_gasto  = $record->numero_traslado_gasto;
    $this->contacto_banco         = $record->contacto_banco;
    $this->pay_term_number        = $record->pay_term_number;
    $this->proforma_change_type   = Helpers::formatDecimal($record->proforma_change_type);
    //$this->proforma_change_type   = $record->proforma_change_type;
    $this->factura_change_type    = $record->factura_change_type;
    $this->num_request_hacienda_set = $record->num_request_hacienda_set;
    $this->num_request_hacienda_get = $record->num_request_hacienda_get;
    $this->comision_pagada        = $record->comision_pagada;
    $this->is_retencion           = $record->is_retencion;
    $this->message                = $record->message;
    $this->notes                  = $record->notes;
    $this->migo                   = $record->migo;
    $this->detalle_adicional      = $record->detalle_adicional;
    $this->gln                    = $record->gln;
    $this->transaction_date       = Carbon::parse($record->transaction_date)->format('Y-m-d');
    $this->fecha_pago             = $record->fecha_pago;
    $this->fecha_deposito_pago    = $record->fecha_deposito_pago;
    $this->fecha_traslado_honorario = $record->fecha_traslado_honorario;
    $this->fecha_traslado_gasto   = $record->fecha_traslado_gasto;
    $this->fecha_solicitud_factura = $record->fecha_solicitud_factura;

    // Totales
    $this->totalHonorarios = $record->totalHonorarios;
    $this->totalTimbres = $record->totalTimbres;
    $this->totalAditionalCharge = $record->totalAditionalCharge;

    $this->totalServGravados = $record->totalServGravados;
    $this->totalServExentos = $record->totalServExentos;
    $this->totalServExonerado = $record->totalServExonerado;
    $this->totalServNoSujeto = $record->totalServNoSujeto;

    $this->totalMercGravadas = $record->totalMercGravadas;
    $this->totalMercExentas = $record->totalMercExentas;
    $this->totalMercExonerada = $record->totalMercExonerada;
    $this->totalMercNoSujeta = $record->totalMercNoSujeta;

    $this->totalGravado = $record->totalGravado;
    $this->totalExento = $record->totalExento;
    $this->totalExonerado = $record->totalExonerado;
    $this->totalNoSujeto = $record->totalExonerado;

    $this->totalVenta = $record->totalVenta;
    $this->totalDiscount = $record->totalDiscount;
    $this->totalVentaNeta = $record->totalVentaNeta;
    $this->totalTax = $record->totalTax;
    $this->totalImpAsumEmisorFabrica = $record->totalImpAsumEmisorFabrica;
    $this->totalIVADevuelto = $record->totalIVADevuelto;
    $this->totalOtrosCargos = $record->totalOtrosCargos;
    $this->totalComprobante = $record->totalComprobante;

    $this->original_currency_id = $record->currency_id;

    $this->bank_name = $record->bank?->name;
    $this->currency_code = $record->currency?->code;
    $this->issuer_name = $record->location?->name;
    $this->codigo_contable_descrip = $record->codigoContable?->descrip;
    $this->user_name = $record->createdBy?->name;

    // Se emite este evento para los componentes hijos

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';

    if ($record->caso) {
      if (!is_null($record->caso->numero_gestion) && !empty($record->caso->numero_gestion))
        $text = $record->caso->numero . ' - ' . $record->caso->numero_gestion . ' - ' . $record->caso->deudor;
      else
        $text = $record->caso->numero . ' - ' . $record->caso->deudor;
      $this->infoCaso = $text;
      //$this->dispatch('setSelect2Value', id: 'caso_id', value: $this->caso_id, text: $text);
    }
  }

  protected function rules()
  {
    return [
      'lines' => 'required|array',
      'lines.*.id' => 'required|exists:transactions_lines,id',
      'lines.*.registro_currency_id' => 'nullable|exists:currencies,id',
      'lines.*.registro_change_type' => 'nullable|numeric|min:0',
      'lines.*.registro_monto_escritura' => 'nullable|numeric|min:0',
      'lines.*.registro_cantidad' => 'nullable|integer|min:1',
      'lines.*.registro_valor_fiscal' => 'nullable|numeric|min:0',
      'lines.*.numero_pago_registro' => 'nullable|string|max:100',
      'lines.*.fecha_pago_registro' => 'nullable|date',
    ];
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'lines.*.registro_currency_id.required' => 'Debe seleccionar una moneda para el registro.',
      'lines.*.registro_currency_id.exists' => 'La moneda seleccionada no es válida.',

      'lines.*.registro_change_type.required' => 'Debe indicar el tipo de cambio del registro.',
      'lines.*.registro_change_type.numeric' => 'El tipo de cambio debe ser un número válido.',
      'lines.*.registro_change_type.min' => 'El tipo de cambio debe ser mayor a cero.',

      'lines.*.registro_monto_escritura.required' => 'Debe indicar el monto original de la escritura.',
      'lines.*.registro_monto_escritura.numeric' => 'El monto de la escritura debe ser numérico.',

      'lines.*.registro_cantidad.required' => 'Debe indicar la cantidad de escrituras.',
      'lines.*.registro_cantidad.integer' => 'La cantidad debe ser un número entero.',
      'lines.*.registro_cantidad.min' => 'La cantidad debe ser al menos 1.',

      'lines.*.registro_valor_fiscal.required' => 'Debe indicar el valor fiscal.',
      'lines.*.registro_valor_fiscal.numeric' => 'El valor fiscal debe ser numérico.',

      'lines.*.numero_pago_registro.numeric' => 'El número de cheque debe ser un número válido.',
      'lines.*.numero_pago_registro.nullable' => 'El número de cheque es opcional.',

      'lines.*.fecha_pago_registro.date' => 'La fecha de pago debe tener un formato válido.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    return [
      'lines.*.registro_currency_id' => 'moneda del registro',
      'lines.*.registro_change_type' => 'tipo de cambio del registro',
      'lines.*.registro_monto_escritura' => 'monto de la escritura',
      'lines.*.registro_cantidad' => 'cantidad de escrituras',
      'lines.*.registro_valor_fiscal' => 'valor fiscal',
      'lines.*.numero_pago_registro' => 'número de cheque',
      'lines.*.fecha_pago_registro' => 'fecha de pago del registro',
    ];
  }

  public function update()
  {
    $recordId = $this->recordId;

    foreach ($this->lines as $i => $line) {
      if (isset($line['registro_change_type'])) {
        $this->lines[$i]['registro_change_type'] = floatval(str_replace(',', '', $line['registro_change_type']));
        $this->lines[$i]['registro_monto_escritura'] = floatval(str_replace(',', '', $line['registro_monto_escritura']));
        $this->lines[$i]['registro_valor_fiscal'] = floatval(str_replace(',', '', $line['registro_valor_fiscal']));
      }
    }

    // Validar
    $this->validate();

    try {
      // Encuentra el registro existente
      $record = Transaction::findOrFail($recordId);


      DB::beginTransaction();

      foreach ($this->lines as $line) {
        $lineModel = TransactionLine::find($line['id']);
        if ($lineModel) {
          $lineModel->update([
            'registro_currency_id' => !empty($line['registro_currency_id']) ? $line['registro_currency_id'] : NULL,
            'registro_change_type' => $line['registro_change_type'],
            'registro_monto_escritura' => $line['registro_monto_escritura'],
            'registro_cantidad' => $line['registro_cantidad'] ?? 1,
            'registro_valor_fiscal' => $line['registro_valor_fiscal'],
            'fecha_pago_registro' => !empty($line['fecha_pago_registro']) ? $line['fecha_pago_registro'] : NULL,
            'numero_pago_registro' => $line['numero_pago_registro'],
          ]);
        }
      }

      $record->message = $this->message;
      $record->save();

      DB::commit();

      $closeForm = $this->closeForm;

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();
      $this->dispatch('scroll-to-top');

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);

      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }
    } catch (\Exception $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }
  }

  #[On('solicitarFacturacion')]
  public function solicitarFacturacion($recordId)
  {
    try {
      DB::transaction(function () use ($recordId) {
        $record = Transaction::findOrFail($recordId);

        $msgs = Helpers::validateProformaToRequestInvoice($record);
        if (!empty($msgs)) {
          $this->dispatch('show-notification', [
            'type' => 'warning',
            'message' => implode('<br>', $msgs),
          ]);
        } else {
          $record->proforma_status = Transaction::SOLICITADA;
          $record->fecha_solicitud_factura = \Carbon\Carbon::now();

          if ($record->save()) {
            $this->dispatch('show-notification', [
              'type' => 'success',
              'message' => __('Billing request was successfully completed'),
            ]);
          } else
            $this->dispatch('show-notification', [
              'type' => 'error',
              'message' => __('An error occurred and the request could not be made'),
            ]);
        }
      });
    } catch (QueryException $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage(),
      ]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage(),
      ]);
    }
  }

  #[On('facturar')]
  public function facturar($recordId)
  {
    $record = Transaction::findOrFail($recordId);

    // Validación por tipo
    if ($record->proforma_type === 'HONORARIO') {
      $msgs = Helpers::validateProformaToConvertInvoice($record);
    } elseif ($record->proforma_type === 'GASTO') {
      $msgs = Helpers::validateProformaToConvertInvoice($record); // puedes usar otro helper si difieren
    } else {
      $this->dispatch('show-notification', [
        'type' => 'warning',
        'message' => __('Unknown proforma type'),
      ]);
      return;
    }

    if ($record->proforma_status === Transaction::FACTURADA) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('The proforma is already invoiced'),
      ]);
      return;
    }

    // Validación con mensajes
    if (!empty($msgs)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => implode('<br>', $msgs),
      ]);
      return;
    }

    // Lógica transaccional
    DB::beginTransaction();  // Comienza la transacción principal

    try {
      // Llamar a las funciones correspondientes basadas en el tipo
      if ($record->proforma_type === 'HONORARIO') {
        $this->facturarHonorario($record);
      } elseif ($record->proforma_type === 'GASTO') {
        $this->facturarGasto($record);
      }

      DB::commit();  // Commit de la transacción principal

      // Después del commit se envian los emails para evitar que si falla el envio de email la acción no se realice
      if ($record->proforma_type === 'GASTO') {
        //Enviar email
        $this->afterFacturarGasto($record);
        // Si todo fue exitoso, mostrar notificación de éxito
        $this->dispatch('show-notification', [
          'type' => 'success',
          'message' => __('Invoicing has been successfully completed and is ready to be sent to the tax authorities')
        ]);
      } else {
        // Enviar email
        // esto se hace en el callback
        //$this->afterFacturarHonorario($record);
      }
    } catch (\Throwable $e) {
      DB::rollBack();  // Si ocurre un error, hacer rollback de la transacción

      // Enviar notificación de error
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An unexpected error occurred:') . ' ' . $e->getMessage()
      ]);

      // Registrar el error en el log
      logger()->error('Error en facturar:' . ' ' . $e->getMessage(), ['exception' => $e]);
    }
  }

  private function facturarHonorario($transaction)
  {
    /*
    - Asignar el document_type a FE !importante para generar la key y el consecutivo
    - Obtener la key y el consecutivo del Documento
    - Obetener el xml del documento
    - Firmar el documento
    - Loguearme para obtener el token
    - Enviar hacienda y recibir la respuesta
    - Cambiar el estado de la factura según la respuesta de hacienda campo status
    - Obtener el tipo de cambio y asignarlo a factura_change_type
    */

    // En este caso, no necesitamos iniciar una nueva transacción aquí
    // Simplemente hacer la lógica y dejar que la transacción principal controle todo

    // Asignar el tipo de documento
    $transaction->document_type = Transaction::FACTURAELECTRONICA;

    // Obtener la secuencia que le corresponde según tipo de comprobante
    $secuencia = DocumentSequenceService::generateConsecutive(
      $transaction->document_type,
      $transaction->location_id
    );

    // Asignar el consecutivo a la transacción
    $transaction->consecutivo = $transaction->getConsecutivo($secuencia);
    $transaction->key = $transaction->generateKey();  // Generar la clave del documento

    // Obtener el xml firmado y en base64
    $encode = true;
    $xml = Helpers::generateComprobanteElectronicoXML($transaction, $encode, 'file');

    //Loguearme en hacienda para obtener el token
    $username = $transaction->location->api_user_hacienda;
    $password = $transaction->location->api_password;
    try {
      $authService = new AuthService();
      $token = $authService->getToken($username, $password);
    } catch (\Exception $e) {
      throw new \Exception("An error occurred when trying to obtain the token in the hacienda api" . ' ' . $e->getMessage());
    }

    $api = new ApiHacienda();
    $result = $api->send($xml, $token, $transaction, $transaction->location, Transaction::FACTURAELECTRONICA);
    if ($result['error'] == 0) {
      $transaction->status = Transaction::RECIBIDA;
      $transaction->invoice_date = \Carbon\Carbon::now();
      $transaction->factura_change_type = Session::get('exchange_rate');
    } else {
      throw new \Exception($result['mensaje']);
    }

    // Guardar la transacción
    if (!$transaction->save()) {
      throw new \Exception(__('An error occurred while saving the transaction'));
    } else {
      // Si todo fue exitoso, mostrar notificación de éxito
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => $result['mensaje'],
      ]);
    }
  }

  private function facturarGasto($transaction)
  {
    $consecutive = DocumentSequenceService::generateConsecutiveGasto($transaction->document_type, null);

    if (!$consecutive) {
      throw new \Exception(__('An error occurred while generating the invoice consecutive number'));
    }

    $transaction->consecutivo = $consecutive;
    $transaction->proforma_status = Transaction::FACTURADA;
    $transaction->invoice_date = \Carbon\Carbon::now();

    if (!$transaction->save()) {
      throw new \Exception(__('An error occurred while creating the expense invoice'));
    }
  }

  private function afterFacturarGasto($transaction)
  {
    $sent = Helpers::sendReciboGastoEmail($transaction);

    if ($sent) {
      $transaction->fecha_envio_email = now();
      $transaction->save();

      $menssage = __('An email has been sent to the following addresses:') . ' ' . $transaction->contact->email;
      if (!empty($transaction->email_cc)) {
        $menssage .= ' ' . __('with copy to') . ' ' . $transaction->email_cc;
      }

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The expense invoice has been successfully issued') . ' ' . $menssage
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The expense invoice has been successfully issued')
      ]);

      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }

  public function resetControls()
  {
    $this->reset(
      //'business_id',
      'location_id',
      'location_economic_activity_id',
      'contact_id',
      'contact_economic_activity_id',
      'currency_id',
      'department_id',
      'area_id',
      'bank_id',
      'codigo_contable_id',
      'created_by',
      'proforma_type',
      'proforma_status',
      'status',
      'payment_status',
      'pay_term_type',
      'customer_name',
      'customer_comercial_name',
      'customer_email',
      'proforma_no',
      'consecutivo',
      'key',
      'access_token',
      'response_xml',
      'filexml',
      'filepdf',
      'transaction_reference',
      'transaction_reference_id',
      'condition_sale',
      'condition_sale_other',
      'numero_deposito_pago',
      'numero_traslado_honorario',
      'numero_traslado_gasto',
      'contacto_banco',
      'pay_term_number',
      'proforma_change_type',
      'factura_change_type',
      'num_request_hacienda_set',
      'num_request_hacienda_get',
      'comision_pagada',
      'is_retencion',
      'message',
      'notes',
      'migo',
      'detalle_adicional',
      'gln',
      'transaction_date',
      'fecha_pago',
      'fecha_deposito_pago',
      'fecha_traslado_honorario',
      'fecha_traslado_gasto',
      'fecha_solicitud_factura',
      'activeTab',
      'closeForm',
      'payments',
      'vuelto',
      'infoCaso'
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function updated($propertyName)
  {
    // Si el campo condition_sale cambia
    if ($propertyName == 'condition_sale') {
      if ($this->condition_sale !== '02') {
        // Limpiar el valor de pay_term_number
        $this->pay_term_number = null;
      }
      if ($this->condition_sale !== '99') {
        $this->condition_sale_other = null;
      }
    }

    if ($propertyName == 'department_id') {
      // emitir el evento para que actualice la info en las lineas
      $this->dispatch('departmentChange', $this->department_id); // Enviar evento al frontend
    }

    if ($propertyName == 'bank_id') {
      // emitir el evento para que actualice la info en las lineas
      $this->dispatch('bankChange', $this->bank_id); // Enviar evento al frontend
    }

    if ($propertyName == 'email_cc') {
      $this->updatedEmails();
    }

    if ($propertyName == 'bank_id') {
      $this->setEnableControl();
    }

    if ($propertyName == 'location_id') {
      if ($this->location_id == '' | is_null($this->location_id))
        $this->location_economic_activity_id = null;
    }

    if ($propertyName == 'contact_id') {
      if ($this->contact_id == '' | is_null($this->contact_id))
        $this->contact_economic_activity_id = null;
    }

    $this->dispatch('updateExportFilters', [
      'search' => $this->search,
      'filters' => $this->filters,
      'selectedIds' => $this->selectedIds,
    ]);

    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
  }

  public function updatedCurrencyId($value)
  {
    if ($value != $this->original_currency_id) {
      // Si la moneda cambia hay que recalcular todo
      $transacion = Transaction::find($this->recordId);
      if ($transacion) {
        $transacion->currency_id = $this->currency_id;
        $transacion->save();
        $this->original_currency_id = $this->currency_id;

        $lines = TransactionLine::where('transaction_id', $this->recordId)->get();
        foreach ($lines as $line) {
          $line->updateTransactionTotals($this->currency_id);
        }
      }
      $activeTabProduct = false;

      $this->dispatch('productUpdated', $this->recordId, $activeTabProduct);  // Emitir evento para otros componentes
    }
  }

  public function updatedEmails()
  {
    // Divide la cadena en correos separados por , o ;
    $emailList = preg_split('/[,;]+/', $this->email_cc);

    // Resetear las listas de correos válidos e inválidos
    $this->validatedEmails = [];
    $this->invalidEmails = [];

    // Validar cada correo
    foreach ($emailList as $email) {
      $email = trim($email); // Elimina espacios en blanco
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->validatedEmails[] = $email; // Correo válido
      } elseif (!empty($email)) {
        $this->invalidEmails[] = $email; // Correo inválido
      }
    }

    // Si hay correos inválidos, añadir error al campo email_cc
    if (!empty($this->invalidEmails)) {
      $this->addError('email_cc', 'Hay correos inválidos: ' . implode(', ', $this->invalidEmails));
    } else {
      $this->resetErrorBag('email_cc'); // Limpiar errores si todos son válidos
    }
  }

  public function setEnableControl()
  {
    $this->enableoc = false;
    $this->enablemigo = false;
    $this->enableor = false;
    $this->enablegln = false;
    $this->enableprebill = false;

    if ($this->bank_id == Bank::SANJOSE) {
      $this->enableoc = true;
      $this->enablemigo = true;

      $this->or = '';
      $this->gln = '';
      $this->prebill = '';
    } else
    if ($this->bank_id == Bank::TERCEROS) {
      $this->enableoc = true;
      $this->enablemigo = true;
      $this->enableor = true;
      $this->enablegln = true;
      $this->enableprebill = true;
    } else {
      $this->oc = '';
      $this->migo = '';
      $this->or = '';
      $this->gln = '';
      $this->prebill = '';
    }
  }

  public function toggleSelectAllNormal(): void
  {
    $this->selectAllNormal = !$this->selectAllNormal;

    if ($this->selectAllNormal) {
      $ids = collect($this->invoice->lines)->pluck('id')->toArray();
      $this->setExclusiveSelection('normal', $ids);
    } else {
      $this->selectedNormal = [];
    }
  }

  public function toggleSelectAllIva(): void
  {
    $this->selectAllIva = !$this->selectAllIva;

    if ($this->selectAllIva) {
      $ids = collect($this->invoice->lines)
        ->filter(fn($line) => $line->product->impuesto_and_timbres_separados)
        ->pluck('id')->toArray();

      $this->setExclusiveSelection('iva', $ids);
    } else {
      $this->selectedIva = [];
    }
  }

  public function toggleSelectAllNoIva(): void
  {
    $this->selectAllNoIva = !$this->selectAllNoIva;

    if ($this->selectAllNoIva) {
      $ids = collect($this->invoice->lines)
        ->filter(fn($line) => $line->product->impuesto_and_timbres_separados)
        ->pluck('id')->toArray();

      $this->setExclusiveSelection('noiva', $ids);
    } else {
      $this->selectedNoIva = [];
    }
  }

  protected function setExclusiveSelection(string $target, array $ids): void
  {
    $this->selectedNormal = $target === 'normal' ? $ids : array_diff($this->selectedNormal, $ids);
    $this->selectedIva    = $target === 'iva'    ? $ids : array_diff($this->selectedIva, $ids);
    $this->selectedNoIva  = $target === 'noiva'  ? $ids : array_diff($this->selectedNoIva, $ids);
  }

  public function toggleLineCheckbox($lineId, $type)
  {
    if ($type === 'normal') {
      $this->selectedNormal[] = $lineId;
      $this->selectedIva = array_diff($this->selectedIva, [$lineId]);
      $this->selectedNoIva = array_diff($this->selectedNoIva, [$lineId]);
    }

    if ($type === 'iva') {
      $this->selectedIva[] = $lineId;
      $this->selectedNormal = array_diff($this->selectedNormal, [$lineId]);
      $this->selectedNoIva = array_diff($this->selectedNoIva, [$lineId]);
    }

    if ($type === 'noiva') {
      $this->selectedNoIva[] = $lineId;
      $this->selectedNormal = array_diff($this->selectedNormal, [$lineId]);
      $this->selectedIva = array_diff($this->selectedIva, [$lineId]);
    }

    // Eliminar duplicados
    $this->selectedNormal = array_unique($this->selectedNormal);
    $this->selectedIva = array_unique($this->selectedIva);
    $this->selectedNoIva = array_unique($this->selectedNoIva);
  }

  public function downloadCalculoReciboDeGastos($invoiceId)
  {
    Log::warning("Se llama a downloadCalculoReciboDeGastos");


    // Obrtener todos los ids seleccionados
    $ids_normal = $this->selectedNormal;

    $ids_iva = $this->selectedIva;

    $ids_no_iva = $this->selectedNoIva;

    $ids = array_merge($ids_normal, $ids_iva, $ids_no_iva);

    if (empty($ids)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Debe seleccionar al menos un elemento')
      ]);

      return false;
    }

    $this->prepareExportCalculoReciboGasto($invoiceId, $ids, $ids_normal, $ids_iva, $ids_no_iva);
  }

  private function prepareExportCalculoReciboGasto($invoiceId, $ids, $ids_normal, $ids_iva, $ids_no_iva)
  {
    Log::warning("datos pasados a preparar exportación", [
      '$invoiceId' => $invoiceId,
      '$ids' => $ids,
      '$ids_normal' => $ids_normal,
      '$ids_iva' => $ids_iva,
      '$ids_no_iva' => $ids_no_iva,
    ]);

    $key = uniqid('export_', true);

    if (empty($invoiceId) || !is_numeric($invoiceId)) {
      Log::warning("ID inválido al preparar exportación", ['invoiceId' => $invoiceId]);
      return;
    }

    cache()->put($key, [
      'invoiceId' => $invoiceId,
      'ids' => $ids,
      'ids_normal' => $ids_normal,
      'ids_iva' => $ids_iva,
      'ids_no_iva' => $ids_no_iva,
    ], now()->addMinutes(5));

    $url = route('exportacion.proforma.calculo.recibo.gasto.preparar', ['key' => $key]);
    $downloadBase = '/descargar-exportacion-calculo-recibo-gasto';

    Log::info('Reporte', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);

    $this->dispatch('exportReady', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);
  }

  public function back()
  {
    $this->resetControls();
    $this->action = 'list';
  }

  #[On('refresh-grid')]
  public function refreshGrid()
  {
    $this->edit($this->recordId);

    // 🔄 Refrescar key de las filas
    $this->refreshCounter++;

    $this->selectedNormal = [];
    $this->selectedIva = [];
    $this->selectedNoIva = [];

    $this->selectAllNormal = false;
    $this->selectAllIva = false;
    $this->selectAllNoIva = false;

    $this->dispatch('show-notification', [
      'type' => 'success',
      'message' => __('Se ha generado el reporte satisfactoriamente')
    ]);
  }
}
