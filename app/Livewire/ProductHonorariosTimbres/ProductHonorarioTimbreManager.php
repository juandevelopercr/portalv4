<?php

namespace App\Livewire\ProductHonorariosTimbres;

use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\DataTableConfig;
use App\Models\Honorario;
use App\Models\ProductHonorariosTimbre;
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

class ProductHonorarioTimbreManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'htSearch', history: true)]
  public $search = '';

  #[Url(as: 'htSortBy', history: true)]
  public $sortBy = 'product_honorarios_timbres.description';

  #[Url(as: 'htSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'htPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $product_id;
  public $description;
  public $base;
  public $porcada;
  public $honorario_id;
  public $tabla_abogado_inscripciones;
  public $tabla_abogado_traspasos;
  public $fincascada;
  public $escalonado;
  public $fijo;
  public $tipo;
  public $descuento_timbre;
  public $otro_cheque;
  public $redondear;
  public $ajustar_honorario;
  public $porciento;
  public $monto_manual;
  public $es_impuesto;

  public $closeForm = false;

  public $columns;
  public $defaultColumns;
  public $listActives;

  #[Computed()]
  public function honorarios()
  {
    return Honorario::orderBy('name', 'ASC')->get();
  }

  #[Computed()]
  public function banks()
  {
    return Bank::orderBy('name', 'ASC')->get();
  }

  protected function getModelClass(): string
  {
    return ProductHonorariosTimbre::class;
  }

  public function mount($product_id, $tipo)
  {
    $this->product_id = $product_id;
    $this->tipo = $tipo;
    $this->listActives = [['id' => 1, 'name' => 'Activo'], ['id' => 0, 'name' => 'Inactivo']];
    $this->refresDatatable();
  }

  public function render()
  {
    $records = ProductHonorariosTimbre::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('product_honorarios_timbres.product_id', '=', $this->product_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.products-honorarios-timbres.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->action = 'create';
    $this->dispatch('reinitControls'); // Enviar evento al frontend
    $this->dispatch('scroll-to-top');
  }

  public function store()
  {
    $this->dispatch('reinitControls'); // Reaplica select2 después de cada actualización

    // Validación de los datos de entrada
    $validatedData = $this->validate([
      'product_id' => 'required|exists:products,id',
      'description' => 'required|string|max:200',
      'base' => 'required|numeric|min:0',
      'porcada' => 'required|numeric|min:0',
      'honorario_id' => 'nullable|exists:honorarios,id',
      'tabla_abogado_inscripciones' => 'nullable|boolean',
      'tabla_abogado_traspasos' => 'nullable|boolean',
      'fijo' => 'nullable|boolean',
      'tipo' => 'nullable|in:single,HONORARIO,GASTO',
      'descuento_timbre' => 'nullable|boolean',
      'porciento' => 'nullable|boolean',
      'monto_manual' => 'nullable|boolean',
      'es_impuesto' => 'nullable|boolean',
    ], [
      'required' => 'El campo :attribute es obligatorio.',
      'exists' => 'El valor seleccionado para :attribute no es válido.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'numeric' => 'El campo :attribute debe ser un valor numérico.',
      'in' => 'El valor seleccionado para :attribute no es válido.',
    ], [
      'product_id' => 'producto',
      'description' => 'descripción',
      'base' => 'base',
      'porcada' => 'por cada',
      'honorario_id' => 'honorario',
      'tabla_abogado_inscripciones' => 'tabla de abogado inscripciones',
      'tabla_abogado_traspasos' => 'tabla de abogado traspasos',
      'fijo' => 'fijo',
      'tipo' => 'tipo',
      'descuento_timbre' => 'descuento de timbre',
      'porciento' => 'porcentaje',
      'monto_manual' => 'monto manual',
      'es_impuesto' => 'es impuesto'
    ]);

    try {

      // Crear el usuario con la contraseña encriptada
      $record = ProductHonorariosTimbre::create([
        'product_id'                    => $validatedData['product_id'],
        'description'                   => $validatedData['description'],
        'base'                          => $validatedData['base'],
        'porcada'                       => $validatedData['porcada'],
        'honorario_id'                  => $validatedData['honorario_id'],
        'tabla_abogado_inscripciones'   => $validatedData['tabla_abogado_inscripciones'] ?? 0,
        'tabla_abogado_traspasos'       => $validatedData['tabla_abogado_traspasos'] ?? 0,
        'fijo'                          => $validatedData['fijo'] ?? 0,
        'tipo'                          => $validatedData['tipo'] ?? 0,
        'descuento_timbre'              => $validatedData['descuento_timbre'] ?? 0,
        'porciento'                     => $validatedData['porciento'] ?? 0,
        'monto_manual'                  => $validatedData['monto_manual'] ?? 0,
        'es_impuesto'                   => $validatedData['es_impuesto'] ?? 0,
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

    $record = ProductHonorariosTimbre::find($recordId);
    $this->recordId = $recordId;

    $this->product_id                    = $record->product_id;
    $this->description                   = $record->description;
    $this->base                          = $record->base;
    $this->porcada                       = $record->porcada;
    $this->honorario_id                  = $record->honorario_id;
    $this->tabla_abogado_inscripciones   = $record->tabla_abogado_inscripciones;
    $this->tabla_abogado_traspasos       = $record->tabla_abogado_traspasos;
    $this->fincascada                    = $record->fincascada;
    $this->escalonado                    = $record->escalonado;
    $this->fijo                          = $record->fijo;
    $this->tipo                          = $record->tipo;
    $this->descuento_timbre              = $record->descuento_timbre;
    $this->otro_cheque                   = $record->otro_cheque;
    $this->redondear                     = $record->redondear;
    $this->ajustar_honorario             = $record->ajustar_honorario;
    $this->porciento                     = $record->porciento;
    $this->monto_manual                  = $record->monto_manual;
    $this->es_impuesto                   = $record->es_impuesto;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
    $this->dispatch('reinitControls'); // Enviar evento al frontend
  }

  public function update()
  {
    $this->dispatch('reinitControls'); // Reaplica select2 después de cada actualización
    $recordId = $this->recordId;

    // Valida los datos
    $validatedData = $this->validate([
      'product_id' => 'required|exists:products,id',
      'description' => 'required|string|max:200',
      'base' => 'required|numeric|min:0',
      'porcada' => 'required|numeric|min:0',
      'honorario_id' => 'nullable|exists:honorarios,id',
      'tabla_abogado_inscripciones' => 'nullable|boolean',
      'tabla_abogado_traspasos' => 'nullable|boolean',
      'fijo' => 'nullable|boolean',
      'tipo' => 'nullable|in:single,HONORARIO,GASTO',
      'descuento_timbre' => 'nullable|boolean',
      'porciento' => 'nullable|boolean',
      'monto_manual' => 'nullable|boolean',
      'es_impuesto' => 'nullable|boolean',
    ], [
      'required' => 'El campo :attribute es obligatorio.',
      'exists' => 'El valor seleccionado para :attribute no es válido.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'numeric' => 'El campo :attribute debe ser un valor numérico.',
      'in' => 'El valor seleccionado para :attribute no es válido.',
    ], [
      'product_id' => 'producto',
      'description' => 'descripción',
      'base' => 'base',
      'porcada' => 'por cada',
      'honorario_id' => 'honorario',
      'tabla_abogado_inscripciones' => 'tabla de abogado inscripciones',
      'tabla_abogado_traspasos' => 'tabla de abogado traspasos',
      'fijo' => 'fijo',
      'tipo' => 'tipo',
      'descuento_timbre' => 'descuento de timbre',
      'porciento' => 'porcentaje',
      'monto_manual' => 'monto manual',
      'es_impuesto' => 'es impuesto'
    ]);

    try {
      // Encuentra el registro existente
      $record = ProductHonorariosTimbre::findOrFail($recordId);

      // Actualiza el usuario
      $record->update([
        'product_id'                    => $validatedData['product_id'],
        'description'                   => $validatedData['description'],
        'base'                          => $validatedData['base'],
        'porcada'                       => $validatedData['porcada'],
        'honorario_id'                  => $validatedData['honorario_id'],
        'tabla_abogado_inscripciones'   => $validatedData['tabla_abogado_inscripciones'] ?? 0,
        'tabla_abogado_traspasos'       => $validatedData['tabla_abogado_traspasos'] ?? 0,
        'fijo'                          => $validatedData['fijo'] ?? 0,
        'tipo'                          => $validatedData['tipo'] ?? 0,
        'descuento_timbre'              => $validatedData['descuento_timbre'] ?? 0,
        'porciento'                     => $validatedData['porciento'] ?? 0,
        'monto_manual'                  => $validatedData['monto_manual'] ?? 0,
        'es_impuesto'                   => $validatedData['es_impuesto'] ?? 0,
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
      $record = ProductHonorariosTimbre::findOrFail($recordId);

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
      'description',
      'base',
      'porcada',
      'honorario_id',
      'tabla_abogado_inscripciones',
      'tabla_abogado_traspasos',
      'fincascada',
      'escalonado',
      'fijo',
      'descuento_timbre',
      'otro_cheque',
      'redondear',
      'ajustar_honorario',
      'porciento',
      'monto_manual',
      'es_impuesto',
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

  public function updated($property)
  {
    // $property: The name of the current property that was updated
    if ($property === 'honorario_id' || $property === 'tipo') {
      $this->dispatch('reinitControls'); // Reaplica select2 después de cada actualización
    }
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'product-honorarios-timbres-datatable')
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
    'filter_description' => NULL,
    'filter_base' => NULL,
    'filter_porcada' => NULL,
    'filter_inscripciones' => NULL,
    'filter_traspasos' => NULL,
    'filter_honorarios' => NULL,
    'filter_fijo' => NULL,
    'filter_porciento' => NULL,
    'filter_timbre' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'description',
        'orderName' => 'product_honorarios_timbres.description',
        'label' => __('Description'),
        'filter' => 'filter_description',
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
        'width' => '15%',
        'visible' => true,
      ],
      [
        'field' => 'base',
        'orderName' => 'base',
        'label' => __('Base'),
        'filter' => 'filter_base',
        'filter_type' => 'input',
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
        'field' => 'porcada',
        'orderName' => 'porcada',
        'label' => __('Por Cada'),
        'filter' => 'filter_porcada',
        'filter_type' => 'input',
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
        'field' => 'tabla_abogado_inscripciones',
        'orderName' => 'tabla_abogado_inscripciones',
        'label' => __('Timbre Abogados Bienes Inmuebles'),
        'filter' => 'filter_inscripciones',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => ['tabla_abogado_inscripciones'],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => '80px',
        'visible' => true,
      ],
      [
        'field' => 'tabla_abogado_traspasos',
        'orderName' => 'tabla_abogado_traspasos',
        'label' => __('Timbre Abogados Bienes Muebles'),
        'filter' => 'filter_traspasos',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => ['tabla_abogado_traspasos'],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'honorario_id',
        'orderName' => 'honorario_id',
        'label' => __('Honorario'),
        'filter' => 'filter_honorarios',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => ['honorario_name'],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'fijo',
        'orderName' => 'fijo',
        'label' => __('Fijo'),
        'filter' => 'filter_fijo',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => ['fijo'],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'porciento',
        'orderName' => 'porciento',
        'label' => __('Percent'),
        'filter' => 'filter_porciento',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => ['porciento'],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'descuento_timbre',
        'orderName' => 'descuento_timbre',
        'label' => __('Discount'),
        'filter' => 'filter_timbre',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => ['descuento_timbre'],
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
