<?php

namespace App\Livewire\Trips;

use App\Livewire\BaseComponent;
use App\Models\Contact;
use App\Models\DataTableConfig;
use App\Models\Town;
use App\Models\Trip;
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

class TripManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $sortBy = 'trips.id';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Propiedades para el formulario
  public $contact_id;
  public $contact_name;
  public $customer_name;
  public $consecutive;
  public $town_id;
  public $type;
  public $pick_up;
  public $destination;
  public $bill_number;
  public $pax = 1;
  public $rack_price = 0.00;
  public $net_cost = 0.00;
  public $date_service;
  public $others;
  public $status = 'INICIADO';

  public $trip;

  public $closeForm = false;
  public $columns;
  public $defaultColumns;

  public $types = [];
  public $liststatus = [];
  public $customers = [];
  public $towns = [];

  public $modalCustomerOpen = false; // Controla el estado del modal

  protected $listeners = [
    'customerSelected' => 'handleCustomerSelected',
    'openCustomerModal' => 'openCustomerModal',
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected'
  ];

  protected function getModelClass(): string
  {
    return Trip::class;
  }

  public function mount($tripId = null)
  {
    $this->customers = Contact::orderBy('name')->get();
    $this->towns = Town::orderBy('name')->get();
    $this->types = [['id' => 'DIARIO', 'name' => 'DIARIO'], ['id' => 'PRIVADO', 'name' => 'PRIVADO'], ['id' => 'SERVICIOTAXI', 'name' => 'SERVICIO DE TAXI']];
    $this->liststatus = [['id' => 'INICIADO', 'name' => 'INICIADO'], ['id' => 'FINALIZADO', 'name' => 'FINALIZADO'], ['id' => 'ANULADO', 'name' => 'ANULADO']];
    $this->refresDatatable();
  }

  private function generateConsecutive()
  {
    // Lógica para generar consecutivo automático
    $lastTrip = Trip::latest()->first();
    $lastId = $lastTrip ? $lastTrip->id : 0;
    return 'TRP-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
  }

  public function render()
  {
    $records = Trip::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->withTrashed() // Añade esta línea para mostrar los eliminados
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.trips.datatable', [
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
  protected function rules(): array
  {
    return [
      'contact_id' => 'required|integer|exists:contacts,id',
      'contact_name' => 'required|string|max:100',
      'customer_name' => 'required|string|max:100',
      'consecutive' => 'nullable|string|max:100',
      'town_id' => 'required|integer|exists:towns,id',
      'type'         => 'required|in:DIARIO,PRIVADO,SERVICIOTAXI',
      'pick_up' => 'required|string|max:100',
      'destination' => 'required|string|max:254',
      'bill_number' => 'required|string|max:50',
      'pax' => 'required|integer|min:1',
      'rack_price' => 'required|numeric|min:0',
      'net_cost' => 'required|numeric|min:0',
      'date_service' => 'required|date',
      'others' => 'nullable|string|max:100',
      'status' => 'required|in:INICIADO,FINALIZADO,ANULADO'
    ];
  }

  protected function messages(): array
  {
    return [
      'contact_id.required' => 'El cliente es obligatorio.',
      'contact_id.exists' => 'El cliente seleccionado no existe.',
      'customer_name.required' => 'El nombre del cliente es obligatorio.',
      'town_id.required' => 'La ciudad es obligatoria.',
      'town_id.exists' => 'La ciudad seleccionada no existe.',
      'type.required' => 'El tipo de viaje es obligatorio.',
      'pick_up.required' => 'El punto de recogida es obligatorio.',
      'destination.required' => 'El destino es obligatorio.',
      'bill_number.required' => 'El número de factura es obligatorio.',
      'pax.required' => 'El número de pasajeros es obligatorio.',
      'pax.min' => 'Debe haber al menos un pasajero.',
      'rack_price.required' => 'El precio de tarifa es obligatorio.',
      'rack_price.min' => 'El precio de tarifa debe ser positivo.',
      'net_cost.required' => 'El costo neto es obligatorio.',
      'net_cost.min' => 'El costo neto debe ser positivo.',
      'date_service.required' => 'La fecha del servicio es obligatoria.',
      'status.required' => 'El estado es obligatorio.',
    ];
  }

  protected function validationAttributes(): array
  {
    return [
      'contact_id' => 'cliente',
      'customer_name' => 'nombre del cliente',
      'town_id' => 'ciudad',
      'type' => 'tipo de viaje',
      'pick_up' => 'punto de recogida',
      'destination' => 'destino',
      'bill_number' => 'número de factura',
      'pax' => 'pasajeros',
      'rack_price' => 'precio de tarifa',
      'net_cost' => 'costo neto',
      'date_service' => 'fecha de servicio',
      'status' => 'estado',
    ];
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate();

    try {
      // Generar consecutivo solo si no se proporcionó
      if (empty($validatedData['consecutive'])) {
        $validatedData['consecutive'] = DocumentSequenceService::generateTripConsecutivo();
      }

      // Crear el usuario con la contraseña encriptada
      $record = Trip::create($validatedData);

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

    $record = Trip::find($recordId);
    $this->recordId = $recordId;

    $this->contact_id = $record->contact_id;
    $this->contact_name = $record->contact_name;
    $this->customer_name = $record->customer_name;
    $this->consecutive = $record->consecutive;
    $this->town_id = $record->town_id;
    $this->type = $record->type;
    $this->pick_up = $record->pick_up;
    $this->destination = $record->destination;
    $this->bill_number = $record->bill_number;
    $this->pax = $record->pax;
    $this->rack_price = $record->rack_price;
    $this->net_cost = $record->net_cost;
    $this->date_service = $record->date_service->format('Y-m-d');
    $this->others = $record->others;
    $this->status = $record->status;

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
      $record = Trip::findOrFail($recordId);

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
    $trip = Trip::withTrashed()->findOrFail($recordId);
    $trip->restore();

    $this->dispatch('show-notification', ['type' => 'success', 'message' => __('El registro se ha restaurado exitosamente')]);
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      //$record = Trip::findOrFail($recordId);
      $trip = Trip::withTrashed()->findOrFail($recordId);

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
      'contact_id',
      'contact_name',
      'customer_name',
      'consecutive',
      'town_id',
      'type',
      'pick_up',
      'destination',
      'bill_number',
      'pax',
      'rack_price',
      'net_cost',
      'date_service',
      'others',
      'status'
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
    'filter_type' => NULL,
    'filter_status' => NULL,
    'filter_company_name' => NULL,
    'filter_date_service' => NULL,
    'filter_town' => NULL,
    'filter_pick_up' => NULL,
    'filter_destination' => NULL,
    'filter_bill_number' => NULL,
    'filter_pax' => NULL,
    'filter_customer_name' => NULL,
    'filter_rack_price' => NULL,
    'filter_net_cost' => NULL,
    'filter_others' => NULL,
    'filter_consecutive' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'type',
        'orderName' => 'trips.type',
        'label' => __('Tipo'),
        'filter' => 'filter_type',
        'filter_type' => 'select',
        'filter_sources' => 'types',
        'filter_source_field' => 'name',
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
        'field' => 'status',
        'orderName' => 'trips.status',
        'label' => __('Status'),
        'filter' => 'filter_status',
        'filter_type' => 'select',
        'filter_sources' => 'liststatus',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlDeleteStatus',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'company_name',
        'orderName' => 'trips.company_name',
        'label' => __('Compañia'),
        'filter' => 'filter_company_name',
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
        'field' => 'date_service',
        'orderName' => 'trips.date_service',
        'label' => __('Fecha'),
        'filter' => 'filter_date_service',
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
        'field' => 'town_name',
        'orderName' => 'trips.town_id',
        'label' => __('Ciudad'),
        'filter' => 'filter_town',
        'filter_type' => 'select',
        'filter_sources' => 'towns',
        'filter_source_field' => 'name',
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
        'field' => 'pick_up',
        'orderName' => 'trips.pick_up',
        'label' => __('Lugar de recogida'),
        'filter' => 'filter_pick_up',
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
        'field' => 'destination',
        'orderName' => 'trips.destination',
        'label' => __('Lugar de entrega'),
        'filter' => 'filter_destination',
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
        'field' => 'bill_number',
        'orderName' => 'trips.bill_number',
        'label' => __('Número de factura'),
        'filter' => 'filter_bill_number',
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
        'field' => 'pax',
        'orderName' => 'trips.pax',
        'label' => __('# de pasajeros'),
        'filter' => 'filter_pax',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
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
        'field' => 'customer_name',
        'orderName' => 'trips.customer_name',
        'label' => __('Nombre del cliente'),
        'filter' => 'filter_customer_name',
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
        'field' => 'rack_price',
        'orderName' => 'trips.rack_price',
        'label' => __('Precio Rack'),
        'filter' => 'filter_rack_price',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tRackPrice',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'net_cost',
        'orderName' => 'trips.net_cost',
        'label' => __('Costo neto'),
        'filter' => 'filter_net_cost',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tNetCosto',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'others',
        'orderName' => 'trips.others',
        'label' => __('Comentarios'),
        'filter' => 'filter_others',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => 'left',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="text-danger">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'consecutive',
        'orderName' => 'trips.consecutive',
        'label' => __('Consecutivo'),
        'filter' => 'filter_consecutive',
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

  public function handleCustomerSelected($data)
  {
    $this->modalCustomerOpen = false;
    $this->contact_id = $data['customer_id'];
    $this->contact_name = $data['customer_name'];

    $this->dispatch('refreshCleave');
    $this->dispatch('reinitSelect2Controls');
  }

  public function openCustomerModal()
  {
    $this->modalCustomerOpen = true;
  }
}
