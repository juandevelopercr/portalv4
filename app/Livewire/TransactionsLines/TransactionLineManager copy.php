<?php

namespace App\Livewire\TransactionsLines;

use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\DiscountType;
use App\Models\ExonerationType;
use App\Models\Institution;
use App\Models\Product;
use App\Models\ProductTax;
use App\Models\TaxRate;
use App\Models\TaxType;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\TransactionLineDiscount;
use App\Models\TransactionLineTax;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class TransactionLineManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'ptSearch', history: true)]
  public $search = '';

  #[Url(as: 'ptSortBy', history: true)]
  public $sortBy = 'transactions_lines.id';

  #[Url(as: 'ptSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'ptPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Variables públicas
  public $transaction_id;
  public $transaction;
  public $product_id;
  public $codigo;
  public $codigocabys;
  public $detail;
  public $quantity;
  public $price;

  public $discount;
  public $subtotal;
  public $baseImponible;
  public $tax;
  public $impuestoAsumidoEmisorFabrica;
  public $impuestoNeto;
  public $total;
  public $servGravados;
  public $servExentos;
  public $servExonerados;
  public $servNoSujeto;
  public $mercGravadas;
  public $mercExentas;
  public $mercExoneradas;
  public $mercNoSujeta;
  public $exoneration;

  //Listados
  public $taxes = [];
  public $discounts = [];

  public $closeForm = false;

  public $columns;
  public $defaultColumns;

  public $canview;
  public $cancreate;
  public $canedit;
  public $candelete;
  public $canexport;
  public $record;

  public $facturaCompra;

  public $degloseHtml;

  protected $listeners = [
    'cabyCodeSelected' => 'handleCabyCodeSelected',
    'datatableSettingChange' => 'refresDatatable',
  ];

  protected function getModelClass(): string
  {
    return TransactionLine::class;
  }

  public function handleCabyCodeSelected($code)
  {
    $this->codigocabys = $code['code'];
  }

  #[Computed()]
  public function products()
  {
    $query = Product::query()->select(['products.id as id', 'products.name as name']);

    // Evitar filas duplicadas
    $query->distinct();

    return $query->orderBy('products.name', 'ASC')->get();
  }

  #[Computed]
  public function taxTypes()
  {
    return TaxType::orderBy('code', 'ASC')->get();
  }

  #[Computed]
  public function taxRates()
  {
    return TaxRate::where('active', 1)->orderBy('code', 'ASC')->get();
  }

  #[Computed]
  public function exhonerations()
  {
    return ExonerationType::where('active', 1)->where('id', '<>', 8)->orderBy('code', 'ASC')->get();
  }

  #[Computed]
  public function institutes()
  {
    return Institution::orderBy('code', 'ASC')->get();
  }

  #[Computed]
  public function discountTypes()
  {
    return DiscountType::orderBy('code', 'ASC')->get();
  }

  #[On('updateTransactionContext')]
  public function handleUpdateContext($data)
  {
    // Aqui si entra cuando edito
    $this->transaction_id = $data['transaction_id'];
    // Aquí puedes recargar los datos si es necesario

    $this->search = '';
  }

  public function mount($canview, $cancreate, $canedit, $candelete, $canexport, $facturaCompra = false)
  {
    $this->addTax();  // Inicializa con un tax vacío
    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;
    $this->facturaCompra = $facturaCompra;

    // Intentar obtener de sesión primero
    if (session()->has('transaction_context')) {
      $this->handleUpdateContext(session()->get('transaction_context'));
    }

    $this->refresDatatable();
  }

  public function render()
  {
    $records = TransactionLine::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('transaction_id', '=', $this->transaction_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.transactions-lines.datatable', [
      'records' => $records,
      ///'transaction' => $this->transaction,
      'canview' => $this->canview,
      'cancreate' => $this->cancreate,
      'canedit' => $this->canedit,
      'candelete' => $this->candelete,
      'canexport' => $this->canexport
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->action = 'create';
    $this->quantity = 1;
    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    $rules = [
      'transaction_id' => 'required|exists:transactions,id',
      'product_id' => 'required|exists:products,id',
      'codigo' => 'required|string|max:13',
      'codigocabys' => 'nullable|string|max:13',
      'detail' => 'required|string|max:200',
      'quantity' => 'required|numeric|min:1',
      'price' => 'required|numeric|min:0',

      // Totales
      'discount' => 'nullable|numeric|min:0',
      'subtotal' => 'nullable|numeric|min:0',
      'baseImponible' => 'nullable|numeric|min:0',
      'tax' => 'nullable|numeric|min:0',
      'impuestoAsumidoEmisorFabrica' => 'nullable|numeric|min:0',
      'impuestoNeto' => 'nullable|numeric|min:0',
      'total' => 'nullable|numeric|min:0',
      'servGravados' => 'nullable|numeric|min:0',
      'servExentos' => 'nullable|numeric|min:0',
      'servExonerados' => 'nullable|numeric|min:0',
      'servNoSujeto' => 'nullable|numeric|min:0',
      'mercGravadas' => 'nullable|numeric|min:0',
      'mercExentas' => 'nullable|numeric|min:0',
      'mercExoneradas' => 'nullable|numeric|min:0',
      'mercNoSujeta' => 'nullable|numeric|min:0',
      'exoneration' => 'nullable|numeric|min:0'
    ];

    // Reglas dinámicas para taxes
    foreach ($this->taxes as $index => $tax) {
      $rules["taxes.$index.tax_type_id"] = 'required|exists:tax_types,id';
      $rules["taxes.$index.tax_rate_id"] = 'required|exists:tax_rates,id';
      $rules["taxes.$index.tax"]         = 'required|numeric|min:0|max:100';
      //$rules["taxes.$index.tax_amount"]  = 'required|numeric|min:0';

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 99) {
        $rules["taxes.$index.tax_type_other"] = 'required|min:5|max:100';
      } else {
        $rules["taxes.$index.tax_type_other"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 8) {
        $rules["taxes.$index.factor_calculo_tax"] = 'required|numeric|min:0.01|max:9.9999';
      } else {
        $rules["taxes.$index.factor_calculo_tax"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && in_array($tax['tax_type_id'], [3, 4, 5, 6])) {
        $rules["taxes.$index.count_unit_type"] = 'required|numeric|min:0.01|max:99999.99';
        $rules["taxes.$index.impuesto_unidad"] = 'required|numeric|min:0.01|max:99999.99';
      } else {
        $rules["taxes.$index.count_unit_type"] = 'nullable';
        $rules["taxes.$index.impuesto_unidad"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 4) {
        $rules["taxes.$index.percent"] = 'required|numeric|min:0.01|max:9.9999';
        $rules["taxes.$index.proporcion"] = 'required|numeric|min:0.01|max:9.9999';
      } else {
        $rules["taxes.$index.percent"] = 'nullable';
        $rules["taxes.$index.proporcion"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 5) {
        $rules["taxes.$index.volumen_unidad_consumo"] = 'required|numeric|min:0.01|max:9.9999';
      } else {
        $rules["taxes.$index.volumen_unidad_consumo"] = 'nullable';
      }

      if ($tax['exoneration_type_id']) {
        $rules["taxes.$index.exoneration_percent"] = 'required|numeric|min:0.01|max:100';
        $rules["taxes.$index.exoneration_doc"] = 'required|max:40';
        $rules["taxes.$index.exoneration_date"] = 'required|date';
        $rules["taxes.$index.exoneration_institution_id"] = 'required|exists:institutions,id';
      }

      if ($tax['exoneration_type_id'] == '99') {
        $rules["taxes.$index.exoneration_doc_other"] = 'required|min:5|max:100';
      }

      if (in_array($tax['exoneration_type_id'], [2, 3, 6, 7, 8])) {
        $rules["taxes.$index.exoneration_article"] = 'required|min:5|max:100';
        $rules["taxes.$index.exoneration_inciso"]  = 'required|min:5|max:100';
      }

      if ($tax['exoneration_type_id'] == '99') {
        $rules["taxes.$index.exoneration_institute_other"] = 'required|min:5|max:100';
      }

      if ((float)$tax['exoneration_percent'] > 0) {
        $rules["taxes.$index.exoneration_doc"] = 'required|max:40';
        $rules["taxes.$index.exoneration_date"] = 'required|date';
        $rules["taxes.$index.exoneration_institution_id"] = 'required|exists:institutions,id';
      }
    }

    // Reglas dinámicas para discounts
    foreach ($this->discounts as $index => $discount) {
      $rules["discounts.$index.discount_type_id"] = 'required|exists:discount_types,id';
      $rules["discounts.$index.discount_percent"] = 'required|numeric|min:0.01|max:100';
      //$rules["discounts.$index.discount_amount"] = 'required|numeric';

      if (isset($discount['discount_type_id']) && $discount['discount_type_id'] == 99) {
        $rules["discounts.$index.discount_type_other"] = 'required|min:5|max:100';
        $rules["discounts.$index.nature_discount"] = 'required|min:3|max:80';
      } else {
        $rules["discounts.$index.discount_type_other"] = 'nullable';
        $rules["discounts.$index.nature_discount"] = 'nullable';
      }
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
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    $attributes = [
      'transaction_id' => 'ID de transacción',
      'product_id' => 'ID de producto',
      'codigo' => 'código',
      'codigocabys' => 'código CABYS',
      'detail' => 'detalle',
      'quantity' => 'cantidad',
      'price' => 'precio unitario',
      'discount' => 'descuento',
      'tax' => 'impuesto'
    ];

    // Agregar dinámicamente los impuestos
    foreach ($this->taxes as $index => $tax) {
      $attributes["taxes.$index.tax_type_id"] = "Tipo de impuesto #" . ($index + 1);
      $attributes["taxes.$index.tax_rate_id"] = "Tasa de impuesto #" . ($index + 1);
      $attributes["taxes.$index.tax"] = "Impuesto #" . ($index + 1);
      $attributes["taxes.$index.tax_type_other"] = "Otro tipo de impuesto #" . ($index + 1);
      $attributes["taxes.$index.factor_calculo_tax"] = "Factor de cálculo #" . ($index + 1);
      $attributes["taxes.$index.count_unit_type"] = "Cantidad de unidad #" . ($index + 1);
      $attributes["taxes.$index.percent"] = "Porcentaje #" . ($index + 1);
      $attributes["taxes.$index.proporcion"] = "Proporción #" . ($index + 1);
      $attributes["taxes.$index.volumen_unidad_consumo"] = "Volumen por unidad #" . ($index + 1);
      $attributes["taxes.$index.impuesto_unidad"] = "Impuesto por unidad #" . ($index + 1);
      $attributes["taxes.$index.tax_amount"] = "Monto IVA #" . ($index + 1);

      $attributes["taxes.$index.exoneration_type_id"] = "tipo de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_doc"] = "documento de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_doc_other"] = "otro documento #" . ($index + 1);
      $attributes["taxes.$index.exoneration_institution_id"] = "código de institución #" . ($index + 1);
      $attributes["taxes.$index.exoneration_institute_other"] = "otra institución #" . ($index + 1);
      $attributes["taxes.$index.exoneration_article"] = "artículo de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_inciso"] = "inciso de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_date"] = "fecha de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_percent"] = "porcentaje de exoneración #" . ($index + 1);
    }

    // Agregar dinámicamente los impuestos
    foreach ($this->discounts as $index => $discount) {
      $attributes["discounts.$index.discount_type_id"] = "Tipo de descuento #" . ($index + 1);
      $attributes["discounts.$index.discount_percent"] = "Porciento de descuento #" . ($index + 1);
      $attributes["discounts.$index.discount_amount"] = "Monto de descuento #" . ($index + 1);
      $attributes["discounts.$index.discount_type_other"] = "Tipo de descuento Otro #" . ($index + 1);
      $attributes["discounts.$index.nature_discount"] = "Naturaleza de descuento #" . ($index + 1);
    }

    return $attributes;
  }

  public function store()
  {
    $transaction = Transaction::find($this->transaction_id);
    $product = Product::where('id', $this->product_id)->first();
    dd($this->price);
    $this->price = str_replace(',', '', $this->price);

    if ($product) {
      $this->codigo = $product->code;
      $this->codigocabys = $product->caby_code;
      $this->detail = $product->name;
    }

    // Validar
    $validatedData = $this->validate();

    if (empty($this->taxes)) {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('Debe definir el impuesto')]);
      return false;
    }

    try {
      // Crear el registro
      $record = TransactionLine::create($validatedData);

      $closeForm = $this->closeForm;

      $this->validateTaxes($this->taxes);

      // Calcular los montos de impuesto
      $this->actualizarTaxAmount($record->getMonto());

      // Sincronizar impuestos
      foreach ($this->taxes as $tax) {
        if (empty($tax['exoneration_percent']))
          $tax['exoneration_percent'] = 0;
        $record->taxes()->updateOrCreate(
          ['id' => $tax['id'] ?? null], // Si el id existe, actualiza; si no, crea
          $tax  // Pasamos el arreglo directamente
        );
      }

      // Sincronizar descuentos
      foreach ($this->discounts as $discount) {
        if (!is_null($discount['discount_type_id'])) {
          $record->discounts()->updateOrCreate(
            ['id' => $discount['id'] ?? null],
            $discount
          );
        }
      }

      $this->updateTransactionTotals($record);
      $this->dispatch('productUpdated', $record->transaction_id);  // Emitir evento para otros componentes

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {
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

    $record = TransactionLine::find($recordId);
    $this->record = $record;
    $this->recordId = $recordId;

    // Asignar valores del registro a las variables públicas
    $this->transaction_id = $record->transaction_id;
    $this->product_id = $record->product_id;
    $this->codigo = $record->codigo;
    $this->codigocabys = $record->codigocabys;
    $this->detail = $record->detail;
    $this->quantity = $record->quantity;
    $this->price = $this->price;
    $this->discount = $record->discount;
    $this->subtotal = $record->subtotal;
    $this->baseImponible = $record->baseImponible;
    $this->tax = $record->tax;
    $this->impuestoAsumidoEmisorFabrica = $record->impuestoAsumidoEmisorFabrica;
    $this->impuestoNeto = $record->impuestoNeto;
    $this->total = $record->total;
    $this->servGravados = $record->servGravados;
    $this->servExentos = $record->servExentos;
    $this->servExonerados = $record->servExonerados;
    $this->servNoSujeto = $record->servNoSujeto;
    $this->mercGravadas = $record->mercGravadas;
    $this->mercExentas = $record->mercExentas;
    $this->mercExoneradas = $record->mercExoneradas;
    $this->mercNoSujeta = $record->mercNoSujeta;
    $this->exoneration = $record->exoneration;
    // Cargar taxes
    $this->taxes = $record->taxes->map(function ($tax) {
      return [
        'id' => $tax->id,
        'tax_type_id' => $tax->tax_type_id,
        'tax_rate_id' => $tax->tax_rate_id,
        'tax' => number_format((float)$tax->tax, 2, '.', ''),
        'tax_type_other' => $tax->tax_type_other ?? '',
        'factor_calculo_tax' => $tax->factor_calculo_tax ?? null,
        'count_unit_type' => $tax->count_unit_type ?? null,
        'percent' => $tax->percent ?? null,
        'proporcion' => $tax->proporcion ?? null,
        'volumen_unidad_consumo' => $tax->volumen_unidad_consumo ?? null,
        'impuesto_unidad' => $tax->impuesto_unidad ?? null,
        //'tax_amount' => $tax->tax_amount ?? null,
        'tax_amount' => Helpers::formatDecimal($tax->tax_amount ?? 0),
        'exoneration_type_id' => $tax->exoneration_type_id ?? null,
        'exoneration_doc' => $tax->exoneration_doc ?? null,
        'exoneration_doc_other' => $tax->exoneration_doc_other ?? null,
        'exoneration_institution_id' => $tax->exoneration_institution_id ?? null,
        'exoneration_institute_other' => $tax->exoneration_institute_other ?? null,
        'exoneration_article' => $tax->exoneration_article ?? null,
        'exoneration_inciso' => $tax->exoneration_inciso ?? null,
        'exoneration_date' => $tax->exoneration_date ?? null,
        'exoneration_percent' => $tax->exoneration_percent ?? null,
      ];
    })->toArray();

    // **Cargar descuentos existentes**
    $this->discounts = $record->discounts->map(function ($discount) {
      return [
        'id' => $discount->id,
        'discount_type_id' => $discount->discount_type_id ?? null,
        'discount_percent' => $discount->discount_percent ?? null,
        'discount_amount' => $discount->discount_amount ?? null,
        'discount_type_other' => $discount->discount_type_other ?? null,
        'nature_discount' => $discount->nature_discount ?? null,
      ];
    })->toArray();

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
    $this->dispatch('refreshCleave');
  }

  public function update()
  {
    $recordId = $this->recordId;

    //$transaction = Transaction::find($this->transaction_id);
    $product = Product::where('id', $this->product_id)->first();
    if ($product) {
      $this->codigo = $product->code;
      $this->codigocabys = $product->caby_code;
    }

    $this->price = str_replace(',', '', $this->price);

    // Validar
    $validatedData = $this->validate();

    if (empty($this->taxes)) {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('Debe definir el impuesto')]);
      return false;
    }

    try {
      // Encuentra el registro existente
      $record = TransactionLine::findOrFail($recordId);

      // Actualiza el registro
      $record->update($validatedData);

      $closeForm = $this->closeForm;

      //*************************************************************
      //**********************Sincronizar taxes**********************
      //*************************************************************

      // Sincronizar impuestos (eliminar los que no estén en el array)
      $existingTaxIds = $record->taxes()->pluck('id')->toArray();

      // Filtrar impuestos enviados (excluyendo los que no tienen ID porque son nuevos)
      $incomingTaxIds = collect($this->taxes)->pluck('id')->filter()->toArray();

      // Eliminar impuestos que ya no están presentes
      $taxesToDelete = array_diff($existingTaxIds, $incomingTaxIds);

      if (!empty($taxesToDelete)) {
        $record->taxes()->whereIn('id', $taxesToDelete)->delete();
      }

      $this->validateTaxes($this->taxes);

      $monto = $record->getMonto();

      // Calcular los montos de impuesto
      $this->actualizarTaxAmount($monto);

      // Sincronizar impuestos
      foreach ($this->taxes as $tax) {
        if (empty($tax['exoneration_percent']))
          $tax['exoneration_percent'] = 0;
        $tax['tax_amount'] = str_replace(',', '', $tax['tax_amount']);
        $record->taxes()->updateOrCreate(
          ['id' => $tax['id'] ?? null],
          $tax
        );
      }

      //*************************************************************
      //*******************Sincronizar descuentos********************
      //*************************************************************

      // Sincronizar descuentos (eliminar los que no estén en el array)
      $existingDiscountIds = $record->discounts()->pluck('id')->toArray();

      // Filtrar descuentos enviados (excluyendo los que no tienen ID porque son nuevos)
      $incomingDiscountIds = collect($this->discounts)->pluck('id')->filter()->toArray();

      // Eliminar descuentos que ya no están presentes
      $discountsToDelete = array_diff($existingDiscountIds, $incomingDiscountIds);

      if (!empty($discountsToDelete)) {
        $record->discounts()->whereIn('id', $discountsToDelete)->delete();
      }

      // Actualizar o crear descuentos nuevos
      foreach ($this->discounts as $discount) {
        if (!is_null($discount['discount_type_id'])) {
          $record->discounts()->updateOrCreate(
            ['id' => $discount['id'] ?? null],
            $discount
          );
        }
      }

      $this->updateTransactionTotals($record);
      $this->dispatch('productUpdated', $record->transaction_id);  // Emitir evento para otros componentes

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

  private function validateTaxes($taxes)
  {
    foreach ($taxes as $tax) {
      $taxType = TaxType::find($tax['tax_type_id']);
      if (in_array($taxType->code, ['03', '04', '05', '06'])) {
        if (!$tax['count_unit_type'])
          throw new Exception("El campo 'CantidadUnidadMedida' para el calculo del impuesto es obligatorio cuando se usan los códigos de impuesto (03, 04, 05 y 06)");

        if ($taxType->code == '04' && !$tax['percent'])
          throw new Exception("El campo 'Porcentaje' para el calculo del impuesto es obligatorio cuando se usa el código de impuesto (04)");

        if ($taxType->code == '04' && !$tax['proporcion'])
          throw new Exception("El campo 'Proporcion' para el calculo del impuesto es obligatorio cuando se usa el código de impuesto (04)");

        if ($taxType->code == '05' && !$tax['volumen_unidad_consumo'])
          throw new Exception("El campo 'VolumenUnidadConsumo' para el calculo del impuesto es obligatorio cuando se usa el código de impuesto (05)");

        if (!$tax['impuesto_unidad'])
          throw new Exception("El campo 'ImpuestoUnidad' para el calculo del impuesto es obligatorio cuando se usan los códigos de impuesto (03, 04, 05 y 06)");
      }
    }
  }

  public function updateTransactionTotals($record)
  {
    $record->updateTransactionTotals($record->transaction->currency_id);

    $this->dispatch('productUpdated', $record->transaction_id);  // Emitir evento para otros componentes
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
      $record = TransactionLine::findOrFail($recordId);
      $transaction_id = $record->transaction_id;

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

        $this->dispatch('productUpdated', $transaction_id);  // Emitir evento para otros componentes

        // Puedes emitir un evento para redibujar el datatable o actualizar la lista
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
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

  public function resetControls()
  {
    $this->reset(
      'product_id',
      'codigocabys',
      'detail',
      'quantity',
      'price',
      'tax',
      'discount',
      'discounts',
      'taxes',
      'discount',
      'subtotal',
      'baseImponible',
      'tax',
      'impuestoAsumidoEmisorFabrica',
      'impuestoNeto',
      'total',
      'servGravados',
      'servExentos',
      'servExonerados',
      'servNoSujeto',
      'mercGravadas',
      'mercExentas',
      'mercExoneradas',
      'mercNoSujeta',
      'exoneration',
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

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function updated($property)
  {
    // $property: The name of the current property that was updated
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    if ($property === 'product_id') {
      $product = Product::find($this->product_id);
      $taxes = ProductTax::where('product_id', $this->product_id)->get();
      $discounts = [];
      if ($this->recordId)
        $discounts = TransactionLineDiscount::where('transaction_line_id', $this->recordId)->get();

      if (!is_null($product)) {
        $this->price = $product->price;
        $this->detail = $product->name;
        $this->codigo = $product->code;
        $this->codigocabys = $product->caby_code;
      }
      // Limpiar el array de taxes actual
      $this->taxes = [];

      $transaction = Transaction::find($this->transaction_id);
      $aplicarImpuesto = $transaction ? $transaction->contact->aplicarImpuesto : true;

      // Cargar los impuestos como objetos
      foreach ($taxes as $tax) {
        $this->taxes[] = [
          'id' => $tax->id,  // Mantén el ID si existe para actualizaciones
          'tax_type_id' => $aplicarImpuesto == false ? 1 : ($tax->tax_type_id ?? null),
          'tax_rate_id' => $aplicarImpuesto == false ? 1 : ($tax->tax_rate_id ?? null),
          'tax' => number_format((float)$tax->tax, 2, '.', ''),
          'tax_type_other' => $tax->tax_type_other ?? '',
          'factor_calculo_tax' => $tax->factor_calculo_tax ?? null,
          'count_unit_type' => $tax->count_unit_type ?? null,
          'percent' => $tax->percent ?? null,
          'proporcion' => $tax->proporcion ?? null,
          'volumen_unidad_consumo' => $tax->volumen_unidad_consumo ?? null,
          'impuesto_unidad' => $tax->impuesto_unidad ?? null,
          'tax_amount' => $tax->tax_amount ?? null,

          'exoneration_type_id' => $tax->exoneration_type_id ?? null,
          'exoneration_doc' => $tax->exoneration_doc ?? null,
          'exoneration_doc_other' => $tax->exoneration_doc_other ?? null,
          'exoneration_institution_id' => $tax->exoneration_institution_id ?? null,
          'exoneration_institute_other' => $tax->exoneration_institute_other ?? null,
          'exoneration_article' => $tax->exoneration_article ?? null,
          'exoneration_inciso' => $tax->exoneration_inciso ?? null,
          'exoneration_date' => $tax->exoneration_date ?? null,
          'exoneration_percent' => number_format((float)$tax->exoneration_percent, 2, '.', ''),
        ];
      }

      // Limpiar el array de discounts actual
      $this->discounts = [];

      // Cargar los impuestos como objetos
      foreach ($discounts as $discount) {
        $this->discounts[] = [
          'id' => $discount->id,
          'discount_type_id' => $discount->discount_type_id ?? null,
          'discount_percent' => $discount->discount_percent ?? null,
          'discount_amount' => $discount->discount_amount ?? null,
          'discount_type_other' => $discount->discount_type_other ?? '',
          'nature_discount' => $discount->nature_discount ?? '',
        ];
      }
    }

    if ($property === 'price' || $property === 'quantity') {
      if (empty($this->price))
        $this->price = 0;
      if (empty($this->quantity))
        $this->quantity = 0;
      if (!is_null($this->record)) {
        $this->record->price = $this->price;
        $this->record->quantity = $this->quantity;
      }
    }

    // Detectar si el cambio corresponde a un tax_type_id
    if (preg_match('/^taxes\.(\d+)\.tax_type_id$/', $property, $matches)) {
      $index = $matches[1]; // Extrae el índice del tax

      if (isset($this->taxes[$index])) {
        $taxTypeId = $this->taxes[$index]['tax_type_id'] ?? null;

        if ($taxTypeId != 99) {
          $this->taxes[$index]['tax_type_other'] = NULL;
        }

        if ($taxTypeId != 8) {
          $this->taxes[$index]['factor_calculo_tax'] = NULL;
        }

        if (!in_array($taxTypeId, [3, 4, 5, 6])) {
          $this->taxes[$index]['count_unit_type'] = NULL;
          $this->taxes[$index]['impuesto_unidad'] = NULL;
        }

        if ($taxTypeId != 4) {
          $this->taxes[$index]['percent'] = NULL;
          $this->taxes[$index]['proporcion'] = NULL;
        }

        if ($taxTypeId != 5) {
          $this->taxes[$index]['volumen_unidad_consumo'] = NULL;
        }
      }
    }
  }

  public function setCabyCode($code)
  {
    $this->codigocabys = $code;
  }

  public function addTax()
  {
    $this->taxes[] =
      [
        'id' => null,
        'tax_type_id' => null,
        'tax_rate_id' => null,
        'tax' => null,
        'tax_type_other' => null,
        'factor_calculo_tax' => null,
        'count_unit_type' => null,
        'percent' => null,
        'proporcion' => null,
        'volumen_unidad_consumo' => null,
        'impuesto_unidad' => null,

        'exoneration_type_id' => null,
        'exoneration_doc' => null,
        'exoneration_doc_other' => null,
        'exoneration_institution_id' => null,
        'exoneration_institute_other' => null,
        'exoneration_article' => null,
        'exoneration_inciso' => null,
        'exoneration_date' => null,
        'exoneration_percent' => null,
      ];
  }

  public function removeTax($index)
  {
    unset($this->taxes[$index]);
    $this->taxes = array_values($this->taxes);
  }

  public function actualizarTaxAmount($monto)
  {
    foreach ($this->taxes as $index => $tax) {
      switch ($tax['tax_rate_id']) {
        case 1:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
        case 2:
          $this->taxes[$index]['tax'] = 1;
          $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 1) / 100, 2, '.', '');
          break;
        case 3:
          $this->taxes[$index]['tax'] = 2;
          $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 2) / 100, 2, '.', '');
          break;
        case 4:
          $this->taxes[$index]['tax'] = 4;
          $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 4) / 100, 2, '.', '');
          break;
        case 5:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
        case 6:
          $this->taxes[$index]['tax'] = 4;
          $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 4) / 100, 2, '.', '');
          break;
        case 7:
          $this->taxes[$index]['tax'] = 8;
          $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 8) / 100, 2, '.', '');
          break;
        case 8:
          $this->taxes[$index]['tax'] = 13;
          $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 13) / 100, 2, '.', '');
          //$this->taxes[$index]['tax_amount'] = $monto;
          break;
        case 9:
          $this->taxes[$index]['tax'] = 0.5;
          $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 0.5) / 100, 2, '.', '');
          break;
        case 10:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
        case 11:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
      }
    }

    $this->dispatch('refreshCleave');
  }

  #[On('tax-rate-changed')]
  public function updateTaxRateFields($index, $value)
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    if (!isset($this->taxes[$index])) {
      logger("Índice inválido: $index");
      return;
    }

    $monto = $this->price * $this->quantity;

    switch ($value) {
      case 1:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
      case 2:
        $this->taxes[$index]['tax'] = 1;
        $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 1) / 100, 2, '.', '');
        break;
      case 3:
        $this->taxes[$index]['tax'] = 2;
        $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 2) / 100, 2, '.', '');
        break;
      case 4:
        $this->taxes[$index]['tax'] = 4;
        $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 4) / 100, 2, '.', '');
        break;
      case 5:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
      case 6:
        $this->taxes[$index]['tax'] = 4;
        $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 4) / 100, 2, '.', '');
        break;
      case 7:
        $this->taxes[$index]['tax'] = 8;
        $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 8) / 100, 2, '.', '');
        break;
      case 8:
        $this->taxes[$index]['tax'] = 13;
        $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 13) / 100, 2, '.', '');
        break;
      case 9:
        $this->taxes[$index]['tax'] = 0.5;
        $this->taxes[$index]['tax_amount'] = number_format((float)($monto * 0.5) / 100, 2, '.', '');
        break;
      case 10:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
      case 11:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
    }

    $this->dispatch('refreshCleave');
  }

  public function addDiscount()
  {
    $this->discounts[] = [
      'discount_type_id' => null,
      'discount_percent' => null,
      'discount_amount' => null,
      'discount_type_other' => null,
      'nature_discount' => null,
    ];
  }

  public function removeDiscount($index)
  {
    unset($this->discounts[$index]);
    $this->discounts = array_values($this->discounts);
  }

  #[On('discount-type-changed')]
  public function updateDiscountTypeFields($index, $value)
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->discounts[$index]['discount_type_id'] = $value;
  }

  #[On('percent-changed')]
  public function calculateDiscountAmount($index, $value)
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->discounts[$index]['discount_amount'] = 10;
    if (!empty($this->discounts[$index]['discount_percent']) && is_numeric($value) && $this->price > 0) {
      $price = (float) ($this->price * $this->quantity);
      $percent = (float) $value;
      $this->discounts[$index]['discount_amount'] = round(($price * $percent) / 100, 2);
    }
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'proformas-lines-datatable')
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
    'filter_codigocabys' => NULL,
    'filter_detail' => NULL,
    'filter_price' => NULL,
    'filter_quantity' => NULL,
    'filter_discount' => NULL,
    'filter_subtotal' => NULL,
    'filter_tax' => NULL,
    'filter_exoneration' => NULL,
    'filter_total' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'codigocabys',
        'orderName' => 'codigocabys',
        'label' => __('Caby Code'),
        'filter' => 'filter_codigocabys',
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
        'field' => 'detail',
        'orderName' => 'detail',
        'label' => __('Description'),
        'filter' => 'filter_detail',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-400',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'price',
        'orderName' => 'transactions_lines.price',
        'label' => __('Price'),
        'filter' => 'filter_price',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
        'field' => 'quantity',
        'orderName' => 'quantity',
        'label' => __('Quantity'),
        'filter' => 'filter_quantity',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'integer',
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
        'field' => 'discount',
        'orderName' => 'discount',
        'label' => __('Discount'),
        'filter' => 'filter_discount',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
        'field' => 'subtotal',
        'orderName' => 'subtotal',
        'label' => __('Subtotal'),
        'filter' => 'filter_subtotal',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
        'field' => 'tax',
        'orderName' => 'tax',
        'label' => __('Tax'),
        'filter' => 'filter_tax',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
        'field' => 'exoneration',
        'orderName' => 'exoneration',
        'label' => __('Exonerated'),
        'filter' => 'filter_exoneration',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
        'field' => 'total',
        'orderName' => 'total',
        'label' => __('Total Line'),
        'filter' => 'filter_total',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
    $this->reset('filters');
    $this->selectedIds = [];
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }
}
