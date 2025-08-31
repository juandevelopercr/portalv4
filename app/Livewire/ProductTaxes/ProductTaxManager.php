<?php

namespace App\Livewire\ProductTaxes;

use App\Livewire\BaseComponent;
use App\Models\DataTableConfig;
use App\Models\ExonerationType;
use App\Models\Institution;
use App\Models\ProductTax;
use App\Models\TaxRate;
use App\Models\TaxType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

class ProductTaxManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'ptSearch', history: true)]
  public $search = '';

  #[Url(as: 'ptSortBy', history: true)]
  public $sortBy = 'tax_types.name';

  #[Url(as: 'ptSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'ptPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Listados
  public $taxTypes;
  public $taxRates;
  public $exhonerations;
  public $institutes;

  public $product_id;
  public $tax_type_id;
  public $tax_rate_id;
  public $tax;
  public $tax_type_other;
  public $factor_calculo_tax;
  public $count_unit_type;
  public $percent;
  public $proporcion;
  public $volumen_unidad_consumo;
  public $impuesto_unidad;
  public $exoneration_type_id;
  public $exoneration_doc;
  public $exoneration_doc_other;
  public $exoneration_article;
  public $exoneration_inciso;
  public $exoneration_institution_id;
  public $exoneration_institute_other;
  public $exoneration_date;
  public $exoneration_percent;

  public $closeForm = false;

  public $columns;
  public $defaultColumns;
  public $listActives;

  protected function getModelClass(): string
  {
    return ProductTax::class;
  }

  public function mount($product_id)
  {
    $this->product_id = $product_id;
    $this->taxTypes = TaxType::orderBy('code', 'ASC')->get();
    $this->taxRates = TaxRate::where('active', 1)->orderBy('code', 'ASC')->get();
    $this->exhonerations = ExonerationType::where('active', 1)->where('id', '<>', 8)->orderBy('code', 'ASC')->get();
    $this->institutes = Institution::orderBy('code', 'ASC')->get();
    $this->listActives = [['id' => 1, 'name' => 'Activo'], ['id' => 0, 'name' => 'Inactivo']];
    $this->refresDatatable();
  }

  public function render()
  {
    $records = ProductTax::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('product_id', '=', $this->product_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.products-taxes.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    $this->dispatch('scroll-to-top');
    $this->dispatch('reinitSelect2Controls');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    $rules = [
      'product_id' => 'required|exists:products,id',
      'tax_type_id' => 'required|exists:tax_types,id',
      'tax_rate_id' => 'required|exists:tax_rates,id',
      'tax' => 'required|numeric|min:0|max:100',
      'tax_type_other' => 'nullable|required_if:tax_type_id,99|min:5|max:100',
      'factor_calculo_tax' => 'nullable|required_if:tax_type_id,8|numeric|min:0|max:9.9999',
      'count_unit_type' => 'nullable|required_if:tax_type_id,3,4,5,6|numeric|min:0|max:99999.99',
      'percent' => 'nullable|required_if:tax_type_id,4|numeric|min:0|max:100',
      'proporcion' => 'nullable|required_if:tax_type_id,4|numeric|min:0|max:100',
      'volumen_unidad_consumo' => 'nullable|required_if:tax_type_id,5|numeric|min:0|max:100',
      'impuesto_unidad' => 'nullable|required_if:tax_type_id,3,4,5,6|numeric|min:0|max:999999999999.99999',
      'exoneration_type_id' => 'nullable|exists:exoneration_types,id'
    ];

    if ($this->exoneration_type_id) {
      $rules['exoneration_percent'] = 'required|numeric|min:0.01|max:100';
      $rules['exoneration_doc'] = 'required|max:40';
      $rules['exoneration_date'] = 'required|date';
      $rules['exoneration_institution_id'] = 'required|exists:institutions,id';
    } else {
      $rules['exoneration_percent'] = 'nullable|numeric|min:0.01|max:100';
      $rules['exoneration_doc'] = 'nullable|max:40';
      $rules['exoneration_date'] = 'nullable|date';
      $rules['exoneration_institution_id'] = 'nullable|exists:institutions,id';
    }

    if ($this->exoneration_type_id == '99') {
      $rules['exoneration_doc_other'] = 'required|min:5|max:100';
    }

    if (in_array($this->exoneration_type_id, [2, 3, 6, 7, 8])) {
      $rules['exoneration_article'] = 'required|min:5|max:100';
      $rules['exoneration_inciso']  = 'required|min:5|max:100';
    }

    if ($this->exoneration_institution_id == '99') {
      $rules['exoneration_institute_other'] = 'required|min:5|max:100';
    }

    return $rules;
  }

  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'required_if' => 'El campo :attribute es obligatorio cuando el tipo de impuesto es :value.',
      'numeric' => 'El campo :attribute debe ser un número válido.',
      'min' => 'El campo :attribute debe ser al menos :min.',
      'max' => 'El campo :attribute no puede exceder :max.',
      'in' => 'El campo :attribute no es válido.',
    ];
  }

  protected function validationAttributes()
  {
    return [
      'tax_type_id' => 'Tipo de Impuesto',
      'tax_rate_id' => 'Tasa de Impuesto',
      'tax' => 'Impuesto',
      'tax_type_other' => 'Descripción de Otros',
      'factor_calculo_tax' => 'Factor de Cálculo',
      'count_unit_type' => 'Unidad de Conteo',
      'percent' => 'Porcentaje',
      'impuesto_unidad' => 'Impuesto por Unidad',
      'exoneration_type_id' => 'exoneration type',
      'exoneration_institution_id' => 'institution',
      'exoneration_institute_other' => 'other institution',
      'exoneration_doc' => 'document',
      'exoneration_article' => 'articulo',
      'exoneration_inciso' => 'inciso',
      'exoneration_doc_other' => 'otros',
      'exoneration_date' => 'date',
      'exoneration_percent' => 'percent',
    ];
  }

  public function updatedExonerationTypeId()
  {
    if (!$this->exoneration_type_id) {
      $this->reset(['exoneration_percent', 'exoneration_doc', 'exoneration_date']);
    }
  }

  public function store()
  {
    $this->validate();

    // Validación condicional específica
    $this->validateAdditionalRules();

    $this->exoneration_date = !empty($this->exoneration_date) ? Carbon::parse($this->exoneration_date)->format('Y-m-d') : $this->exoneration_date;
    $this->exoneration_percent = empty($this->exoneration_percent) ? NULL : $this->exoneration_percent;
    $this->exoneration_institution_id = empty($this->exoneration_institution_id) ? NULL : $this->exoneration_institution_id;

    try {

      // Crear el usuario con la contraseña encriptada
      $record = ProductTax::create($this->only([
        'product_id',
        'tax_type_id',
        'tax_rate_id',
        'tax',
        'tax_type_other',
        'factor_calculo_tax',
        'count_unit_type',
        'percent',
        'impuesto_unidad',
        'exoneration_type_id',
        'exoneration_doc',
        'exoneration_doc_other',
        'exoneration_article',
        'exoneration_inciso',
        'exoneration_institution_id',
        'exoneration_institute_other',
        'exoneration_date',
        'exoneration_percent',
      ]));

      $closeForm = $this->closeForm;

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

  /**
   * Validación adicional condicional basada en tax_type_id.
   */
  private function validateAdditionalRules()
  {
    // Validación condicional específica
    if ($this->tax_type_id == 99 && empty($this->tax_type_other)) {
      $this->addError('tax_type_other', 'El campo Tax Type Other es obligatorio cuando el tipo de impuesto es 99.');
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('El campo Tax Type Other es obligatorio cuando el tipo de impuesto es 99.')]);
      return;
    }

    if (in_array($this->tax_type_id, [3, 4, 5, 6]) && empty($this->count_unit_type)) {
      $this->addError('count_unit_type', 'El campo Count Unit Type es obligatorio para los códigos de impuesto 03, 04, 05 y 06.');
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('El campo Count Unit Type es obligatorio para los códigos de impuesto 03, 04, 05 y 06.')]);
      return;
    }

    if ($this->tax_type_id == 4 && empty($this->percent)) {
      $this->addError('percent', 'El campo Percent es obligatorio para el código de impuesto 04.');
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('El campo Percent es obligatorio para el código de impuesto 04.')]);
      return;
    }

    if ($this->tax_type_id == 5 && empty($this->volumen_unidad_consumo)) {
      $this->addError('volumen_unidad_consumo', 'El campo Volumen Unidad Consumo es obligatorio para el código de impuesto 05.');
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('El campo Volumen Unidad Consumo es obligatorio para el código de impuesto 05.')]);
      return;
    }

    // Validación condicional específica
    if ($this->exoneration_type_id == 99 && empty($this->exoneration_doc_other)) {
      /*
      throw \Illuminate\Validation\ValidationException::withMessages([
        'exoneration_doc_other' => 'El campo Otros es obligatorio cuando el tipo de exoneración es 99.'
      ]);
      */
      $this->addError('exoneration_doc_other', 'El campo Otros es obligatorio cuando el tipo de exoneración es 99.');
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('El campo Otros es obligatorio cuando el tipo de exoneración es 99.')]);
      return;
    }

    if (!is_null($this->exoneration_percent) && !is_null($this->tax) && $this->exoneration_percent > $this->tax) {
      throw \Illuminate\Validation\ValidationException::withMessages([
        'exoneration_percent' => 'El porciento de exoneración no puede ser mayor que el IVA.'
      ]);
      $this->addError('exoneration_percent', 'El porciento de exoneración no puede ser mayor que el IVA.');
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('El porciento de exoneración no puede ser mayor que la tarifa del IVA.')]);
      return;
    }

    // Si hay errores, aborta la ejecución del método
    if ($this->getErrorBag()->any()) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('There is invalid data, please review')]);
      //throw new \Exception('Validación condicional fallida.');
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = ProductTax::find($recordId);
    $this->recordId = $recordId;

    $this->product_id                    = $record->product_id;
    $this->tax_type_id                   = $record->tax_type_id;
    $this->tax_rate_id                   = $record->tax_rate_id;
    $this->tax                           = $record->tax;
    $this->tax_type_other                = $record->tax_type_other ?? '';
    $this->factor_calculo_tax            = $record->factor_calculo_tax ?? 0.0000;
    $this->count_unit_type               = $record->count_unit_type ?? 0.00;
    $this->percent                       = $record->percent ?? 0.00;
    $this->proporcion                    = $record->proporcion ?? 0.00;
    $this->volumen_unidad_consumo        = $record->volumen_unidad_consumo ?? 0.00;
    $this->impuesto_unidad               = $record->impuesto_unidad ?? 0.00000;
    $this->exoneration_type_id           = $record->exoneration_type_id;
    $this->exoneration_doc               = $record->exoneration_doc;
    $this->exoneration_article           = $record->exoneration_article;
    $this->exoneration_inciso            = $record->exoneration_inciso;
    $this->exoneration_doc_other         = $record->exoneration_doc_other;
    $this->exoneration_institution_id    = $record->exoneration_institution_id;
    $this->exoneration_institute_other   = $record->exoneration_institute_other;
    $this->exoneration_date              = $record->exoneration_date;
    $this->exoneration_percent           = $record->exoneration_percent;

    $this->exoneration_date = !empty($record->exoneration_date) ? Carbon::parse($record->exoneration_date)->format('d-m-Y') : $record->exoneration_date;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->dispatch('reinitSelect2Controls');

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;
    $this->validate();

    // Validación condicional específica
    $this->validateAdditionalRules();

    $this->exoneration_date = !empty($this->exoneration_date) ? Carbon::parse($this->exoneration_date)->format('Y-m-d') : $this->exoneration_date;

    $this->exoneration_percent = empty($this->exoneration_percent) ? NULL : $this->exoneration_percent;

    $this->exoneration_institution_id = empty($this->exoneration_institution_id) ? NULL : $this->exoneration_institution_id;

    //dd($this);
    try {
      // Encuentra el registro existente
      $record = ProductTax::findOrFail($recordId);

      // Actualiza el usuario
      $record->update($this->only([
        'tax_type_id',
        'tax_rate_id',
        'tax',
        'tax_type_other',
        'factor_calculo_tax',
        'count_unit_type',
        'percent',
        'impuesto_unidad',
        'exoneration_type_id',
        'exoneration_doc',
        'exoneration_doc_other',
        'exoneration_article',
        'exoneration_inciso',
        'exoneration_institution_id',
        'exoneration_institute_other',
        'exoneration_date',
        'exoneration_percent',
      ]));

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
      $record = ProductTax::findOrFail($recordId);

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
      'tax_type_id',
      'tax_rate_id',
      'tax',
      'tax_type_other',
      'factor_calculo_tax',
      'count_unit_type',
      'percent',
      'proporcion',
      'volumen_unidad_consumo',
      'impuesto_unidad',
      'closeForm',
      'exoneration_type_id',
      'exoneration_doc',
      'exoneration_doc_other',
      'exoneration_article',
      'exoneration_inciso',
      'exoneration_institution_id',
      'exoneration_institute_other',
      'exoneration_date',
      'exoneration_percent',
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
    if ($property === 'tax_type_id') {
      $taxTypeId = $this->tax_type_id ?? null;

      if ($taxTypeId != 99) {
        $this->tax_type_other = NULL;
      }

      if ($taxTypeId != 8) {
        $this->factor_calculo_tax = NULL;
      }

      if (!in_array($taxTypeId, [3, 4, 5, 6])) {
        $this->count_unit_type = NULL;
        $this->impuesto_unidad = NULL;
      }

      if ($taxTypeId != 4) {
        $this->percent = NULL;
        $this->proporcion = NULL;
      }

      if ($taxTypeId != 5) {
        $this->volumen_unidad_consumo = NULL;
      }
    }

    if ($property === 'tax_rate_id') {
      switch ($this->tax_rate_id) {
        case 1:
          $this->tax = 0;
          break;
        case 2:
          $this->tax = 1;
          break;
        case 3:
          $this->tax = 2;
          break;
        case 4:
          $this->tax = 4;
          break;
        case 5:
          $this->tax = 0;
          break;
        case 6:
          $this->tax = 4;
          break;
        case 7:
          $this->tax = 8;
          break;
        case 8:
          $this->tax = 13;
          break;
        case 9:
          $this->tax = 0.5;
          break;
        case 10:
          $this->tax = 0;
          break;
        case 11:
          $this->tax = 0;
          break;
      }
    }

    if ($property == 'exoneration_type_id' && (empty($this->exoneration_type_id) || is_null($this->exoneration_type_id))) {
      $this->exoneration_type_id = null;
      $this->exoneration_doc = null;
      $this->exoneration_article = null;
      $this->exoneration_inciso = null;
      $this->exoneration_institution_id = null;
      $this->exoneration_doc_other = null;
      $this->exoneration_date = null;
      $this->exoneration_percent = null;
    }
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'product-tax-datatable')
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
    'filter_tax_type_name' => NULL,
    'filter_tax_rate_name' => NULL,
    'filter_tax' => NULL,
    'filter_tax_type_other' => NULL,
    'filter_factor_calculo_tax' => NULL,
    'filter_count_unit_type' => NULL,
    'filter_percent' => NULL,
    'filter_proporcion' => NULL,
    'filter_volumen_unidad_consumo' => NULL,
    'filter_impuesto_unidad' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'tax_type_name',
        'orderName' => 'tax_types.name',
        'label' => __('Tax Type'),
        'filter' => 'filter_tax_type_name',
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
        'field' => 'tax_rate_name',
        'orderName' => 'tax_rates.name',
        'label' => __('Tax Rate'),
        'filter' => 'filter_tax_rate_name',
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
        'field' => 'tax_type_other',
        'orderName' => 'tax_type_other',
        'label' => __('Código de impuesto OTRO'),
        'filter' => 'filter_tax_type_other',
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
        'field' => 'factor_calculo_tax',
        'orderName' => 'factor_calculo_tax',
        'label' => __('Factor para Calculo IVA'),
        'filter' => 'filter_factor_calculo_tax',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'deciaml',
        'columnAlign' => 'center',
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
        'field' => 'count_unit_type',
        'orderName' => 'count_unit_type',
        'label' => __('Cantidad de la unidad de medida a utilizar'),
        'filter' => 'filter_count_unit_type',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'center',
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
        'field' => 'percent',
        'orderName' => 'product_taxes.percent',
        'label' => __('Percent'),
        'filter' => 'filter_percent',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'center',
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
        'field' => 'proporcion',
        'orderName' => 'proporcion',
        'label' => __('Proporción'),
        'filter' => 'filter_proporcion',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'center',
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
        'field' => 'volumen_unidad_consumo',
        'orderName' => 'volumen_unidad_consumo',
        'label' => __('Volumen por Unidad de Consumo'),
        'filter' => 'filter_volumen_unidad_consumo',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'center',
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
        'field' => 'impuesto_unidad',
        'orderName' => 'impuesto_unidad',
        'label' => __('Impuesto por Unidad'),
        'filter' => 'filter_impuesto_unidad',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'center',
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

  public function resetFilters()
  {
    $this->reset('filters');
    $this->selectedIds = [];
  }
}
