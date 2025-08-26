<?php

namespace App\Livewire\TransactionsCommissions;

use App\Livewire\BaseComponent;
use App\Models\CentroCosto;
use App\Models\DataTableConfig;
use App\Models\TransactionCommission;
use App\Models\User;
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

class TransactionCommissionManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'ctSearch', history: true)]
  public $search = '';

  #[Url(as: 'ctSortBy', history: true)]
  public $sortBy = 'transactions_commissions.id';

  #[Url(as: 'ctSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'ctPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Variables públicas
  public $transaction_id;
  public $centro_costo_id;
  public $abogado_encargado;
  public $comisionista_id;
  public $percent;
  public $commission_percent;
  public $comision_pagada;
  public $comision_pagada_date;

  //Listados
  public $centrosCostos = [];
  public $comisionistas = [];

  public $closeForm = false;

  public $columns;
  public $defaultColumns;

  public $canview;
  public $cancreate;
  public $canedit;
  public $candelete;
  public $canexport;

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
  ];

  protected function getModelClass(): string
  {
    return TransactionCommission::class;
  }

  #[On('updateTransactionContext')]
  public function handleUpdateContext($data)
  {
    $this->transaction_id = $data['transaction_id'];
    // Aquí puedes recargar los datos si es necesario
  }

  public function mount($transaction_id, $canview, $cancreate, $canedit, $candelete, $canexport)
  {
    $this->transaction_id = $transaction_id;
    $this->centrosCostos = CentroCosto::orderBy('codigo', 'ASC')->get();
    $this->comisionistas = User::whereHas('roles', function ($query) {
      $query->where('name', 'Abogado');
    })->orderBy('name', 'ASC')->get();

    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;

    $this->refresDatatable();
  }

  public function render()
  {
    $records = TransactionCommission::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('transaction_id', '=', $this->transaction_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.transactions-commissions.datatable', [
      'records' => $records,
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
    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    return [
      'transaction_id' => 'required|exists:transactions,id',
      'centro_costo_id' => 'required|exists:centro_costos,id',
      'abogado_encargado' => 'required|string|max:100',
      'comisionista_id' => 'nullable|exists:users,id',
      'percent' => 'required|numeric|min:0.01|max:100',
      'commission_percent' => 'nullable|numeric|min:0.01|max:100',
      'comision_pagada' => 'nullable|boolean',
      'comision_pagada_date' => 'nullable|date',
      //'comision_pagada_date' => 'nullable|date|after_or_equal:today',
    ];
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'exists' => 'El :attribute seleccionado no es válido.',
      'string' => 'El campo :attribute debe ser texto.',
      'max' => 'El campo :attribute no debe tener más de :max caracteres.',
      'numeric' => 'El campo :attribute debe ser un número.',
      'min' => 'El valor de :attribute debe ser al menos :min.',
      'max' => 'El valor de :attribute no puede ser mayor que :max.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'after_or_equal' => 'La fecha de :attribute debe ser hoy o posterior.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    return [
      'transaction_id' => 'ID de transacción',
      'centro_costo_id' => 'Centro de costo',
      'abogado_encargado' => 'Abogado encargado',
      'comisionista_id' => 'Comisionista',
      'percent' => 'Porciento de Participación',
      'commission_percent' => 'Porciento de comisión',
      'comision_pagada' => 'Comisión pagada',
      'comision_pagada_date' => 'Fecha de comisión pagada',
    ];
  }

  public function store()
  {
    // Validar
    $validatedData = $this->validate();

    $sumaPercent = TransactionCommission::where('transaction_id', $this->transaction_id)
      ->where('id', '!=', $this->recordId ?? 0)
      ->sum('percent');

    if (($sumaPercent + $validatedData['percent']) > 100) {
      $this->addError('percent', __('The sum of the participation percentages cannot exceed 100%'));
      return;
    }

    // Asignar 0 si es null
    $validatedData['commission_percent'] = !empty($validatedData['commission_percent']) ? $validatedData['commission_percent'] : null;
    $validatedData['comision_pagada'] = !empty($validatedData['comision_pagada']) ? $validatedData['comision_pagada'] : 0;

    try {

      // Crear el usuario con la contraseña encriptada
      $record = TransactionCommission::create($validatedData);

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

    $record = TransactionCommission::find($recordId);
    $this->recordId = $recordId;

    // Asignar valores del registro a las variables públicas
    $this->transaction_id = $record->transaction_id;
    $this->transaction_id = $record->transaction_id;
    $this->centro_costo_id = $record->centro_costo_id;
    $this->abogado_encargado = $record->abogado_encargado;
    $this->comisionista_id = $record->comisionista_id;
    $this->percent = $record->percent;
    $this->commission_percent = $record->commission_percent;
    $this->comision_pagada = $record->comision_pagada;
    $this->comision_pagada_date = $record->comision_pagada_date;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validar
    $validatedData = $this->validate();

    $sumaPercent = TransactionCommission::where('transaction_id', $this->transaction_id)
      ->where('id', '!=', $this->recordId ?? 0)
      ->sum('percent');

    if (($sumaPercent + $validatedData['percent']) > 100) {
      $this->addError('percent', __('The sum of the participation percentages cannot exceed 100%'));
      return;
    }

    // Asignar 0 si es null
    $validatedData['commission_percent'] = !empty($validatedData['commission_percent']) ? $validatedData['commission_percent'] : null;
    $validatedData['comision_pagada'] = !empty($validatedData['comision_pagada']) ? $validatedData['comision_pagada'] : 0;

    try {
      // Encuentra el registro existente
      $record = TransactionCommission::findOrFail($recordId);

      // Actualiza el registro
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
      $record = TransactionCommission::findOrFail($recordId);

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

        unset($this->selectedIds[$recordId]);
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
      'centro_costo_id',
      'abogado_encargado',
      'comisionista_id',
      'percent',
      'commission_percent',
      'comision_pagada',
      'comision_pagada_date',
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

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'proformas-commisions-datatable')
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
    'filter_descrip' => NULL,
    'filter_abogado_encargado' => NULL,
    'filter_percent' => NULL,
    'filter_comisionista' => NULL,
    'filter_commision_percent' => NULL,
    'filter_distribution_amount' => NULL,
    'filter_monto_distribucion' => NULL,
    'filter_comision_pagada' => NULL,
    'filter_fechacomision_pagada' => NULL,
    'filter_monto_pagar' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'descrip',
        'orderName' => 'centro_costos.descrip',
        'label' => __('Centro de Costo'),
        'filter' => 'filter_descrip',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlcolumnName',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'abogado_encargado',
        'orderName' => 'abogado_encargado',
        'label' => __('Abogado a Cargo'),
        'filter' => 'filter_abogado_encargado',
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
        'field' => 'percent',
        'orderName' => 'percent',
        'label' => __('Porciento de Participación'),
        'filter' => 'filter_percent',
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
        'field' => 'comisionista',
        'orderName' => 'comisionista',
        'label' => __('Comisionista'),
        'filter' => 'filter_comisionista',
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
        'field' => 'commission_percent',
        'orderName' => 'commission_percent',
        'label' => __('Porciento de Comisión'),
        'filter' => 'filter_commision_percent',
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
        'field' => '',
        'orderName' => '',
        'label' => __('Monto de Distribución'),
        'filter' => 'filter_distribution_amount',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => 'calculateDistributionAmount',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => '',
        'orderName' => '',
        'label' => __('Comisión Pagada'),
        'filter' => 'filter_comision_pagada',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnComisionPagada',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'comision_pagada_date',
        'orderName' => 'comision_pagada_date',
        'label' => __('Fecha Pago Comisión'),
        'filter' => 'filter_fechacomision_pagada',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
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
        'field' => '',
        'orderName' => '',
        'label' => __('Monto a Pagar'),
        'filter' => 'filter_monto_pagar',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => 'calculateAmountToPay',
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
