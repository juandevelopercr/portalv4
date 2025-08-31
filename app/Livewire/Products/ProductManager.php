<?php

namespace App\Livewire\Products;

use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\Country;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductTax;
use App\Models\UnitType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

class ProductManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'products.created_at';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Listados
  public $unitTypes;
  public $listdepartments;
  public $listbanks;

  public $name;
  public $code;
  public $description;
  public $business_id;
  public $type;
  public $unit_type_id;
  public $caby_code;
  public $price;
  public $is_expense;
  public $enable_quantity;

  public $sku;
  public $image;
  public $created_by;

  public $closeForm = false;

  public $columns;
  public $defaultColumns;
  public $listActives;

  // para el calculo del desglo se servicios
  public $desgloseMonto;
  public $desgloseMoneda;
  public $desgloseBanco;

  public $currencies;

  // En tu componente Livewire
  public $activeTab = 1; // 1, 2 o 3
  public $degloseHtml;

  protected $listeners = [
    'cabyCodeSelected' => 'handleCabyCodeSelected',
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  protected function getModelClass(): string
  {
    return Product::class;
  }

  public function handleCabyCodeSelected($code)
  {
    $this->caby_code = $code['code'];
  }

  public function mount($type)
  {
    $this->type = $type;
    $this->business_id = 1;
    $this->unitTypes = UnitType::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->listActives = [['id' => 1, 'name' => 'Activo'], ['id' => 0, 'name' => 'Inactivo']];

    $this->currencies = Currency::orderBy('code', 'ASC')->get();

    $this->refresDatatable();
  }

  public function render()
  {
    $records = Product::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->when($this->active !== '', function ($query) {
        $query->where('products.active', $this->active);
      })
      ->where('type', $this->type)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.products.datatable', [
      'records' => $records,
    ]);
  }

  public function updatedActive($value)
  {
    $this->active = (int) $value;
  }

  public function updatedEnableQuantity($value)
  {
    $this->enable_quantity = (int) $value;
  }

  public function create()
  {
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    $this->active = 1;
    $this->unit_type_id = UnitType::SERVICIO_PROFESIONAL;

    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    return [
      'name' => 'required|string|max:191',
      'code' => 'required|string|max:5',
      'unit_type_id' => 'required|exists:unit_types,id',
      'price' => 'nullable|numeric|min:0',
      'caby_code' => 'required|string|max:13',
      'enable_quantity' => 'nullable|numeric|min:0',
      'type'  => 'required|in:single,variable,service,combo',
      //'sku' => 'required|string|max:20|unique:products,sku',
      'active' => 'required|integer|in:0,1',
    ];
  }

  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'required_if' => 'El campo :attribute es obligatorio cuando el tipo es :value.',
      'required_with' => 'El campo :attribute es obligatorio.',
      'numeric' => 'El campo :attribute debe ser un número válido.',
      'integer' => 'El campo :attribute debe ser un número válido.',
      'min' => 'El campo :attribute debe ser al menos :min caracteres',
      'max' => 'El campo :attribute no puede exceder :max caracteres',
      'in' => 'El campo :attribute no es válido.',
      'exists' => 'El campo :attribute no existe en el sistema.',
      'string' => 'El campo :attribute debe ser un texto.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    ];
  }

  protected function validationAttributes()
  {
    return [
      'name' => 'name',
      'code' => 'code',
      'unit_type_id' => 'unit type',
      'price' => 'price',
      'caby_code' => 'caby code',
      'enable_quantity' => 'anable quantity',
      //'sku' => 'required|string|max:20|unique:products,sku',
      'active' => 'active',
    ];
  }

  public function store()
  {
    $this->active = empty($this->active) ? 0 : $this->active;
    $this->price = (float) str_replace(',', '', $this->price);
    $this->validate();

    //$this->created_by = Auth::user()->id;

    try {
      // Crear el usuario con la contraseña encriptada
      $record = Product::create($this->only([
        'name',
        'code',
        'business_id',
        'type',
        'unit_type_id',
        'caby_code',
        'price',
        'enable_quantity',
        //'created_by',
        'active',
      ]));

      $closeForm = $this->closeForm;

      if (empty(trim($record->sku))) {
        $sku = $record->generateProductSku($record->id);
        $record->sku = $sku;
        $record->save();
      }

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
        $this->dispatch('$refresh');
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {

      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Product::findOrFail($recordId);
    $this->recordId = $recordId;

    $this->name = $record->name;
    $this->code = $record->code;
    $this->unit_type_id = $record->unit_type_id;
    $this->price = Helpers::formatDecimal($record->price);
    $this->caby_code = $record->caby_code;
    $this->enable_quantity = $record->enable_quantity;
    //'sku' => 'required|string|max:20|unique:products,sku',
    $this->active = $record->active;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;
    $this->price = (float) str_replace(',', '', $this->price);
    $this->active = empty($this->active) ? 0 : $this->active;
    $this->validate();

    try {
      // Encuentra el registro existente
      $record = Product::findOrFail($recordId);

      //dd($this);
      // Actualiza el producto
      $record->update($this->only([
        'name',
        'code',
        'business_id',
        'type',
        'unit_type_id',
        'caby_code',
        'price',
        'enable_quantity',
        'active',
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
      $record = Product::findOrFail($recordId);

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
      'name',
      'unit_type_id',
      'caby_code',
      'code',
      'price',
      'is_expense',
      'enable_quantity',
      'sku',
      'image',
      'description',
      'active',
      'created_by',
      'closeForm',
      'activeTab'
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

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
  }

  public function setCabyCode($code)
  {
    $this->caby_code = $code;
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'product-datatable')
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
    'filter_code' => NULL,
    'filter_name' => NULL,
    'filter_caby_code' => NULL,
    'filter_price' => NULL,
    'filter_unit_type' => NULL,
    'filter_active' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'code',
        'orderName' => 'code',
        'label' => __('Code'),
        'filter' => 'filter_code',
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
        'field' => 'name',
        'orderName' => 'products.name',
        'label' => __('Name'),
        'filter' => 'filter_name',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-300',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'caby_code',
        'orderName' => 'caby_code',
        'label' => __('Caby Code'),
        'filter' => 'filter_caby_code',
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
        'field' => 'price',
        'orderName' => 'price',
        'label' => __('Price'),
        'filter' => 'filter_price',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
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
        'field' => 'unit_type',
        'orderName' => 'unit_types.code',
        'label' => __('Unit Type'),
        'filter' => 'filter_unit_type',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => 'center',
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
        'field' => 'active',
        'orderName' => 'products.active',
        'label' => __('Active'),
        'filter' => 'filter_active',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
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
    $this->reset('filters');
    $this->selectedIds = [];
  }

  public function setActiveTab($tab)
  {
    $this->activeTab = $tab;
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  #[On('clonar')]
  public function clonar($recordId)
  {
    DB::beginTransaction();

    try {
      $original = Product::with(['taxes'])->findOrFail($recordId);

      // Clonar el producto principal
      $cloned = $original->replicate();
      $cloned->name = $original->name . ' (Copia)';
      $cloned->code = '9999'; //
      $cloned->save();

      // Clonar impuestos
      foreach ($original->taxes as $item) {
        $copy = $item->replicate();
        $copy->product_id = $cloned->id;
        $copy->save();
      }

      DB::commit();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The product has been successfully cloned')]);

      return response()->json(['success' => true, 'message' => 'Producto clonado exitosamente', 'id' => $cloned->id]);
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. While cloning the service') . ' ' . $e->getMessage()]);
      Log::error('Error al clonar producto.', ['error' => $e->getMessage()]);
    }
  }
}
