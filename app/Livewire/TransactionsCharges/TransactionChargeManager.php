<?php

namespace App\Livewire\TransactionsCharges;

use App\Models\AdditionalChargeType;
use App\Models\DataTableConfig;
use App\Models\IdentificationType;
use App\Models\Transaction;
use App\Models\TransactionOtherCharge;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

class TransactionChargeManager extends Component
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'chtSearch', history: true)]
  public $search = '';

  #[Url(as: 'chtSortBy', history: true)]
  public $sortBy = 'transactions_other_charges.id';

  #[Url(as: 'chtSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'chtPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Variables públicas
  public $transaction_id;
  public $additional_charge_type_id;
  public $additional_charge_other;
  public $third_party_identification_type;
  public $third_party_identification;
  public $third_party_name;
  public $detail;
  public $percent;
  public $quantity;
  public $amount;

  public $closeForm = false;

  //Listados
  public $chargeTypes = [];
  public $identificationTypes;

  public $total = 0;

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
    return TransactionOtherCharge::class;
  }

  #[On('updateTransactionContext')]
  public function handleUpdateContext($data)
  {
    $this->transaction_id = $data['transaction_id'];
    // Aquí puedes recargar los datos si es necesario
  }

  public function mount($transaction_id, $canview, $cancreate, $canedit, $candelete, $canexport)
  {
    // Intentar obtener de sesión primero
    if (session()->has('transaction_context')) {
      $this->handleUpdateContext(session()->get('transaction_context'));
    }

    $this->transaction_id = $transaction_id;

    $this->chargeTypes = AdditionalChargeType::orderBy('code', 'ASC')->get();
    $this->identificationTypes = IdentificationType::orderBy('code', 'ASC')->get();

    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;

    $this->refresDatatable();
  }

  public function render()
  {
    $records = TransactionOtherCharge::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('transaction_id', '=', $this->transaction_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.transactions-charges.datatable', [
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

    $transaction = Transaction::with('otherCharges')->find($this->transaction_id);
    if (count($transaction->otherCharges) >= 15) {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('You have exceeded the maximum number of charges allowed. Only up to 15 charges are allowed')]);
    }

    $this->additional_charge_type_id = 99;
    $this->quantity = 1;

    $this->action = 'create';
    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    return [
      'transaction_id' => 'required|exists:transactions,id',
      'additional_charge_type_id' => 'required|exists:additional_charge_types,id',
      'additional_charge_other' => 'nullable|required_if:additional_charge_type_id,99|string|max:100',
      'third_party_identification_type' => 'nullable|required_if:additional_charge_type_id,4|string|size:2',
      'third_party_identification' => 'nullable|required_if:additional_charge_type_id,4|string|max:20',
      'third_party_name' => 'nullable|required_if:additional_charge_type_id,4|string|max:100',
      'detail' => 'required|string|max:160',
      'percent' => 'nullable|numeric|min:0|max:100',
      'quantity' => 'required|numeric|min:1|max:999999999999',
      'amount' => 'required|numeric|min:0.00001|max:999999999999.99999',
    ];
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'transaction_id.required' => 'El campo ID de transacción es obligatorio.',
      'transaction_id.exists' => 'La transacción seleccionada no existe.',

      'additional_charge_type_id.required' => 'El tipo de cargo adicional es obligatorio.',
      'additional_charge_type_id.exists' => 'El tipo de cargo adicional no es válido.',

      'additional_charge_other.required_if' => 'El campo "Otro" es obligatorio cuando el tipo de cargo es "Otros".',
      'additional_charge_other.max' => 'El campo "Otro" no debe exceder los 100 caracteres.',

      'third_party_identification_type.required_if' => 'El tipo de identificación de terceros es obligatorio cuando el tipo de cargo es cobro de un tercero.',
      'third_party_identification_type.size' => 'El tipo de identificación debe tener 2 caracteres.',

      'third_party_identification.required_if' => 'El campo identificación de terceros es obligatorio cuando el tipo de cargo es cobro de un tercero.',
      'third_party_identification.max' => 'La identificación de terceros no debe exceder los 20 caracteres.',

      'third_party_name.required_if' => 'El campo nombre de tercero es obligatorio cuando el tipo de cargo es cobro de un tercero.',
      'third_party_name.max' => 'El nombre del tercero no debe exceder los 100 caracteres.',

      'detail.required' => 'El campo detalle es obligatorio.',
      'detail.max' => 'El detalle no debe exceder los 160 caracteres.',

      'percent.numeric' => 'El campo porcentaje debe ser un valor numérico.',
      'percent.min' => 'El porcentaje no puede ser menor que 0.',
      'percent.max' => 'El porcentaje no puede exceder el 100%.',

      'quantity.required' => 'La cantidad es obligatorio.',
      'quantity.numeric' => 'La cantidad debe ser un valor numérico.',
      'quantity.min' => 'La cantidad debe ser mayor que cero.',
      'quantity.max' => 'La cantidad no puede exceder el límite permitido.',

      'amount.required' => 'El monto es obligatorio.',
      'amount.numeric' => 'El monto debe ser un valor numérico.',
      'amount.min' => 'El monto debe ser mayor que cero.',
      'amount.max' => 'El monto no puede exceder el límite permitido.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    return [
      'transaction_id' => 'ID de transacción',
      'additional_charge_type_id' => 'tipo de cargo adicional',
      'additional_charge_other' => 'otro cargo adicional',
      'third_party_identification_type' => 'tipo de identificación de terceros',
      'third_party_identification' => 'identificación de terceros',
      'third_party_name' => 'nombre del tercero',
      'detail' => 'detalle',
      'percent' => 'porcentaje',
      'quantity' => 'cantidad',
      'amount' => 'monto',
    ];
  }

  public function store()
  {
    // Validar
    $validatedData = $this->validate();

    try {

      // Crear el usuario con la contraseña encriptada
      $record = TransactionOtherCharge::create($validatedData);

      $closeForm = $this->closeForm;

      $this->dispatch('chargeUpdated', $record->transaction_id);  // Emitir evento para otros componentes

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

    $record = TransactionOtherCharge::find($recordId);
    $this->recordId = $recordId;

    // Asignar valores del registro a las variables públicas
    $this->transaction_id = $record->transaction_id;
    $this->additional_charge_type_id = $record->additional_charge_type_id;
    $this->additional_charge_other = $record->additional_charge_other;
    $this->third_party_identification_type = $record->third_party_identification_type;
    $this->third_party_identification = $record->third_party_identification;
    $this->third_party_name = $record->third_party_name;
    $this->detail = $record->detail;
    $this->percent = $record->percent;
    $this->quantity = $record->quantity;
    $this->amount = $record->amount;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validar
    $validatedData = $this->validate();

    try {
      // Encuentra el registro existente
      $record = TransactionOtherCharge::findOrFail($recordId);

      // Actualiza el registro
      $record->update($validatedData);

      $closeForm = $this->closeForm;

      $this->dispatch('chargeUpdated', $record->transaction_id);  // Emitir evento para otros componentes

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
    //Log::debug('confirmarAccion ANTES', ['recordId' => $recordId, 'metodo' => $metodo, 'selectedIds' => $this->selectedIds]);

    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    Log::debug('confirmarAccion DESPUES', ['recordId' => $recordId, 'metodo' => $metodo, 'selectedIds' => $this->selectedIds]);

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
      $record = TransactionOtherCharge::findOrFail($recordId);

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

        // ✅ Eliminar el ID del array de seleccionados si estaba presente
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

        //dd($this->selectedIds);
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
      'additional_charge_type_id',
      'additional_charge_other',
      'third_party_identification_type',
      'third_party_identification',
      'third_party_name',
      'detail',
      'percent',
      'quantity',
      'amount',
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
    'filter_additional_charge_types' => NULL,
    'filter_detail' => NULL,
    'filter_quantity' => NULL,
    'filter_amount' => NULL,
    'filter_total' => NULL,
    'filter_third_party_name' => NULL,
    'filter_third_party_identification_type' => NULL,
    'filter_third_party_identification' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'charge_name',
        'orderName' => 'additional_charge_types.name',
        'label' => __('Charge Type'),
        'filter' => 'filter_additional_charge_types',
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
        'label' => __('Detail'),
        'filter' => 'filter_additional_charge_types',
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
        'field' => 'amount',
        'orderName' => 'amount',
        'label' => __('Amount'),
        'filter' => 'filter_amount',
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
        'field' => '',
        'orderName' => '',
        'label' => __('Total'),
        'filter' => 'filter_total',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlTotal',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'third_party_name',
        'orderName' => 'third_party_name',
        'label' => __('Third Party Name'),
        'filter' => 'filter_third_party_name',
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
        'field' => 'third_party_identification_type',
        'orderName' => 'third_party_identification_type',
        'label' => __('Third Party Identification'),
        'filter' => 'filter_third_party_identification_type',
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
        'field' => 'third_party_identification',
        'orderName' => 'third_party_identification',
        'label' => __('Third Party Identification'),
        'filter' => 'filter_third_party_identification',
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
