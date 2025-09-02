<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Contact;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use App\Models\BusinessLocation;

class InvoiceManager extends TransactionManager
{

  public $sortBy = 'transactions.id';

  public $filters = [
    'filter_proforma_no',
    'filter_consecutivo' => NULL,
    'filter_document_type' => NULL,
    'filter_customer_name' => NULL,
    'filter_transaction_date' => NULL,
    'filter_issuer_name' => NULL,
    'filter_currency_code' => NULL,
    'filter_fecha_envio_email' => NULL,
    'filter_totalComprobante' => NULL,
    'filter_status' => NULL,
    'filter_action' => NULL,
  ];

  public $documentTypes;

  public function mount()
  {
    parent::mount();
    // Aquí puedes agregar lógica específica para proformas
    $this->documentTypes = [
      ['id' => 'FE', 'name' => 'FACTURA'],
      ['id' => 'TE', 'name' => 'TIQUETE']
    ];
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'invoice-datatable')
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
        'field' => 'document_type',
        'orderName' => 'transactions.document_type',
        'label' => __('Tipo'),
        'filter' => 'filter_document_type',
        'filter_type' => 'select',
        'filter_sources' => 'documentTypes',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlDocumentType',
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
        'function' => 'getInvoiceHtmlColumnAction',
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
    $document_type = $this->document_type;
    if (!is_array($this->document_type)) {
      $document_type = [$this->document_type];
    }

    $query = Transaction::search($this->search, $this->filters)
      ->whereIn('document_type', $document_type);

