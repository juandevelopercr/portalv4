<?php

namespace App\Livewire\Clasificadores\HonorariosRecetas;

use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\DataTableConfig;
use App\Models\HonorarioReceta;
use Barryvdh\DomPDF\Facade\Pdf;
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

class HonorarioRecetaManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'hbSearch', history: true)]
  public $search = '';

  #[Url(as: 'hbSortBy', history: true)]
  public $sortBy = 'orden';

  #[Url(as: 'hbSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'hbPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $honorario_id;
  public $bank_id;
  public $desde;
  public $hasta;
  public $porcentaje;
  public $tipo;
  public $orden;

  public $closeForm = false;

  public $columns;
  public $defaultColumns;

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
  ];

  #[Computed()]
  public function banks()
  {
    return Bank::orderBy('name', 'ASC')->get();
  }

  protected function getModelClass(): string
  {
    return HonorarioReceta::class;
  }

  public function mount($honorario_id)
  {
    $this->honorario_id = $honorario_id;
    $this->refresDatatable();
  }

  public function render()
  {
    $records = HonorarioReceta::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('honorarios_recetas.honorario_id', '=', $this->honorario_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.clasificadores.honorarios-recetas.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    $this->dispatch('scroll-to-top');
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate([
      'honorario_id' => 'required|integer|exists:honorarios,id',
      'desde'        => 'required|numeric|min:0|max:999999999999999.99999',
      'hasta'        => 'nullable|numeric|min:0|max:999999999999999.99999',
      'porcentaje'   => 'required|numeric|min:0|max:100',
      'orden'        => 'nullable|integer|min:0',
    ], [
      'required'               => 'El campo :attribute es obligatorio.',
      'integer'                => 'El campo :attribute debe ser un número entero.',
      'numeric'                => 'El campo :attribute debe ser un número válido.',
      'exists'                 => 'El :attribute seleccionado no es válido.',
      'in'                     => 'El valor del campo :attribute no es válido.',
      'desde.min'              => 'El campo :attribute debe ser al menos 0.',
      'hasta.min'              => 'El campo :attribute debe ser al menos 0.',
      'porcentaje.min'         => 'El campo :attribute debe ser al menos 0.',
      'porcentaje.max'         => 'El campo :attribute no puede superar 100.',
    ], [
      'honorario_id' => 'honorario',
      'desde'        => 'valor desde',
      'hasta'        => 'valor hasta',
      'porcentaje'   => 'porcentaje',
      'orden'        => 'orden',
    ]);

    try {

      // Crear el usuario con la contraseña encriptada
      $record = HonorarioReceta::create([
        'honorario_id' => $validatedData['honorario_id'],
        'desde'        => $validatedData['desde'],
        'hasta'        => $validatedData['hasta'] ?? 0.00000, // Valor por defecto
        'porcentaje'   => $validatedData['porcentaje'],
        'orden'        => $validatedData['orden'] ?? null,    // Campo opcional
      ]);

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

    $record = HonorarioReceta::find($recordId);
    $this->recordId = $recordId;

    $this->honorario_id = $record->honorario_id;
    $this->desde        = $record->desde;
    $this->hasta        = $record->hasta;
    $this->porcentaje   = $record->porcentaje;
    $this->orden        = $record->orden;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Valida los datos
    $validatedData = $this->validate([
      'honorario_id' => 'required|integer|exists:honorarios,id',
      'desde'        => 'required|numeric|min:0|max:999999999999999.99999',
      'hasta'        => 'nullable|numeric|min:0|max:999999999999999.99999',
      'porcentaje'   => 'required|numeric|min:0|max:100',
      'orden'        => 'nullable|integer|min:0',
    ], [
      'required'               => 'El campo :attribute es obligatorio.',
      'integer'                => 'El campo :attribute debe ser un número entero.',
      'numeric'                => 'El campo :attribute debe ser un número válido.',
      'exists'                 => 'El :attribute seleccionado no es válido.',
      'in'                     => 'El valor del campo :attribute no es válido.',
      'desde.min'              => 'El campo :attribute debe ser al menos 0.',
      'hasta.min'              => 'El campo :attribute debe ser al menos 0.',
      'porcentaje.min'         => 'El campo :attribute debe ser al menos 0.',
      'porcentaje.max'         => 'El campo :attribute no puede superar 100.',
    ], [
      'honorario_id' => 'honorario',
      'desde'        => 'valor desde',
      'hasta'        => 'valor hasta',
      'porcentaje'   => 'porcentaje',
      'orden'        => 'orden',
    ]);

    try {
      // Encuentra el registro existente
      $record = HonorarioReceta::findOrFail($recordId);

      // Actualiza el usuario
      $record->update([
        'honorario_id' => $validatedData['honorario_id'],
        'desde'        => $validatedData['desde'],
        'hasta'        => $validatedData['hasta'] ?? 0.00000, // Valor por defecto
        'porcentaje'   => $validatedData['porcentaje'],
        'orden'        => $validatedData['orden'] ?? null,    // Campo opcional
      ]);

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
      $record = HonorarioReceta::findOrFail($recordId);

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
      'desde',
      'hasta',
      'porcentaje',
      'orden',
      'closeForm',
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

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'classifier-honorarios-recetas-datatable')
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
    'filter_name' => NULL,
    'filter_desde' => NULL,
    'filter_hasta' => NULL,
    'filter_percent' => NULL,
    'filter_orden' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'desde',
        'orderName' => 'desde',
        'label' => __('From'),
        'filter' => 'filter_desde',
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
        'field' => 'hasta',
        'orderName' => 'hasta',
        'label' => __('Until'),
        'filter' => 'filter_hasta',
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
        'field' => 'porcentaje',
        'orderName' => 'porcentaje',
        'label' => __('Percent'),
        'filter' => 'filter_percent',
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
        'field' => 'orden',
        'orderName' => 'orden',
        'label' => __('Order'),
        'filter' => 'filter_orden',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'integer',
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
