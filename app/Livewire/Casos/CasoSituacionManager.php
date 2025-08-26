<?php

namespace App\Livewire\Casos;

use App\Livewire\BaseComponent;
use App\Models\CasoSituacion;
use App\Models\DataTableConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CasoSituacionManager extends BaseComponent
{
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'fecha';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public string $action = 'list';
  public $recordId = '';

  public $closeForm = false;
  public $columns;
  public $defaultColumns;

  public $caso_id;
  public $name;
  public $responsable;
  public $fecha;
  public $tipo;
  public $estado;

  public $estados = [['id' => 'PENDIENTE', 'name' => 'PENDIENTE'], ['id' => 'DEFECTUOSO', 'name' => 'DEFECTUOSO']];

  public $filters = [
    'filter_name' => NULL,
    'filter_responsable' => NULL,
    'filter_fecha' => NULL,
    'filter_estado' => NULL,
  ];

  public function mount($caso_id, $tipo)
  {
    $this->caso_id = $caso_id;
    $this->tipo = $tipo;
    $this->refresDatatable();
  }

  protected function getModelClass(): string
  {
    return CasoSituacion::class;
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'caso-situaciones-datatable')
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

  public function rules(): array
  {
    return [
      'caso_id' => ['required', 'exists:casos,id'],
      'name' => ['required', 'string', 'max:255'],
      'responsable' => ['required', 'string', 'max:150'],
      'fecha' => ['required', 'date'],
      'tipo' => ['required', 'in:PENDIENTE,DEFECTUOSO'],
      'estado' => ['required', 'in:PENDIENTE,CUMPLIDO'],
    ];
  }

  public function messages(): array
  {
    return [
      'caso_id.required' => 'Debe seleccionar un caso.',
      'caso_id.exists' => 'El caso seleccionado no existe.',
      'name.required' => 'El nombre es obligatorio.',
      'responsable.required' => 'Debe indicar el responsable.',
      'fecha.date' => 'La fecha no tiene un formato válido.',
      'tipo.in' => 'El tipo debe ser PENDIENTE o DEFECTUOSO.',
      'estado.in' => 'El estado debe ser PENDIENTE o CUMPLIDO.',
    ];
  }

  public function attributes(): array
  {
    return [
      'caso_id' => 'caso',
      'name' => 'nombre',
      'responsable' => 'responsable',
      'fecha' => 'fecha',
      'tipo' => 'tipo de pendiente',
      'estado' => 'estado del pendiente',
    ];
  }

  public function create()
  {
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $today = Carbon::now()->toDateString();
    // Convertir a formato d-m-Y para mostrar en el input
    $this->fecha = Carbon::parse($today)->format('d-m-Y');
    $this->action = 'create';
    $this->active = 1;

    $this->dispatch('scroll-to-top');
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate();

    $validatedData['fecha'] = Carbon::parse($this->fecha)->format('Y-m-d');

    try {
      // Crear el usuario con la contraseña encriptada
      $record = CasoSituacion::create($validatedData);

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

    $record = CasoSituacion::find($recordId);
    $this->recordId = $recordId;

    $this->name = $record->name;
    $this->responsable = $record->responsable;
    $this->tipo = $record->tipo;
    $this->fecha = Carbon::parse($record->fecha)->format('d-m-Y');
    $this->estado = $record->estado;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';

    $this->dispatch('select2');
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validación de los datos de entrada
    $validatedData = $this->validate();

    $validatedData['fecha'] = Carbon::parse($this->fecha)->format('Y-m-d');

    try {
      // Encuentra el registro existente
      $record = CasoSituacion::findOrFail($recordId);

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

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $record = CasoSituacion::findOrFail($recordId);

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
      'responsable',
      'fecha',
      'estado'
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

  public function getDefaultColumns(): array
  {
    $this->defaultColumns = [
      [
        'field' => 'name',
        'orderName' => 'name',
        'label' => __('Description'),
        'filter' => 'filter_name',
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
        'field' => 'responsable',
        'orderName' => 'responsable',
        'label' => __('Responsable'),
        'filter' => 'filter_responsable',
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
        'field' => 'fecha',
        'orderName' => 'fecha',
        'label' => __('Fecha'),
        'filter' => 'filter_fecha',
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
        'field' => 'estado',
        'orderName' => 'estado',
        'label' => __('Status'),
        'filter' => 'filter_estado',
        'filter_type' => 'select',
        'filter_sources' => 'estados',
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
      ]
    ];

    return $this->defaultColumns;
  }

  protected function getFilteredQuery()
  {
    $query = CasoSituacion::search($this->search, $this->filters)
      ->where('tipo', $this->tipo)
      ->where('caso_id', $this->caso_id);

    return $query;
  }

  public function render()
  {
    $query = $this->getFilteredQuery();

    // Ordenamiento y paginación final
    $records = $query
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.casos.caso-situacion-datatable', [
      'records' => $records,
    ]);
  }

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
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
}
