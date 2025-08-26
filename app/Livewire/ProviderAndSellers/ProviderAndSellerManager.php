<?php

namespace App\Livewire\ProviderAndSellers;

use App\Livewire\BaseComponent;
use App\Models\Contact;
use App\Models\DataTableConfig;
use App\Models\ProviderAndSeller;
use App\Models\ProviderCompany;
use App\Models\Seller;
use App\Models\ServiceProvider;
use App\Services\DocumentSequenceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class ProviderAndSellerManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $sortBy = 'providers_sellers.id';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Propiedades para el formulario
  public $fecha_venta;
  public $service_provider_id;
  public $seller_id;
  public $fecha_servicio;
  public $company_provider_id;
  public $num_pax;
  public $cliente;
  public $precio_rank;
  public $precio_neto;
  public $num_recibo;
  public $pick_up_time;
  public $pick_up_place;
  public $comment;
  public $dop_off;

  public $closeForm = false;
  public $columns;
  public $defaultColumns;

  public $sellers = [];
  public $companies = [];
  public $providerServices = [];

  public $modalCustomerOpen = false; // Controla el estado del modal

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected'
  ];

  protected function getModelClass(): string
  {
    return ProviderAndSeller::class;
  }

  public function mount($tripId = null)
  {
    $this->sellers = Seller::where('active', '1')->orderBy('name')->get();
    $this->companies = ProviderCompany::where('active', '1')->orderBy('name')->get();
    $this->providerServices = ServiceProvider::where('active', '1')->orderBy('name')->get();
    $this->refresDatatable();
  }

  private function generateConsecutive()
  {
    // Lógica para generar consecutivo automático
    $lastTrip = ProviderAndSeller::latest()->first();
    $lastId = $lastTrip ? $lastTrip->id : 0;
    return 'TRP-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
  }

  public function render()
  {
    $records = ProviderAndSeller::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.providers-and-sellers.datatable', [
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
  public static function rules($id = null)
  {
    return [
      'fecha_venta' => 'required|date',
      'service_provider_id' => 'required|integer|min:1',
      'seller_id' => 'required|integer|min:1',
      'fecha_servicio' => 'required|date',
      'company_provider_id' => 'required|integer|min:1',
      'num_pax' => 'required|integer|min:1',
      'cliente' => 'required|string|max:150',
      'precio_rank' => 'required|numeric|min:0|decimal:0,2',
      'precio_neto' => 'required|numeric|min:0|decimal:0,2',
      'num_recibo' => 'required|string|max:50',
      'pick_up_time' => 'required|string|max:50',
      'pick_up_place' => 'required|string|max:100',
      'comment' => 'required|string',
      'dop_off' => 'nullable|string|max:100',
    ];
  }

  public static function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'min' => 'El campo :attribute debe ser al menos :min.',
      'string' => 'El campo :attribute debe ser texto.',
      'max' => 'El campo :attribute no debe exceder :max caracteres.',
      'numeric' => 'El campo :attribute debe ser numérico.',
      'decimal' => 'El campo :attribute debe tener hasta 2 decimales.',

      // Mensajes personalizados para campos específicos
      'service_provider_id.required' => 'El proveedor de servicio es obligatorio.',
      'seller_id.required' => 'El vendedor es obligatorio.',
      'company_provider_id.required' => 'La compañía proveedora es obligatoria.',
      'num_pax.min' => 'El número de pasajeros debe ser al menos 1.',
      'precio_rank.min' => 'El precio rank no puede ser negativo.',
      'precio_neto.min' => 'El precio neto no puede ser negativo.',
    ];
  }

  public static function validationAttributes()
  {
    return [
      'fecha_venta' => 'fecha de venta',
      'service_provider_id' => 'proveedor de servicio',
      'seller_id' => 'vendedor',
      'fecha_servicio' => 'fecha de servicio',
      'company_provider_id' => 'compañía proveedora',
      'num_pax' => 'número de pasajeros',
      'cliente' => 'cliente',
      'precio_rank' => 'precio rank',
      'precio_neto' => 'precio neto',
      'num_recibo' => 'número de recibo',
      'pick_up_time' => 'hora de recogida',
      'pick_up_place' => 'lugar de recogida',
      'comment' => 'comentario',
      'dop_off' => 'lugar de destino',
    ];
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate();

    try {
      // Generar consecutivo solo si no se proporcionó
      /*
      if (empty($validatedData['consecutive'])) {
        $validatedData['consecutive'] = DocumentSequenceService::generateTripConsecutivo();
      }
      */

      // Crear el usuario con la contraseña encriptada
      $record = ProviderAndSeller::create($validatedData);

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

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = ProviderAndSeller::find($recordId);
    $this->recordId = $recordId;

    $this->fecha_venta = $record->fecha_venta;
    $this->service_provider_id = $record->service_provider_id;
    $this->seller_id = $record->seller_id;
    $this->fecha_servicio = $record->fecha_servicio;
    $this->company_provider_id = $record->company_provider_id;
    $this->num_pax = $record->num_pax;
    $this->cliente = $record->cliente;
    $this->precio_rank = $record->precio_rank;
    $this->precio_neto = $record->precio_neto;
    $this->num_recibo = $record->num_recibo;
    $this->pick_up_time = $record->pick_up_time;
    $this->pick_up_place = $record->pick_up_place;
    $this->comment = $record->comment;
    $this->dop_off = $record->dop_off;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->dispatch('reinitSelect2Controls');

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validación de los datos de entrada
    $validatedData = $this->validate();
    try {
      // Encuentra el registro existente
      $record = ProviderAndSeller::findOrFail($recordId);

      // Actualiza el usuario
      $record->update($validatedData);

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

  #[On('restore')]
  public function restore($recordId)
  {
    // para restaurar un registro eliminado
    $trip = ProviderAndSeller::withTrashed()->findOrFail($recordId);
    $trip->restore();

    $this->dispatch('show-notification', ['type' => 'success', 'message' => __('El registro se ha restaurado exitosamente')]);
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      //$record = ProviderAndSeller::findOrFail($recordId);
      $trip = ProviderAndSeller::withTrashed()->findOrFail($recordId);

      if ($trip->trashed()) {
        // Eliminación permanente
        $trip->forceDelete();

        $this->selectedIds = array_filter(
          $this->selectedIds,
          fn($selectedId) => $selectedId != $recordId
        );

        // Opcional: limpiar "seleccionar todo" si ya no aplica
        if (empty($this->selectedIds)) {
          $this->selectAll = false;
        }
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('Registro eliminado permanentemente')]);
      } else {
        // Eliminación suave
        $trip->delete();

        $this->selectedIds = array_filter(
          $this->selectedIds,
          fn($selectedId) => $selectedId != $recordId
        );

        // Opcional: limpiar "seleccionar todo" si ya no aplica
        if (empty($this->selectedIds)) {
          $this->selectAll = false;
        }
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('Registro archivado (puede ser restaurado)')]);
      }
      // Emitir actualización
      $this->dispatch('updateSelectedIds', $this->selectedIds);
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
      'fecha_venta',
      'service_provider_id',
      'seller_id',
      'fecha_servicio',
      'company_provider_id',
      'num_pax',
      'cliente',
      'precio_rank',
      'precio_neto',
      'num_recibo',
      'pick_up_time',
      'pick_up_place',
      'comment',
      'dop_off',
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

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'trips-datatable')
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
    'filter_seller' => NULL,
    'filter_fecha_venta' => NULL,
    'filter_company' => NULL,
    'filter_pick_up_place' => NULL,
    'filter_pick_up_time' => NULL,
    'filter_num_pax' => NULL,
    'filter_cliente' => NULL,
    'filter_fecha_servicio' => NULL,
    'filter_num_recibo' => NULL,
    'filter_precio_rank' => NULL,
    'filter_precio_neto' => NULL
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'seller_name',
        'orderName' => 'sellers.name',
        'label' => __('Vendedor'),
        'filter' => 'filter_seller',
        'filter_type' => 'select',
        'filter_sources' => 'sellers',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'left',
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
        'field' => 'fecha_venta',
        'orderName' => 'fecha_venta',
        'label' => __('Fecha de venta'),
        'filter' => 'filter_fecha_venta',
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
        'field' => 'company_name',
        'orderName' => 'providers_companies.name',
        'label' => __('Compañia'),
        'filter' => 'filter_company_name',
        'filter_type' => 'select',
        'filter_sources' => 'companies',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'left',
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
        'field' => 'pick_up_place',
        'orderName' => 'pick_up_place',
        'label' => __('Lugar de recogida'),
        'filter' => 'filter_pick_up_place',
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
        'field' => 'pick_up_time',
        'orderName' => 'pick_up_time',
        'label' => __('Hora de recogida'),
        'filter' => 'filter_pick_up_time',
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
        'field' => 'num_pax',
        'orderName' => 'num_pax',
        'label' => __('Número de pasajeros'),
        'filter' => 'filter_num_pax',
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
        'field' => 'cliente',
        'orderName' => 'cliente',
        'label' => __('Cliente'),
        'filter' => 'filter_cliente',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => 'left',
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
        'field' => 'fecha_servicio',
        'orderName' => 'fecha_servicio',
        'label' => __('Fecha de servicio'),
        'filter' => 'filter_fecha_servicio',
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
        'field' => 'num_recibo',
        'orderName' => 'num_recibo',
        'label' => __('Lugar de entrega'),
        'filter' => 'filter_num_recibo',
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
        'field' => 'precio_rank',
        'orderName' => 'precio_rank',
        'label' => __('Precio Rack'),
        'filter' => 'filter_precio_rank',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
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
        'field' => 'precio_neto',
        'orderName' => 'precio_neto',
        'label' => __('Precio neto'),
        'filter' => 'filter_precio_neto',
        'filter_type' => '',
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
