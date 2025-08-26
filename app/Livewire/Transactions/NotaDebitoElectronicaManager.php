<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\EconomicActivity;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\DocumentSequenceService;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NotaDebitoElectronicaManager extends TransactionManager
{
  public $filters = [
    'filter_consecutivo' => NULL,
    'filter_customer_name' => NULL,
    'filter_department_name' => NULL,
    'filter_user_name' => NULL,
    'filter_transaction_date' => NULL,
    'filter_issuer_name' => NULL,
    'filter_numero_caso' => NULL,
    'filter_referencia' => NULL,
    'filter_oc' => NULL,
    'filter_migo' => NULL,
    'filter_bank_name' => NULL,
    'filter_currency_code' => NULL,
    'filter_status' => NULL,
    'filter_totalComprobante' => NULL,
    'filter_total_usd' => NULL,
    'filter_total_crc' => NULL,
    'filter_action' => NULL,
  ];

  public function mount()
  {
    parent::mount();
    // Aquí puedes agregar lógica específica para proformas

    // Verificar si hay parámetros en la URL
    $params = request()->query();

    if (!empty($params)) {
      if (isset($params['id']) && isset($params['action'])) {
        if ($params['action'] === 'edit') {
          $this->edit($params['id']);
        }
      }
    }
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'debit-note-datatable')
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
        'label' => __('Consecutive'),
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
        'field' => 'department_name',
        'orderName' => 'departments.name',
        'label' => __('Department'),
        'filter' => 'filter_department_name',
        'filter_type' => 'select',
        'filter_sources' => 'departments',
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
        'field' => 'numero_caso',
        'orderName' => '',
        'label' => __('Case Number'),
        'filter' => 'filter_numero_caso',
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
        'field' => 'referencia',
        'orderName' => '',
        'label' => __('Case/Reference'),
        'filter' => 'filter_referencia',
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
        'field' => 'oc',
        'orderName' => 'oc',
        'label' => __('O.C'),
        'filter' => 'filter_oc',
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
        'field' => 'migo',
        'orderName' => 'migo',
        'label' => __('MIGO'),
        'filter' => 'filter_migo',
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
        'field' => 'total_usd',
        'orderName' => '',
        'label' => __('Total USD'),
        'filter' => 'filter_total_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getTotalComprobante',
        'parameters' => ['USD', true],
        'sumary' => 'tComprobanteUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'total_crc',
        'orderName' => '',
        'label' => __('Total CRC'),
        'filter' => 'filter_total_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getTotalComprobante',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tComprobanteCrc',
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
        'function' => 'getElectronicDebitNoteHtmlColumnAction',
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

  protected function afterTransactionSaved()
  {
    // Lógica específica tras guardar una proforma
    // Ejemplo: generar PDF, enviar notificación, etc.
  }

  protected function getFilteredQuery()
  {
    $query = Transaction::search($this->search, $this->filters)
      ->where('document_type', $this->document_type);

    // Condiciones según el rol del usuario
    $allowedRoles = User::ROLES_ALL_DEPARTMENTS;
    if (in_array(Session::get('current_role_name'), $allowedRoles)) {
      $query->where(function ($q) {
        $q->whereIn('status', [Transaction::PENDIENTE, Transaction::RECIBIDA, Transaction::ACEPTADA, Transaction::RECHAZADA, Transaction::ANULADA]);
      });
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

      $query->whereIn('status', [Transaction::PENDIENTE, Transaction::RECIBIDA, Transaction::ACEPTADA, Transaction::RECHAZADA, Transaction::ANULADA]);
    }

    return $query;
  }

  public function render()
  {
    $query = $this->getFilteredQuery();

    // Ordenamiento y paginación final
    $records = $query
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    $stats = $this->getStatics();

    $this->totalProceso = 0;
    $this->totalPorAprobar = 0;
    $this->totalUsdHonorario = 0;
    $this->totalCrcHonorario = 0;
    $this->totalUsdGasto = 0;
    $this->totalCrcGasto = 0;

    if ($stats) {
      $this->totalProceso = $stats->total_facturas_proceso ?? 0;
      $this->totalPorAprobar = $stats->facturas_por_aprobar ?? 0;
      $this->totalUsdHonorario = $stats->totalUsdHonorario ?? 0;
      $this->totalCrcHonorario = $stats->totalCrcHonorario ?? 0;
      $this->totalUsdGasto = $stats->totalUsdGasto ?? 0;
      $this->totalCrcGasto = $stats->totalCrcGasto ?? 0;
    }

    return view('livewire.transactions.debit-note-datatable', [
      'records' => $records,
    ]);
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    $rules = [
      // Foreign Keys
      'business_id'           => 'required|integer|exists:business,id',
      'location_id'           => 'nullable|integer|exists:business_locations,id',
      'location_economic_activity_id'  => 'nullable|integer|exists:economic_activities,id',
      'cuenta_id'             => 'nullable|integer|exists:cuentas,id',
      'contact_id'            => 'required|integer|exists:contacts,id',
      'contact_economic_activity_id' => 'nullable|integer|exists:economic_activities,id',
      'currency_id'           => 'required|integer|exists:currencies,id',
      'department_id'         => 'required|integer|exists:departments,id',
      'area_id'               => 'nullable|integer|exists:areas,id',
      'bank_id'               => 'nullable|integer|exists:banks,id',
      'codigo_contable_id'    => 'nullable|integer|exists:codigo_contables,id',
      'caso_id'               => 'nullable|integer|exists:casos,id',
      //'created_by'          => 'required|integer|exists:users,id',

      // Enums
      'document_type'         => 'required|in:NDE',
      'proforma_type'         => 'required|in:HONORARIO,GASTO',
      'proforma_status'       => 'nullable|in:PROCESO,SOLICITADA,FACTURADA,RECHAZADA,ANULADA',
      'status'                => 'nullable|in:PENDIENTE,RECIBIDA,ACEPTADA,RECHAZADA,ANULADA',
      'showInstruccionesPago' => 'nullable|in:NACIONAL,INTERNACIONAL,AMBAS',
      //'payment_status'        => 'nullable|in:paid,due,partial',
      'pay_term_type'         => 'nullable|in:days,months',

      // Strings
      'customer_name'         => 'required|string|max:150',
      'customer_comercial_name' => 'nullable|string|max:150',
      'customer_email'        => 'nullable|email|max:150',
      'email_cc'              => 'nullable|string',
      'nombre_caso'           => 'nullable|string|max:191',

      //'proforma_no'           => 'nullable|string|max:20',
      //'consecutivo'           => 'nullable|string|max:20',
      //'key'                   => 'nullable|string|max:50',
      //'access_token'          => 'nullable|string|max:191',
      //'response_xml'          => 'nullable|string|max:191',
      //'filexml'               => 'nullable|string|max:191',
      //'filepdf'               => 'nullable|string|max:191',
      //'transaction_reference' => 'nullable|string|max:50',
      //'transaction_reference_id' => 'nullable|string|max:50',
      'condition_sale' => 'required|string|in:01,02,03,04,05,06,06,08,09,10,11,12,13,14,15,99|max:2',
      'condition_sale_other' => 'nullable|required_if:condition_sale,99|max:100|string',
      //'numero_deposito_pago'  => 'nullable|string|max:191',
      //'numero_traslado_honorario' => 'nullable|string|max:20',
      //'numero_traslado_gasto' => 'nullable|string|max:20',
      'contacto_banco'        => 'nullable|string|max:100',

      // Numerics
      //'pay_term_number'     => 'nullable|integer|min:0',
      'pay_term_number'       => 'required_if:condition_sale,02|numeric|max:100',
      //'pay_term_number' => 'sometimes|required_if:condition_sale,02|numeric|max:100',
      'proforma_change_type'  => 'nullable|numeric|required_if:document_type,PR|min:0.1|max:999999999999999.99999',
      'factura_change_type'   => 'nullable|numeric|min:0|max:999999999999999.99999',
      //'num_request_hacienda_set' => 'nullable|integer|min:0',
      //'num_request_hacienda_get' => 'nullable|integer|min:0',
      //'comision_pagada'       => 'nullable|boolean',
      //'is_retencion'          => 'nullable|boolean',

      // Texts
      'message'               => 'nullable|string',
      'notes'                 => 'nullable|string',
      'detalle_adicional'     => 'nullable|string',
      'oc'                    => 'nullable|string',
      'migo'                  => 'nullable|string',
      'or'                    => 'nullable|string',
      'gln'                   => 'nullable|string',
      'prebill'               => 'nullable|string',

      // Dates
      'transaction_date'         => 'required|date',
      'fecha_pago'               => 'nullable|date',
      'fecha_deposito_pago'      => 'nullable|date',
      'fecha_traslado_honorario' => 'nullable|date',
      'fecha_traslado_gasto'     => 'nullable|date',
      'fecha_solicitud_factura'  => 'nullable|date',
      'fecha_envio_email'        => 'nullable|date',

      'totalHonorarios' => 'nullable|numeric|min:0',
      'totalTimbres' => 'nullable|numeric|min:0',
      'totalDiscount' => 'nullable|numeric|min:0',
      'totalTax' => 'nullable|numeric|min:0',
      'totalAditionalCharge' => 'nullable|numeric|min:0',

      'totalServGravados' => 'nullable|numeric|min:0',
      'totalServExentos' => 'nullable|numeric|min:0',
      'totalServExonerado' => 'nullable|numeric|min:0',
      'totalServNoSujeto' => 'nullable|numeric|min:0',

      'totalMercGravadas' => 'nullable|numeric|min:0',
      'totalMercExentas' => 'nullable|numeric|min:0',
      'totalMercExonerada' => 'nullable|numeric|min:0',
      'totalMercNoSujeta' => 'nullable|numeric|min:0',

      'totalGravado' => 'nullable|numeric|min:0',
      'totalExento' => 'nullable|numeric|min:0',
      'totalVenta' => 'nullable|numeric|min:0',
      'totalVentaNeta' => 'nullable|numeric|min:0',
      'totalExonerado' => 'nullable|numeric|min:0',
      'totalNoSujeto' => 'nullable|numeric|min:0',
      'totalImpAsumEmisorFabrica' => 'nullable|numeric|min:0',
      'totalIVADevuelto' => 'nullable|numeric|min:0',
      'totalOtrosCargos' => 'nullable|numeric|min:0',
      'totalComprobante' => 'nullable|numeric|min:0',

      'totalPagado' => 'nullable|numeric|min:0',
      'pendientePorPagar' => 'nullable|numeric|min:0',
      'vuelto' => 'nullable|numeric|min:0',

      'payments.*.tipo_medio_pago' => 'required|in:01,02,03,04,05,06,07,99',
      'payments.*.total_medio_pago' => 'required|numeric|min:0',
      'payments.*.medio_pago_otros' => 'nullable|string|max:255'
    ];

    return $rules;
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'required_if' => 'El campo :attribute es obligatorio cuando el tipo es :value.',
      'required_with' => 'El campo :attribute es obligatorio.',
      'numeric' => 'El campo :attribute debe ser un número válido.',
      'min' => 'El campo :attribute debe tener al menos :min caracteres.',
      'max' => 'El campo :attribute no puede exceder :max caracteres.',
      'in' => 'El campo :attribute no es válido.',
      'exists' => 'El campo :attribute no existe en el sistema.',
      'string' => 'El campo :attribute debe ser texto.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'proforma_no.required' => 'El campo proforma es obligatorio cuando el tipo de documento es PR.',
      'consecutivo.required' => 'El campo consecutivo es obligatorio para documentos que no sean proforma.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    $attributes = [
      'business_id'           => 'ID del negocio',
      'document_type'         => 'tipo de documento',
      'currency_id'           => 'moneda',
      'condition_sale'        => 'condición de venta',
      'department_id'         => 'departamento',
      'proforma_type'         => 'tipo de acto',
      'status'                => 'estado',
      'transaction_date'      => 'fecha de transacción',
      'customer_name'         => 'nombre del cliente',
      'pay_term_number'       => 'término de pago',
      'created_by'            => 'creado por',
      'location_economic_activity_id' => 'actividad económica',
      'contact_economic_activity_id'  => 'actividad económica',
    ];

    return $attributes;
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Transaction::find($recordId);
    if ($record->status != Transaction::PENDIENTE) {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('Solo las notas en estado pendiente pueden ser editadas')]);
      return;
    }

    $this->recordId = $recordId;
    //$this->transaction = $record;

    $this->business_id            = $record->business_id;
    $this->location_id            = $record->location_id;
    $this->location_economic_activity_id = $record->location_economic_activity_id;
    $this->contact_id             = $record->contact_id;
    $this->contact_economic_activity_id = $record->contact_economic_activity_id;
    $this->cuenta_id              = $record->cuenta_id;
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
    $this->nombre_caso            = $record->nombre_caso;
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
    $this->factura_change_type    = Helpers::formatDecimal($record->factura_change_type);
    $this->num_request_hacienda_set = $record->num_request_hacienda_set;
    $this->num_request_hacienda_get = $record->num_request_hacienda_get;
    $this->comision_pagada        = $record->comision_pagada;
    $this->is_retencion           = $record->is_retencion;
    $this->message                = $record->message;
    $this->notes                  = $record->notes;
    $this->migo                   = $record->migo;
    $this->detalle_adicional      = $record->detalle_adicional;
    $this->gln                    = $record->gln;
    $this->transaction_date       = $record->transaction_date;
    $this->fecha_pago             = $record->fecha_pago;
    $this->fecha_deposito_pago    = $record->fecha_deposito_pago;
    $this->fecha_traslado_honorario = $record->fecha_traslado_honorario;
    $this->fecha_traslado_gasto   = $record->fecha_traslado_gasto;
    $this->fecha_solicitud_factura = $record->fecha_solicitud_factura;
    $this->showInstruccionesPago   = $record->showInstruccionesPago;

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

    $this->show_transaction_date = Carbon::parse($record->transaction_date)->format('Y-m-d');

    //$this->show_transaction_date = Carbon::parse($record->transaction_date)->format('Y-m-d');
    $this->original_currency_id = $record->currency_id;

    // Se emite este evento para los componentes hijos
    $this->dispatch('updateTransactionContext', [
      'transaction_id'    => $record->id,
      'department_id'     => $record->department_id,
      'bank_id'           => $record->bank_id,
      'type_notarial_act' => $record->proforma_type,
    ]);

    // Almacenar en sesión Y emitir evento global
    $contextData = [
      'transaction_id'    => $record->id,
      'department_id'     => $record->department_id,
      'bank_id'           => $record->bank_id,
      'type_notarial_act' => $record->proforma_type,
    ];

    session()->forget('transaction_context');
    session()->put('transaction_context', $contextData);

    $this->payments = $record->payments->map(fn($p) => [
      'id'              => $p->id,
      'tipo_medio_pago' => $p->tipo_medio_pago,
      'medio_pago_otros' => $p->medio_pago_otros,
      'total_medio_pago' => Helpers::formatDecimal($p->total_medio_pago),
    ])->toArray();

    if (empty($this->payments))
      $this->payments = [[
        'tipo_medio_pago' => '04', // Transferencia
        'medio_pago_otros' => '',
        'total_medio_pago' => '0',
      ]];

    $this->recalcularVuelto();

    $this->setEnableControl();

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';

    $this->setlocationEconomicActivities();
    $this->setcontactEconomicActivities();

    if ($this->location_economic_activity_id) {
      $activity = EconomicActivity::find($this->location_economic_activity_id);
      if ($activity) {
        $this->dispatch('setSelect2Value', id: 'location_economic_activity_id', value: $activity->id, text: $activity->name);
      }
    }

    if ($this->contact_economic_activity_id) {
      $activity = EconomicActivity::find($this->contact_economic_activity_id);
      if ($activity) {
        $this->dispatch('setSelect2Value', id: 'contact_economic_activity_id', value: $activity->id, text: $activity->name);
      }
    }

    if ($record->caso) {
      $text = $record->caso->numero . ' - ' . $record->caso->deudor;
      $this->dispatch('setSelect2Value', id: 'caso_id', value: $this->caso_id, text: $text);
    }

    $this->setInfoCaso();
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    // Eliminar comas del número en el servidor
    //$this->proforma_change_type = str_replace(',', '', $this->proforma_change_type);
    $this->pay_term_number = trim($this->pay_term_number);

    if ($this->pay_term_number === '' || $this->pay_term_number === null) {
      $this->pay_term_number = 0;
    }

    $this->payments = collect($this->payments)->map(function ($pago) {
      $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']);
      return $pago;
    })->toArray();

    // Validar
    $validatedData = $this->validate();

    try {
      // Encuentra el registro existente
      $record = Transaction::findOrFail($recordId);

      // Actualizar
      $record->update($validatedData);

      $this->dispatch('updateTransactionContext', [
        'transaction_id'    => $record->id,
        'department_id'     => $record->department_id,
        'bank_id'           => $record->bank_id,
        'type_notarial_act' => $record->proforma_type,
      ]);

      // --- Sincronizar pagos ---
      // 1. Obtener los IDs actuales en la BD
      $existingPaymentIds = $record->payments()->pluck('id')->toArray();

      // 2. Obtener los IDs que aún están en $this->payments
      $submittedPaymentIds = collect($this->payments)
        ->pluck('id')
        ->filter() // elimina null
        ->toArray();

      // 3. Detectar los eliminados (los que ya no están)
      $idsToDelete = array_diff($existingPaymentIds, $submittedPaymentIds);

      // 4. Eliminar los pagos que ya no están
      if (!empty($idsToDelete)) {
        TransactionPayment::whereIn('id', $idsToDelete)->delete();
      }

      // 5. Crear o actualizar los que se enviaron
      foreach ($this->payments as $pago) {
        $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']);

        if (!empty($pago['id'])) {
          TransactionPayment::updateOrCreate(['id' => $pago['id']], $pago);
        } else {
          $record->payments()->create($pago);
        }
      }

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

    $this->dispatch('updateExportFilters', [
      'search' => $this->search,
      'filters' => $this->filters,
      'selectedIds' => $this->selectedIds,
      'sortBy' => $this->sortBy,
      'sortDir' => $this->sortDir,
      'perPage' => $this->perPage
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
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
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

  public function getStatics()
  {
    $stats = Transaction::select([
      DB::raw("COUNT(*) AS total_facturas_proceso"),
      DB::raw("SUM(CASE WHEN proforma_status = 'SOLICITADA' THEN 1 ELSE 0 END) AS facturas_por_aprobar"),
      DB::raw("SUM(CASE WHEN currency_id = " . Currency::DOLARES . " AND proforma_type = 'HONORARIO' THEN totalComprobante ELSE 0 END) AS totalUsdHonorario"),
      DB::raw("SUM(CASE WHEN currency_id = " . Currency::COLONES . " AND proforma_type = 'HONORARIO' THEN totalComprobante ELSE 0 END) AS totalCrcHonorario"),
      DB::raw("SUM(CASE WHEN currency_id = " . Currency::DOLARES . " AND proforma_type = 'GASTO' THEN totalComprobante ELSE 0 END) AS totalUsdGasto"),
      DB::raw("SUM(CASE WHEN currency_id = " . Currency::COLONES . " AND proforma_type = 'GASTO' THEN totalComprobante ELSE 0 END) AS totalCrcGasto")
    ])
      ->whereMonth('created_at', Carbon::now()->month)
      ->whereYear('created_at', Carbon::now()->year)
      ->where('document_type', $this->document_type)
      ->whereIn('status', [Transaction::PENDIENTE, Transaction::RECIBIDA, Transaction::ACEPTADA, Transaction::RECHAZADA, Transaction::ANULADA])
      ->first();

    return $stats;
  }

  public function sendDocumentToHacienda($recordId)
  {
    try {
      // Iniciamos transacción con bloqueo para evitar condiciones de carrera
      DB::beginTransaction();

      // Bloqueamos el registro para operaciones de escritura
      $transaction = Transaction::lockForUpdate()->findOrFail($recordId);

      // Asignar la fecha de emisión
      $transaction->transaction_date = Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s');

      // Verificar si necesita consecutivo/clave
      if (!$transaction->consecutivo || !$transaction->key) {
        $secuencia = DocumentSequenceService::generateConsecutive(
          $transaction->document_type,
          $transaction->location_id
        );

        $transaction->consecutivo = $transaction->getConsecutivo($secuencia);
        $transaction->key = $transaction->generateKey();

        // Guardar cambios dentro de la transacción
        $transaction->save();
      }

      // Confirmar cambios en la base de datos
      DB::commit();
    } catch (Exception $e) {
      // Revertir cambios en caso de error
      DB::rollBack();
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "Error al enviar la nota de débito hacienda: " . $e->getMessage(),
      ]);
      throw new \Exception("Error al generar documento: " . $e->getMessage());
    }

    // Obtener el xml firmado y en base64
    $encode = true;
    $xml = Helpers::generateComprobanteElectronicoXML($transaction, $encode, 'content');

    //Loguearme en hacienda para obtener el token
    $username = $transaction->location->api_user_hacienda;
    $password = $transaction->location->api_password;
    try {
      $authService = new AuthService();
      $token = $authService->getToken($username, $password);
    } catch (\Exception $e) {
      throw new \Exception("An error occurred when trying to obtain the token in the hacienda api" . ' ' . $e->getMessage());
    }

    $tipoDocumento = $this->getTipoDocumento($transaction->document_type);

    $api = new ApiHacienda();
    $result = $api->send($xml, $token, $transaction, $transaction->location, $tipoDocumento);
    if ($result['error'] == 0) {
      $transaction->status = Transaction::RECIBIDA;
      $transaction->invoice_date = \Carbon\Carbon::now();
    } else {
      throw new \Exception($result['mensaje']);
    }

    // Guardar la transacción
    if (!$transaction->save()) {
      throw new \Exception(__('Un error ha ocurrido al enviar el comprobante a Hacienda'));
    } else {
      // Si todo fue exitoso, mostrar notificación de éxito
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => $result['mensaje'],
      ]);
    }
  }
}