    $query->where(function ($q) {
      $q->whereIn('status', [Transaction::PENDIENTE, Transaction::RECIBIDA, Transaction::ACEPTADA, Transaction::RECHAZADA, Transaction::ANULADA]);
    });
    return $query;
  }

  public function render()
  {
    $query = $this->getFilteredQuery();

    // Ordenamiento y paginación final
    $records = $query
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.transactions.invoice-datatable', [
      'records' => $records,
    ]);
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

    if ($propertyName == 'email_cc') {
      $this->updatedEmails();
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

  public function confirmarWithFormAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $this->dispatch('show-creditnote-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforeCreditNote()
  {
    $this->confirmarWithFormAccion(
      null,
      'createCreditNote',
      "¿Está seguro que desea anular la factura electrónica?",
      'Después de confirmar, la factura será anulada mediante nota de crédito electrónica',
      __('Sí, proceed')
    );
  }

  #[On('createCreditNote')]
  public function createCreditNote($recordId, $motivo)
  {
    DB::beginTransaction();

    try {
      // Bloquear el registro original para evitar modificaciones concurrentes
      $original = Transaction::with([
        'lines.taxes',
        'lines.discounts',
        'otherCharges',
        //'commissions', // Si es necesario clonar
        //'documents'   // Si es necesario clonar
      ])->lockForUpdate()->findOrFail($recordId);

      // Validar que la transacción original sea válida para nota de crédito
      if (!$this->isCreditNoteEligible($original->status)) {
        throw new \Exception(__('El comprobante no es elegible para nota de crédito. Seleccione un comprobante con estado ACEPTADO'));
      }

      $cloned = $original->replicate();
      $now = Carbon::now('America/Costa_Rica');

      // Configuración básica
      $cloned->forceFill([
        'document_type' => Transaction::NOTACREDITOELECTRONICA,
        'transaction_date' => $now->format('Y-m-d H:i:s'),
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'created_by' => auth()->id(),

        'RefTipoDoc' => '01',
        'RefNumero' => $original->key,
        'RefFechaEmision' => $original->transaction_date,
        'RefCodigo' => '01',
        'RefRazon' => trim($motivo),

        'created_by' => auth()->user()->id,
        'proforma_status' => null,
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'access_token' => NULL,
        'response_xml' => NULL,
        'filexml' => NULL,
        'filepdf' => NULL,

        'num_request_hacienda_set' => 0,
        'num_request_hacienda_get' => 0,
        'transaction_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'invoice_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'fecha_envio_email' => NULL,
        'totalPagado' => 0,
        'pendientePorPagar' => $original->totalComprobante,
        'vuelto' => 0,
      ]);

      // Generar consecutivo y clave
      $secuencia = DocumentSequenceService::generateConsecutive(
        $cloned->document_type,
        $cloned->location_id
      );
      $cloned->consecutivo = $cloned->getConsecutivo($secuencia);
      $cloned->key = $cloned->generateKey();
      $cloned->save();

      // Clonar líneas con montos negativos
      foreach ($original->lines as $line) {
        $clonedLine = $line->replicate();
        $clonedLine->forceFill([
          'transaction_id' => $cloned->id,
          //'quantity' => -abs($line->quantity), // Negativo
          //'unit_price' => -abs($line->unit_price), // Negativo
        ]);
        $clonedLine->save();

        // Clonar impuestos (negativos)
        foreach ($line->taxes as $tax) {
          $clonedTax = $tax->replicate();
          $clonedTax->transaction_line_id = $clonedLine->id;
          //$clonedTax->amount = -abs($tax->amount);
          $clonedTax->save();
        }

        // Clonar descuentos (negativos)
        foreach ($line->discounts as $discount) {
          $clonedDiscount = $discount->replicate();
          $clonedDiscount->transaction_line_id = $clonedLine->id;
          //$clonedDiscount->amount = -abs($discount->amount);
          $clonedDiscount->save();
        }
      }

      // Clonar otros cargos (negativos)
      foreach ($original->otherCharges as $charge) {
        $clonedCharge = $charge->replicate();
        $clonedCharge->transaction_id = $cloned->id;
        //$clonedCharge->amount = -abs($charge->amount);
        $clonedCharge->save();
      }

      // Clonar comisiones y documentos si es necesario
      // ... (agregar lógica similar según requerimientos)

      // Generar XML
      $xml = Helpers::generateComprobanteElectronicoXML($cloned, true, 'content');

      // Autenticación en Hacienda
      try {
        $authService = new AuthService();
        $token = $authService->getToken(
          $cloned->location->api_user_hacienda,
          $cloned->location->api_password
        );
      } catch (\Exception $e) {
        throw new \Exception("An error occurred when trying to obtain the token in the hacienda api" . ' ' . $e->getMessage());
      }

      // Enviar a Hacienda
      $api = new ApiHacienda();
      $result = $api->send(
        $xml,
        $token,
        $cloned,
        $cloned->location,
        Transaction::NCE
      );

      if ($result['error'] != 0) {
        throw new \Exception($result['mensaje']);
      }

      // Actualizar estado si es exitoso
      $cloned->update([
        'status' => Transaction::RECIBIDA,
        'invoice_date' => $now
      ]);

      DB::commit();

      // Livewire: Notificación y limpieza
      $this->reset(['selectedIds', 'recordId']);
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('Se ha creado la nota de crédito satisfactoriamente')]);

      return response()->json([
        'success' => true,
        'id' => $cloned->id
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error creating credit note', ['error' => $e, 'recordId' => $recordId]);

      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Ha ocurrido un error al crear a nota de crédito') . ' ' . $e->getMessage()]);
    }
  }

  private function isCreditNoteEligible($status): bool
  {
    return in_array($status, [
      Transaction::ACEPTADA,
      Transaction::RECHAZADA
    ]);
  }

  public function beforeDebitNote()
  {
    $this->confirmarWithFormAccion(
      null,
      'createDebitNote',
      "¿Está seguro que desea realizar nota de débito a la factura electrónica?",
      'Después de confirmar, se creará la nota de débito electrónica',
      __('Sí, proceed')
    );
  }

  #[On('createDebitNote')]
  public function createDebitNote($recordId, $motivo)
  {
    DB::beginTransaction();

    try {
      // Bloquear el registro original para evitar modificaciones concurrentes
      $original = Transaction::with([
        'lines.taxes',
        'lines.discounts',
        'otherCharges',
        //'commissions', // Si es necesario clonar
        //'documents'   // Si es necesario clonar
      ])->lockForUpdate()->findOrFail($recordId);

      // Validar que la transacción original sea válida para nota de débito
      if (!$this->isCreditNoteEligible($original->status)) {
        throw new \Exception(__('El comprobante no es elegible para nota de débito. Seleccione un comprobante con estado ACEPTADO'));
      }

      $cloned = $original->replicate();
      $now = Carbon::now('America/Costa_Rica');

      // Configuración básica
      $cloned->forceFill([
        'document_type' => Transaction::NOTADEBITOELECTRONICA,
        'transaction_date' => $now->format('Y-m-d H:i:s'),
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'created_by' => auth()->id(),
        'key' => null,
        'consecutivo' => null,

        'RefTipoDoc' => '01',
        'RefNumero' => $original->key,
        'RefFechaEmision' => $original->transaction_date,
        'RefCodigo' => '02',
        'RefRazon' => trim($motivo),

        'created_by' => auth()->user()->id,
        'proforma_status' => null,
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'access_token' => NULL,
        'response_xml' => NULL,
        'filexml' => NULL,
        'filepdf' => NULL,

        'num_request_hacienda_set' => 0,
        'num_request_hacienda_get' => 0,
        'transaction_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'invoice_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'fecha_envio_email' => NULL,
        'totalPagado' => 0,
        'pendientePorPagar' => $original->totalComprobante,
        'vuelto' => 0,
      ]);

      $cloned->save();

      DB::commit();

      // Livewire: Notificación y limpieza
      $this->reset(['selectedIds', 'recordId']);
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('Se ha creado la nota de débito satisfactoriamente')]);

      // Redirigir al componente de edición de la nota de débito
      return $this->redirectRoute('billing-debit-note', ['id' => $cloned->id, 'action' => 'edit']);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error creating debit note', ['error' => $e, 'recordId' => $recordId]);

      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Ha ocurrido un error al crear a nota de débito') . ' ' . $e->getMessage()]);
    }
  }

  public function create()
  {
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    // Obtener la fecha actual en formato Y-m-d
    $today = Carbon::now()->toDateString();

    // Convertir a formato d-m-Y para mostrar en el input
    $this->show_transaction_date = Carbon::parse($today)->format('d-m-Y');
    $this->transaction_date = Carbon::parse($today)->format('Y-m-d H:i:s');

    $this->created_by = Auth::user()->id;
    $this->invoice_type = 'FACTURA';
    $this->document_type = Transaction::FACTURAELECTRONICA;

    $this->payment_status = 'due';

    $this->proforma_status = 'PROCESO';
    $this->status = 'PENDIENTE';
    $this->proforma_change_type = Helpers::formatDecimal(Session::get('exchange_rate'));

    $location = BusinessLocation::where('id', 1)->first();
    $this->location_id = $location->id;
    $this->location_economic_activity_id = $location->economicActivities[0]->id;

    $this->payments = [[
      'tipo_medio_pago' => '04', // Transferencia
      'medio_pago_otros' => '',
      'total_medio_pago' => '0',
    ]];

    $this->recalcularVuelto();

    $this->action = 'create';
    $this->dispatch('scroll-to-top');
    //$this->dispatch('select2');
    $this->dispatch('reinitSelect2Controls');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    $rules = [
      // Foreign Keys
      'business_id'           => 'required|integer|exists:business,id',
      'location_id'           => 'nullable|integer|exists:business_locations,id',
      'location_economic_activity_id'  => 'nullable|integer|exists:economic_activities,id',
      'contact_id'            => 'required|integer|exists:contacts,id',
      'contact_economic_activity_id' => 'nullable|integer|exists:economic_activities,id',
      'currency_id'           => 'required|integer|exists:currencies,id',
      'created_by'            => 'required|integer',

      // Enums
      'document_type'         => 'required|in:PR,FE,TE,ND,NC,FEC,FEE,REP',
      'proforma_status'       => 'nullable|in:PROCESO,SOLICITADA,FACTURADA,RECHAZADA,ANULADA',
      'status'                => 'nullable|in:PENDIENTE,RECIBIDA,ACEPTADA,RECHAZADA,ANULADA',
      'pay_term_type'         => 'nullable|in:days,months',
      'invoice_type'          => 'required|in:FACTURA,TIQUETE',

      // Strings
      'customer_name'         => 'required|string|max:150',
      'customer_comercial_name' => 'nullable|string|max:150',
      'customer_email'        => 'nullable|email|max:150',
      'email_cc'              => 'nullable|string',
      'condition_sale' => 'required|string|in:01,02,03,04,05,06,06,08,09,10,11,12,13,14,15,99|max:2',
      'condition_sale_other' => 'nullable|required_if:condition_sale,99|max:100|string',

      'proforma_change_type'  => 'nullable|numeric|required_if:document_type,PR|min:0.1|max:999999999999999.99999',
      'factura_change_type'   => 'nullable|numeric|min:0|max:999999999999999.99999',

      // Texts
      'message'               => 'nullable|string',
      'notes'                 => 'nullable|string',
      'detalle_adicional'     => 'nullable|string',

      // Dates
      'transaction_date'         => 'required|date',
      'fecha_envio_email'        => 'nullable|date',

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

    if ($this->condition_sale == '02') {
      $rules['pay_term_number'] = 'required|integer|min:1|max:100';
    } else {
      $rules['pay_term_number'] = 'nullable';
    }

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

  public function store()
  {
    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    // Eliminar comas del número en el servidor
    //$this->proforma_change_type = str_replace(',', '', $this->proforma_change_type);
    $this->pay_term_number = trim($this->pay_term_number);

    if ($this->pay_term_number === '' || $this->pay_term_number === null) {
      $this->pay_term_number = 0;
    }

    $this->transaction_date = Carbon::parse($this->show_transaction_date)
      ->setTime(now()->hour, now()->minute, now()->second)
      ->format('Y-m-d H:i:s');

    if ($this->invoice_type == 'TIQUETE') {
      $this->document_type = Transaction::TIQUETEELECTRONICO;
    }

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

    $this->payments = collect($this->payments)->map(function ($pago) {
      $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']);
      return $pago;
    })->toArray();

    // Validar nuevamente para asegurar que el campo correcto esté presente
    $this->validate([
      'proforma_no' => 'required|string|max:20',
    ]);

    $this->totalPagado = collect($this->payments)->sum(function ($p) {
      $valor = str_replace(',', '', $p['total_medio_pago']); // elimina separadores de miles
      return floatval($valor);
    });

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
        $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']); // elimina separadores de miles
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

    $record = Transaction::find($recordId);
    $this->recordId = $recordId;
    //$this->transaction = $record;

    $this->business_id            = $record->business_id;
    $this->location_id            = $record->location_id;
    $this->location_economic_activity_id = $record->location_economic_activity_id;
    $this->contact_id             = $record->contact_id;
    $this->contact_economic_activity_id = $record->contact_economic_activity_id;
    $this->currency_id            = $record->currency_id;
    $this->created_by             = $record->created_by;
    $this->document_type          = $record->document_type;
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
    $this->pay_term_number        = $record->pay_term_number;
    $this->proforma_change_type   = Helpers::formatDecimal($record->proforma_change_type);
    //$this->proforma_change_type   = $record->proforma_change_type;
    $this->factura_change_type    = $record->factura_change_type;
    $this->num_request_hacienda_set = $record->num_request_hacienda_set;
    $this->num_request_hacienda_get = $record->num_request_hacienda_get;
    $this->message                = $record->message;
    $this->notes                  = $record->notes;
    $this->detalle_adicional      = $record->detalle_adicional;
    $this->transaction_date       = $record->transaction_date;
    $this->showInstruccionesPago   = $record->showInstruccionesPago;
    $this->invoice_type            = $record->invoice_type;

    // Totales
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

    $this->show_transaction_date = Carbon::parse($record->transaction_date)->format('d-m-Y');
    $this->original_currency_id = $record->currency_id;

    $this->clientEmail = $record->contact->email;

    $contact = Contact::find($record->contact_id);
    $this->tipoIdentificacion = $contact->identificationType->name;
    $this->identificacion = $contact->identification;

    // Se emite este evento para los componentes hijos
    $this->dispatch('updateTransactionContext', [
      'transaction_id'    => $record->id,
    ]);

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

    $this->dispatch('reinitSelect2Controls');
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    $this->transaction_date = Carbon::parse($this->show_transaction_date)
      ->setTime(now()->hour, now()->minute, now()->second)
      ->format('Y-m-d H:i:s');

    $this->pay_term_number = trim($this->pay_term_number);

    if ($this->pay_term_number === '' || $this->pay_term_number === null) {
      $this->pay_term_number = 0;
    }

    $this->payments = collect($this->payments)->map(function ($pago) {
      $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']);
      return $pago;
    })->toArray();

    if ($this->invoice_type == 'TIQUETE') {
      $this->document_type = Transaction::TIQUETEELECTRONICO;
    }

    // Validar
    $validatedData = $this->validate();

    try {
      // Encuentra el registro existente
      $record = Transaction::findOrFail($recordId);

      // Actualizar
      $record->update($validatedData);

      $this->dispatch('updateTransactionContext', [
        'transaction_id'    => $record->id
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

  public function resetControls()
  {
    $this->reset(
      //'business_id',
      //'location_id',
      //      'location_economic_activity_id',
      'contact_id',
      'contact_economic_activity_id',
      'currency_id',
      'created_by',
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
      'pay_term_number',
      'proforma_change_type',
      'factura_change_type',
      'fecha_envio_email',
      'num_request_hacienda_set',
      'num_request_hacienda_get',
      'message',
      'notes',
      'detalle_adicional',
      'transaction_date',
      'activeTab',
      'closeForm',
      'payments',
      'vuelto',
      'invoice_type',
      'tipoIdentificacion',
      'identificacion'
    );

    $this->currency_id = null;

    // Forzar actualización de Select2
    $this->dispatch('resetSelect2', [
      'ids' => ['currency_id']
    ]);

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function beforeclonar()
  {
    $this->confirmarAccion(
      null,
      'clonar',
      '¿Está seguro que desea clonar este registro?',
      'Después de confirmar, el registro será clonado',
      __('Sí, proceed'),
      true
    );
  }

  #[On('clonar')]
  public function clonar($recordId)
  {
    $recordId = $this->getRecordAction($recordId, true);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    DB::beginTransaction();

    try {
      $original = Transaction::with(['lines', 'otherCharges'])->findOrFail($recordId);

      // Generar consecutivo
      $consecutive = DocumentSequenceService::generateConsecutive(
        'PR',
        NULL
      );

      // Clonar transaction
      $cloned = $original->replicate();
      $cloned->proforma_no = $consecutive;
      $cloned->created_by = auth()->user()->id;
      $cloned->proforma_status = Transaction::PROCESO;
      $cloned->status = Transaction::PENDIENTE;
      $cloned->payment_status = 'due';
      $cloned->consecutivo = NULL;
      $cloned->key = NULL;
      $cloned->access_token = NULL;
      $cloned->response_xml = NULL;
      $cloned->filexml = NULL;
      $cloned->filepdf = NULL;
      $cloned->transaction_reference = NULL;
      $cloned->transaction_reference_id = NULL;
      $cloned->proforma_change_type = Session::get('exchange_rate');
      $cloned->factura_change_type = NULL;
      $cloned->num_request_hacienda_set = 0;
      $cloned->num_request_hacienda_get = 0;
      $cloned->transaction_date = Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s');
      $cloned->invoice_date = NULL;
      $cloned->fecha_envio_email = NULL;
      $cloned->totalPagado = 0;
      $cloned->pendientePorPagar = $original->totalComprobante;
      $cloned->vuelto = 0;
      $cloned->RefRazon = NULL;
      $cloned->RefCodigoOtro = NULL;
      $cloned->RefCodigo = NULL;
      $cloned->RefFechaEmision = NULL;
      $cloned->RefNumero = NULL;
      $cloned->RefTipoDocOtro = NULL;
      $cloned->RefTipoDoc = NULL;
      $cloned->RefTipoDoc = NULL;
      $cloned->save();

      // Clonar lines
      foreach ($original->lines as $item) {
        $copy = $item->replicate();
        $copy->transaction_id = $cloned->id;
        $copy->save();

        // clonar los taxes
        foreach ($item->taxes as $tax) {
          $copyTax = $tax->replicate();
          $copyTax->transaction_line_id = $copy->id;
          $copyTax->save();
        }

        // clonar los descuentos
        foreach ($item->discounts as $discount) {
          $copyDiscount = $discount->replicate();
          $copyDiscount->transaction_line_id = $copy->id;
          $copyDiscount->save();
        }
      }

      // Clonar otros cargos
      foreach ($original->otherCharges as $item) {
        $copy = $item->replicate();
        $copy->transaction_id = $cloned->id;
        $copy->save();
      }

      $payment = new TransactionPayment;
      $payment->transaction_id = $cloned->id;
      $payment->tipo_medio_pago = '04';  // transaferencia
      $payment->medio_pago_otros = '';
      $payment->total_medio_pago = 0;
      $payment->save();

      // Clona los documentos asociados (colección 'documents')
      /*
      foreach ($original->getMedia('documents') as $media) {
        // Verifica que el archivo físico existe en el disco configurado
        if (Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
          $media->copy($cloned, 'documents');
        } else {
          Log::warning("Archivo no encontrado al clonar media ID {$media->id}: " . $media->getPath());
        }
      }
      */

      DB::commit();

      $this->selectedIds = [];
      $this->dispatch('updateSelectedIds', $this->selectedIds);

      $this->recordId = '';

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The proforma has been successfully cloned')]);

      return response()->json(['success' => true, 'message' => 'Proforma clonada exitosamente', 'id' => $cloned->id]);
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. While cloning the proforma') . ' ' . $e->getMessage()]);
      Log::error('Error al clonar producto.', ['error' => $e->getMessage()]);
    }
  }
}
