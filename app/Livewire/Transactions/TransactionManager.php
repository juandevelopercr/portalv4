<?php

namespace App\Livewire\Transactions;

use \Exception;
use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\BusinessLocation;
use App\Models\ConditionSale;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\EconomicActivity;
use App\Models\PaymentMethod;
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
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

abstract class TransactionManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'transactions.id';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // listados
  public $conditionSales;
  public $currencies;
  public $issuers;
  public $users;

  //public $transaction;
  public $business_id;
  public $location_id;
  public $location_economic_activity_id;
  public $contact_id;
  public $contact_economic_activity_id;
  public $showInstruccionesPago;
  public $currency_id;
  public $created_by;
  public $document_type;
  public $proforma_status;
  public $status;
  public $payment_status;
  public $pay_term_type;
  public $customer_name;
  public $customer_comercial_name;
  public $customer_email;
  public $proforma_no;
  public $consecutivo;
  public $key;
  public $access_token;
  public $response_xml;
  public $filexml;
  public $filepdf;
  public $transaction_reference;
  public $transaction_reference_id;
  public $condition_sale;
  public $condition_sale_other;
  public $pay_term_number;
  public $proforma_change_type;
  public $factura_change_type;
  public $num_request_hacienda_set;
  public $num_request_hacienda_get;
  public $message;
  public $notes;
  public $detalle_adicional;
  public $email_cc;
  public $transaction_date;
  public $invoice_date;
  public $fecha_envio_email;
  public $original_currency_id;
  public $invoice_type = 'FACTURA';

  public $totalHonorarios;
  public $totalTimbres;
  public $totalDiscount;
  public $totalTax;
  public $totalAditionalCharge;

  public $totalServGravados;
  public $totalServExentos;
  public $totalServExonerado;
  public $totalServNoSujeto;

  public $totalMercGravadas;
  public $totalMercExentas;
  public $totalMercExonerada;
  public $totalMercNoSujeta;

  public $totalGravado;
  public $totalExento;
  public $totalVenta;
  public $totalVentaNeta;
  public $totalExonerado;
  public $totalNoSujeto;
  public $totalImpAsumEmisorFabrica;
  public $totalImpuesto;
  public $totalIVADevuelto;
  public $totalOtrosCargos;
  public $totalComprobante;

  public $paymentMethods;
  public $payments = [];
  public float $pendientePorPagar = 0.00;
  public float $totalPagado = 0.00;
  public float $vuelto = 0.00;

  // Estadísticas para el Header
  public $totalProceso;
  public $totalPorAprobar;
  public $totalUsdHonorario;
  public $totalCrcHonorario;
  public $totalUsdGasto;
  public $totalCrcGasto;

  public $validatedEmails = []; // Almacena correos válidos
  public $invalidEmails = []; // Almacena correos inválidos

  public $statusOptions;
  public $modalCustomerOpen = false; // Controla el estado del modal
  public $activeTab = 'invoice';
  public $closeForm = false;
  public $columns;
  public $defaultColumns;
  public $proformaTypes;
  public $isLoadingEmailModal = false;

  public $locationsEconomicActivities = [];
  public $contactEconomicActivities = [];
  public $paymentStatus = [];

  public $show_transaction_date;

  public $tipoIdentificacion;
  public $identificacion;

  public $RefTipoDoc;
  public $RefTipoDocOtro;
  public $RefNumero;
  public $RefFechaEmision;
  public $RefCodigo;
  public $RefCodigoOtro;
  public $RefRazon;

  public $clientEmail = '';

  public function setlocationEconomicActivities()
  {
    $activities = [];
    $activities = EconomicActivity::join('business_locations_economic_activities', 'business_locations_economic_activities.economic_activity_id', '=', 'economic_activities.id')
      ->where('business_locations_economic_activities.location_id', $this->location_id)
      ->orderBy('economic_activities.name', 'asc')
      ->get();

    $this->locationsEconomicActivities = $activities;
  }

  public function setcontactEconomicActivities()
  {
    $activities = [];
    $activities = EconomicActivity::join('contacts_economic_activities', 'contacts_economic_activities.economic_activity_id', '=', 'economic_activities.id')
      ->where('contacts_economic_activities.contact_id', $this->contact_id)
      ->orderBy('economic_activities.name', 'asc')
      ->get();

    $this->contactEconomicActivities = $activities;
  }

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'customerSelected' => 'handleCustomerSelected',
    'openCustomerModal' => 'openCustomerModal',
    'productUpdated' => 'refreshTotalByProduct',
    'chargeUpdated' => 'refreshTotalByCharge',
    'dateRangeSelected' => 'dateRangeSelected',
    'dateSelected' => 'handleDateSelected',
    'datatableSettingChange' => 'refresDatatable',
  ];

  protected function getModelClass(): string
  {
    return Transaction::class;
  }

  public function handleCustomerSelected($data)
  {
    $this->modalCustomerOpen = false;
    $this->contact_id = $data['customer_id'];
    $this->customer_name = $data['customer_name'];
    $this->customer_comercial_name = $data['customer_comercial_name'];
    $this->customer_email = $data['customer_email'];
    $this->email_cc = $data['email_cc'];
    $this->condition_sale = $data['condition_sale'];
    $this->pay_term_number = $data['pay_term_number'];
    $this->tipoIdentificacion = $data['tipoIdentificacion'];
    $this->identificacion = $data['identification'];
    $this->invoice_type = $data['invoice_type'];
    $this->clientEmail = $data['customer_email'];

    $this->contact_economic_activity_id = null;
    $this->setcontactEconomicActivities();

    $activities = $this->contactEconomicActivities;

    $options = $activities->map(function ($activity) {
      return [
        'id' => $activity->id,
        'text' => $activity->name,
      ];
    });

    $this->dispatch('updateSelect2Options', id: 'contact_economic_activity_id', options: $options);

    $this->dispatch('refreshCleave');
    $this->dispatch('reinitSelect2Controls');
  }

  public function openCustomerModal()
  {
    $this->modalCustomerOpen = true;
  }

  public function refreshTotalByProduct($transaction_id, $activeTabProduct = true)
  {
    // Si es null es que se actualizó el pproducto
    if ($activeTabProduct)
      $this->activeTab = 'product';
    $this->recalculeteTotals($transaction_id);
  }

  public function refreshTotalByCharge($transaction_id)
  {
    $this->activeTab = 'charges';
    $this->recalculeteTotals($transaction_id);
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  public function recalculeteTotals($transaction_id)
  {
    $transaction = Transaction::with('lines')->find($transaction_id);

    if ($transaction) {
      //Poner aqui el calculo de los totales
      // Realizar una única consulta para calcular todos los totales
      $totals = $transaction->lines()
        ->select([
          DB::raw('SUM(discount) as totalDiscount'),
          //DB::raw('SUM(tax) as totalTax'),
          DB::raw('SUM(servGravados) as totalServGravados'),
          DB::raw('SUM(servExentos) as totalServExentos'),
          DB::raw('SUM(servExonerados) as totalServExonerados'),
          DB::raw('SUM(servNoSujeto) as totalServNoSujeto'),

          DB::raw('SUM(mercGravadas) as totalmercGravadas'),
          DB::raw('SUM(mercExentas) as totalmercExentas'),
          DB::raw('SUM(mercExoneradas) as totalMercExoneradas'),
          DB::raw('SUM(mercNoSujeta) as totalMercNoSujeta'),

          DB::raw('SUM(
              CASE
                  WHEN (exoneration IS NULL OR exoneration = 0)
                      AND (impuestoAsumidoEmisorFabrica IS NULL OR impuestoAsumidoEmisorFabrica = 0)
                  THEN tax
                  WHEN exoneration > 0 OR (impuestoAsumidoEmisorFabrica IS NOT NULL AND impuestoAsumidoEmisorFabrica >= 0)
                  THEN impuestoNeto
                  ELSE 0
              END
          ) AS totalImpuesto'),
          DB::raw('SUM(impuestoAsumidoEmisorFabrica) as totalImpuestoAsumidoEmisorFabrica')
          //DB::raw('SUM(honorarios + timbres - discount) as totalVenta'),
        ])
        ->first();


      $totalCharge = $transaction->otherCharges()
        ->select([
          DB::raw('SUM(amount * quantity) as total'),
        ])
        ->first();

      // Asignar los resultados a los atributos de la transacción
      $transaction->totalAditionalCharge = $totals ? ($totals->totalAditionalCharge ?? 0) : 0;

      $transaction->totalServGravados = $totals ? ($totals->totalServGravados ?? 0) : 0;
      $transaction->totalServExentos = $totals ? ($totals->totalServExentos ?? 0) : 0;
      $transaction->totalServExonerado = $totals ? ($totals->totalServExonerados ?? 0) : 0;
      $transaction->totalServNoSujeto = $totals->totalServNoSujeto ?? 0;

      $transaction->totalMercGravadas = $totals ? ($totals->totalmercGravadas ?? 0) : 0;
      $transaction->totalMercExentas = $totals ? ($totals->totalmercExentas ?? 0) : 0;
      $transaction->totalMercExonerada = $totals ? ($totals->totalMercExoneradas ?? 0) : 0;
      $transaction->totalMercNoSujeta = $totals->totalMercNoSujeta ?? 0;

      $transaction->totalImpuesto = $totals ? ($totals->totalImpuesto ?? 0) : 0;
      $transaction->totalTax = $totals ? ($totals->totalImpuesto ?? 0) : 0;

      $transaction->totalGravado = $transaction->totalServGravados + $transaction->totalMercGravadas;
      $transaction->totalExento = $transaction->totalServExentos + $transaction->totalMercExentas;
      $transaction->totalExonerado = $transaction->totalServExonerado + $transaction->totalMercExonerada;
      $transaction->totalNoSujeto = $transaction->totalServNoSujeto + $transaction->totalMercNoSujeta;

      $transaction->totalVenta = $transaction->totalGravado + $transaction->totalExento + $transaction->totalExonerado + $transaction->totalNoSujeto;
      $transaction->totalDiscount = $totals ? ($totals->totalDiscount ?? 0) : 0;
      $transaction->totalVentaNeta = $transaction->totalVenta - $transaction->totalDiscount;

      $transaction->totalImpAsumEmisorFabrica = $totals ? ($totals->totalImpuestoAsumidoEmisorFabrica ?? 0) : 0;
      $transaction->totalIVADevuelto = 0; // Por ahora se pone en cero, si se factura algún medicamento debe colocarse
      $transaction->totalOtrosCargos = $totalCharge ? ($totalCharge->total ?? 0) : 0;
      $transaction->totalComprobante = $transaction->totalVentaNeta + $transaction->totalImpuesto + $transaction->totalOtrosCargos;
      $transaction->save();

      // Asignar los resultados a los atributos de la transacción
      $this->totalAditionalCharge = $transaction->totalAditionalCharge;

      $this->totalServGravados = $transaction->totalServGravados;
      $this->totalServExentos = $transaction->totalServExentos;
      $this->totalServExonerado = $transaction->totalServExonerado;
      $this->totalServNoSujeto = $transaction->totalServNoSujeto;

      $this->totalMercGravadas = $transaction->totalMercGravadas;
      $this->totalMercExentas = $transaction->totalMercExentas;
      $this->totalMercExonerada = $transaction->totalMercExonerada;
      $this->totalMercNoSujeta = $transaction->totalMercNoSujeta;

      $this->totalImpuesto = $transaction->totalImpuesto;
      $this->totalTax = $transaction->totalTax;

      $this->totalGravado = $transaction->totalGravado;
      $this->totalExento = $transaction->totalExento;
      $this->totalExonerado = $transaction->totalExonerado;
      $this->totalNoSujeto = $transaction->totalNoSujeto;

      $this->totalVenta = $transaction->totalVenta;
      $this->totalDiscount = $transaction->totalDiscount;
      $this->totalVentaNeta = $transaction->totalVentaNeta;

      $this->totalImpAsumEmisorFabrica = $transaction->totalImpAsumEmisorFabrica;
      $this->totalIVADevuelto = $transaction->totalIVADevuelto;
      $this->totalOtrosCargos = $transaction->totalOtrosCargos;
      $this->totalComprobante = $transaction->totalComprobante;
    }
  }

  public function mount()
  {
    $this->loadCommonData();
    //$this->loadLines();
    //$this->loadPayments();
  }

  protected function loadCommonData()
  {
    // Ejemplo de datos comunes que se podrían necesitar en todos los managers
    $this->business_id = 1;
    $this->currencies = Currency::orderBy('code', 'ASC')->get();
    $this->conditionSales = ConditionSale::where('active', 1)->orderBy('code', 'ASC')->get();
    $this->pay_term_type = 'days';
    $this->issuers = BusinessLocation::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->users = User::where('active', 1)->orderBy('name', 'ASC')->get();

    $location = BusinessLocation::where('business_id', 1)->first();
    $this->locationsEconomicActivities = $location->economicActivities;

    $this->payments = [];
    $this->validatedEmails; // Almacena correos válidos
    $this->invalidEmails; // Almacena correos inválidos

    $this->paymentStatus = [
      ['id' => 'paid', 'name' => 'Pagado'],
      ['id' => 'due', 'name' => 'Pendiente'],
      ['id' => 'partial', 'name' => 'Parcial'],
      ['id' => 'annulled', 'name' => 'Anulado']
    ];

    $this->condition_sale = ConditionSale::CREDIT;
    $this->pay_term_number = 30;
    $this->paymentMethods = PaymentMethod::where('active', 1)->orderBy('code', 'ASC')->get();
    $this->statusOptions = $this->getStatusOptions();

    $this->refresDatatable();
  }

  abstract public function getDefaultColumns(): array;

  abstract public function render();

  protected function cleanEmptyForeignKeys()
  {
    // Lista de campos que pueden ser claves foráneas
    $foreignKeys = [
      'location_id',
      'location_economic_activity_id',
      'contact_economic_activity_id',
      // Agrega otros campos aquí
    ];

    foreach ($foreignKeys as $key) {
      if (isset($this->$key) && $this->$key === '') {
        $this->$key = null;
      }
    }
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
    // ... el resto del código
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton, $clonar = false)
  {
    $recordId = $this->getRecordAction($recordId, $clonar);

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
      $record = Transaction::findOrFail($recordId);

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

        // Emitir un evento de éxito si la eliminación es exitosa
        $this->dispatch('show-notification', [
          'type' => 'success',
          'message' => __('The record has been deleted')
        ]);
      }
    } catch (QueryException $e) {
      // Capturar errores de integridad referencial (clave foránea)
      if ($e->getCode() == '23000') { // Código de error SQL para restricciones de integridad
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('The record cannot be deleted because it is related to other data.')
        ]);
      } else {
        // Otro tipo de error SQL
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage()
        ]);
      }
    } catch (\Exception $e) {
      // Capturar cualquier otro error general
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while deleting the record') . ' ' . $e->getMessage()
      ]);
    }
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

  public function setSortBy($sortByField)
  {
    if ($this->sortBy === $sortByField) {
      $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
      return;
    }

    $this->sortBy = $sortByField;
    $this->sortDir = 'DESC';
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function closeCustomerModal()
  {
    $this->modalCustomerOpen = false;
  }

  public function getStatusOptions()
  {
    // Retornar los estados
    $is_invoice = true;
    if (in_array($this->document_type, [Transaction::PROFORMA, Transaction::COTIZACION, Transaction::NOTACREDITO, Transaction::NOTADEBITO]))
      $is_invoice = false;

    if ($this->document_type == Transaction::COTIZACION) {
      return [
        ['id' => 'PROCESO', 'name' => __('PROCESO')]
      ];
    }

    $estados = Transaction::getStatusOptions($is_invoice);
    return $estados;
  }

  public function resetFilters()
  {
    foreach (array_keys($this->filters) as $key) {
      $this->filters[$key] = null;
    }

    $this->selectedIds = [];
    $this->dispatch('select2:refresh');
    $this->dispatch('clearFilterselect2');
  }

  public function downloadProformaSencilla($invoiceId)
  {
    $this->prepareExportProforma($invoiceId, 'sencillo', 'proforma');
  }

  public function downloadProformaDetallada($invoiceId)
  {
    $this->prepareExportProforma($invoiceId, 'detallado', 'proforma');
  }

  private function prepareExportProforma($invoiceId, $type, $prefix)
  {
    $key = uniqid('export_', true);

    if (empty($invoiceId) || !is_numeric($invoiceId)) {
      Log::warning("ID inválido al preparar exportación", ['invoiceId' => $invoiceId]);
      return;
    }

    cache()->put($key, [
      'invoiceId' => $invoiceId,
      'type' => $type,
    ], now()->addMinutes(5));

    $url = route('exportacion.' . $prefix . '.preparar', ['key' => $key]);
    $downloadBase = '/descargar-exportacion-' . $prefix;

    Log::info('Reporte', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);

    $this->dispatch('exportReady', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);
  }

  public function downloadReciboSencillo($invoiceId)
  {
    $this->prepareExportRecibo($invoiceId, 'sencillo', 'recibo');
  }

  public function downloadReciboDetallado($invoiceId)
  {
    $this->prepareExportRecibo($invoiceId, 'detallado', 'recibo');
  }

  private function prepareExportRecibo($invoiceId, $type, $prefix)
  {
    $key = uniqid('export_', true);

    cache()->put($key, [
      'invoiceId' => $invoiceId,
      'type' => $type,
    ], now()->addMinutes(5));

    $url = route('exportacion.' . $prefix . '.preparar', ['key' => $key]);
    $downloadBase = '/descargar-exportacion-' . $prefix;
    $this->dispatch('exportReady', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);
  }

  public function downloadXML($invoiceId)
  {
    try {
      // Buscar la transacción por su ID
      $transaction = Transaction::findOrFail($invoiceId);

      // Llamar al helper para generar el XML
      $encode = false;
      return Helpers::generateComprobanteElectronicoXML($transaction, $encode, 'browser');
    } catch (\Exception $e) {
      // Si ocurre un error, se captura la excepción y se muestra una notificación
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while downloading the XML:') . ' ' . $e->getMessage()
      ]);

      // Registrar el error en los logs para facilitar el diagnóstico
      logger()->error('Error while downloading XML: ' . $e->getMessage(), ['exception' => $e]);
    }
  }

  public function openEmailModal($transactionId)
  {
    $this->isLoadingEmailModal = true;
    // Simular un pequeño delay antes de abrir el modal (si es necesario)
    //sleep(1);
    $this->dispatch('openEmailModal', ['transactionId' => $transactionId]);
    $this->isLoadingEmailModal = false; // Resetear el loading después de abrir el modal
  }

  public function addPayment()
  {
    if (count($this->payments) >= 4) return;

    $this->payments[] = [
      'tipo_medio_pago' => '',
      'medio_pago_otros' => '',
      'total_medio_pago' => 0,
    ];

    $this->recalcularVuelto();
  }

  public function removePayment($index)
  {
    unset($this->payments[$index]);
    $this->payments = array_values($this->payments);
    $this->recalcularVuelto();
  }

  public function updatedPayments()
  {
    $this->recalcularVuelto();
  }

  public function recalcularVuelto()
  {
    //$this->totalPagado = collect($this->payments)->sum(fn($p) => floatval($p['total_medio_pago']));
    $this->totalPagado = collect($this->payments)->sum(function ($p) {
      $valor = str_replace(',', '', $p['total_medio_pago']); // elimina separadores de miles
      return floatval($valor);
    });
    $this->vuelto = max(0, $this->totalPagado - floatval($this->totalComprobante));
    $this->pendientePorPagar = max(0, floatval($this->totalComprobante) - $this->totalPagado);

    $this->resetErrorBag();
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    // Determinar estado de pago
    if ($this->totalPagado <= 0) {
      $this->payment_status = 'due';
    } elseif ($this->pendientePorPagar == 0) {
      $this->payment_status = 'paid';
    } else {
      $this->payment_status = 'partial';
    }
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
        $original->document_type,
        NULL
      );

      // Clonar transaction
      $cloned = $original->replicate();
      $cloned->proforma_no = $consecutive;
      $cloned->created_by = auth()->user()->id;
      $cloned->proforma_status = Transaction::PROCESO;
      $cloned->status = NULL;
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

  public function getStatusDocumentInHacienda($recordId)
  {
    try {
      // Intenta obtener la transacción
      $transaction = Transaction::findOrFail($recordId);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      // Manejo más específico del error cuando no se encuentra el registro
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "Invoice not found in the database for ID: $recordId"
      ]);
      return;
    }

    // Loguearme en hacienda para obtener el token
    $username = $transaction->location->api_user_hacienda;
    $password = $transaction->location->api_password;
    try {
      $authService = new AuthService();
      $token = $authService->getToken($username, $password);
    } catch (\Exception $e) {
      // Si falla la obtención del token, notificar al usuario
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "Error obtaining token: " . $e->getMessage()
      ]);
      return;
    }

    $tipoDocumento = $this->getTipoDocumento($transaction->document_type);

    // Consulta el estado del comprobante
    $api = new ApiHacienda();

    Log::info('getStatusComprobante:', ['tipoDocumento' => $tipoDocumento]);

    $result = $api->getStatusComprobante($token, $transaction, $transaction->location, $tipoDocumento);

    Log::info('resultado de getStatusComprobante:', ['result' => $result]);

    if ($result['estado'] == 'aceptado') {
      $sent = Helpers::sendComprobanteElectronicoEmail($recordId);

      if ($sent) {
        $transaction->fecha_envio_email = now();
        $transaction->save();

        $menssage = __('An email has been sent to the following addresses:') . ' ' . $transaction->contact->email;
        if (!empty($transaction->email_cc)) {
          $menssage .= ' ' . __('with copy to') . ' ' . $transaction->email_cc;
        }

        $this->dispatch('show-notification', [
          'type' => $result['type'],
          'message' => $result['mensaje'] . '<br> ' . $menssage
        ]);
      } else {
        $this->dispatch('show-notification', [
          'type' => $result['type'],
          'message' => $result['mensaje']
        ]);
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('An error occurred, the email could not be sent')
        ]);
      }
    } else {
      // Mostrar mensaje de error según el resultado de la API
      $this->dispatch('show-notification', [
        'type' => $result['type'],
        'message' => $result['mensaje']
      ]);

      if ($result['estado'] == 'rechazado')
        $sent = Helpers::sendNotificationComprobanteElectronicoRejected($recordId);
    }
  }

  public function sendDocumentToHacienda($recordId)
  {
    try {
      $transaction = Transaction::findOrFail($recordId);
    } catch (Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "No se ha encontrado el documento",
      ]);
      //throw new \Exception("No se ha encontrado el documento" . ' ' . $e->getMessage());
    }

    if (!$transaction->contact->other_signs || strlen($transaction->contact->other_signs) < 5) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "El campo otras señas del cliente no cumple con los requisitos, el campo no puede estar vacio y debe tener una logintud de al menos 5 caracteres",
      ]);
      return;
    }

    $msgs = Helpers::validateProformaToConvertInvoice($transaction);

    // Validación con mensajes
    if (!empty($msgs)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => implode('<br>', $msgs),
      ]);
      return;
    }

    //Asignar la fecha de emision
    $transaction->transaction_date = Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s');

    // Tipo de cambio del día
    $transaction->factura_change_type = Session::get('exchange_rate');

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
    $xml = Helpers::generateComprobanteElectronicoXML($transaction, $encode, 'content');

    //Loguearme en hacienda para obtener el token
    $username = $transaction->location->api_user_hacienda;
    $password = $transaction->location->api_password;
    try {
      $authService = new AuthService();
      $token = $authService->getToken($username, $password);
    } catch (\Exception $e) {
      //throw new \Exception("An error occurred when trying to obtain the token in the hacienda api" . ' ' . $e->getMessage());
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "Ha ocurrido un error al intentar identificarse en la api de hacienda",
      ]);
    }

    $tipoDocumento = $this->getTipoDocumento($transaction->document_type);

    $api = new ApiHacienda();
    $result = $api->send($xml, $token, $transaction, $transaction->location, $tipoDocumento);
    if ($result['error'] == 0) {
      $transaction->status = Transaction::RECIBIDA;
      $transaction->invoice_date = \Carbon\Carbon::now();
    } else {
      //throw new \Exception($result['mensaje']);
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => $result['mensaje'],
      ]);
    }

    // Guardar la transacción
    if (!$transaction->save()) {
      //throw new \Exception(__('Un error ha ocurrido al enviar el comprobante a Hacienda'));
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'Un error ha ocurrido al guardar la transación',
      ]);
    } else {
      // Si todo fue exitoso, mostrar notificación de éxito
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => $result['mensaje'],
      ]);
    }
  }

  public function downloadInvoice($invoiceId)
  {
    $this->prepareExportInvoice($invoiceId, 'invoice');
  }

  private function prepareExportInvoice($invoiceId, $prefix)
  {
    Log::warning("datos pasados a preparar exportación", [
      '$invoiceId' => $invoiceId,
    ]);

    $key = uniqid('export_', true);

    if (empty($invoiceId) || !is_numeric($invoiceId)) {
      Log::warning("ID inválido al preparar exportación", ['invoiceId' => $invoiceId]);
      return;
    }

    cache()->put($key, [
      'invoiceId' => $invoiceId
    ], now()->addMinutes(5));

    $url = route('exportacion.' . $prefix . '.preparar', ['key' => $key]);
    $downloadBase = '/descargar-exportacion-' . $prefix;

    Log::info('Reporte', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);

    $this->dispatch('exportReady', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);
  }

  public function downloadHaciendaResponsaXML($invoiceId)
  {
    try {
      $transaction = Transaction::findOrFail($invoiceId);
      $baseDir = public_path('storage/');
      $xmlResponse = $baseDir . $transaction->response_xml;

      // Verificar si el archivo existe
      if (!file_exists($xmlResponse)) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('No se ha encontrado el archivo de respuesta de hacienda: ') . $xmlResponse
        ]);
        return false;
      }

      $filename = $transaction->key . '_respuesta.xml';

      // Obtener el contenido del archivo
      $content = file_get_contents($xmlResponse);

      // Verificar si el contenido es válido
      if ($content === false) {
        throw new \Exception("No se pudo leer el archivo XML");
      }

      // Retornar la respuesta con el contenido del archivo
      return response()->streamDownload(function () use ($content) {
        echo $content;
      }, $filename, [
        'Content-Type' => 'application/xml; charset=utf-8',
        'Content-Disposition' => 'inline; filename="' . $filename . '"'
      ]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Error al descargar el XML:') . ' ' . $e->getMessage()
      ]);
      logger()->error('Error descargando XML: ' . $e->getMessage(), [
        'exception' => $e,
        'invoiceId' => $invoiceId,
        'path' => $xmlResponse ?? null
      ]);
      return false;
    }
  }

  public function updatedLocationId($value)
  {
    $this->setlocationEconomicActivities();
    $activities = $this->locationsEconomicActivities;

    $options = $activities->map(function ($activity) {
      return [
        'id' => $activity->id,
        'text' => $activity->name,
      ];
    });

    // Limpia el valor actual y notifica al JS para reiniciar el select
    $this->location_economic_activity_id = null;

    $this->dispatch('updateSelect2Options', id: 'location_economic_activity_id', options: $options);
  }

  public function getTipoDocumento($documentType)
  {
    $type = '';
    switch ($documentType) {
      case "FE":
        $type = '01';
        break;
      case "TE":
        $type = '04';
        break;
      case "NDE":
        $type = '02';
        break;
      case "NCE":
        $type = '03';
        break;
      case "FEC":
        $type = '08';
        break;
      case "FEE":
        $type = '09';
        break;
      case "REP":
        $type = '10';
        break;
    }
    return $type;
  }

  public function getRecordAction($recordId, $clonar = false)
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

    if ($clonar == false) {
      $transaction = Transaction::find($recordId);
      if ($transaction->proforma_status != Transaction::PROCESO) {
        $this->dispatch('show-notification', [
          'type' => 'warning',
          'message' => 'No puede editar una proforma que se encuentra en estado distinto de PROCESO'
        ]);
        return;
      }
    }

    return $recordId;
  }

  public function setActiveTab($tab)
  {
    $this->activeTab = $tab;
  }
}
