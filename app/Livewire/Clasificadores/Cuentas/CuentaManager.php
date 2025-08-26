<?php

namespace App\Livewire\Clasificadores\Cuentas;

use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\BusinessLocation;
use App\Models\Cuenta;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Department;
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

class CuentaManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $sortBy = 'cuentas.nombre_cuenta';

  #[Url(history: true)]
  public $sortDir = 'ASC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public string $numero_cuenta = '';
  public string $nombre_cuenta = '';
  public int $moneda_id;
  public float $balance = 0.0;
  public float $saldo = 0.0;
  public ?string $ultimo_cheque = null;

  public bool $mostrar_lugar = false;
  public ?int $lugar_fecha_y = null;
  public ?int $lugar_fecha_x = null;
  public ?int $beneficiario_y = null;
  public ?int $beneficiario_x = null;
  public ?int $monto_y = null;
  public ?int $monto_x = null;
  public ?int $monto_letras_y = null;
  public ?int $monto_letras_x = null;
  public ?int $detalles_y = null;
  public ?int $detalles_x = null;

  public bool $is_cuenta_301 = false;
  public bool $calcular_pendiente_registro = false;
  public bool $calcular_traslado_gastos = false;
  public bool $calcular_traslado_honorarios = false;

  public $intruccionesPagoNacional;
  public $intruccionesPagoInternacional;

  public ?int $banco_id = null; // Si la cuenta tiene un banco principal directo
  public ?string $perosna_sociedad = null;

  public float $traslados_karla = 0.0;
  public float $certifondo_bnfa = 0.0;
  public float $colchon = 0.0;
  public float $tipo_cambio = 0.0;

  // 🔁 Relaciones many-to-many
  public array $selected_banks = [];  // Para usar en checkboxes o multiselect
  public array $selected_locations = [];
  public array $selected_departments = [];

  // 🔄 Listas auxiliares para selects
  public $currencies = [];
  public $banks = [];
  public $locations = [];
  public $departments = [];

  public $closeForm = false;
  public $columns;
  public $defaultColumns;

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
  ];

  protected function getModelClass(): string
  {
    return Cuenta::class;
  }

  public function mount()
  {
    $this->locations = BusinessLocation::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->banks = Bank::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->currencies = Currency::where('active', 1)->orderBy('code', 'ASC')->get();
    $this->departments = Department::orderBy('name', 'ASC')->get();

    $this->refresDatatable();
  }

  public function render()
  {
    $records = Cuenta::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.clasificadores.cuentas.datatable', [
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

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    $rules = [
      'numero_cuenta' => 'required|string|max:100|unique:cuentas,numero_cuenta,' . $this->recordId,
      'nombre_cuenta' => 'required|string|max:100',
      'moneda_id' => 'required|integer|exists:currencies,id',
      'balance' => 'nullable|numeric',
      'saldo' => 'nullable|numeric',
      'ultimo_cheque' => 'nullable|string|max:100',
      'mostrar_lugar' => 'boolean',
      'lugar_fecha_y' => 'nullable|integer',
      'lugar_fecha_x' => 'nullable|integer',
      'beneficiario_y' => 'nullable|integer',
      'beneficiario_x' => 'nullable|integer',
      'monto_y' => 'nullable|integer',
      'monto_x' => 'nullable|integer',
      'monto_letras_y' => 'nullable|integer',
      'monto_letras_x' => 'nullable|integer',
      'detalles_y' => 'nullable|integer',
      'detalles_x' => 'nullable|integer',
      'is_cuenta_301' => 'boolean',
      'calcular_pendiente_registro' => 'boolean',
      'calcular_traslado_gastos' => 'boolean',
      'calcular_traslado_honorarios' => 'boolean',
      'banco_id' => 'nullable|integer|exists:banks,id',
      'perosna_sociedad' => 'nullable|string|max:200',
      'traslados_karla' => 'nullable|numeric',
      'certifondo_bnfa' => 'nullable|numeric',
      'colchon' => 'nullable|numeric',
      'tipo_cambio' => 'nullable|numeric',
      'intruccionesPagoNacional' => 'nullable|string',
      'intruccionesPagoInternacional' => 'nullable|string',
    ];

    return $rules;
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'required_if' => 'El campo :attribute es obligatorio cuando el tipo es :value.',
      'required_with' => 'El campo :attribute es obligatorio.',
      'email' => 'El campo :attribute debe ser una dirección de correo válida.',
      'string' => 'El campo :attribute debe ser un texto.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'numeric' => 'El campo :attribute debe ser un número.',
      'exists' => 'El campo :attribute debe existir en el sistema.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'in' => 'El valor seleccionado para :attribute no es válido.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'max' => 'El campo :attribute no puede exceder los :max caracteres.',
      'min' => 'El campo :attribute debe ser al menos :min.',
      'numero_cuenta.required' => 'El número de cuenta es obligatorio.',
      'numero_cuenta.unique' => 'Ya existe una cuenta con ese número.',
      'moneda_id.required' => 'Debe seleccionar una moneda.',
      'nombre_cuenta.required' => 'El nombre de la cuenta es obligatorio.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    return [
      'numero_cuenta' => 'número de cuenta',
      'nombre_cuenta' => 'nombre de cuenta',
      'moneda_id' => 'moneda',
      'balance' => 'balance',
      'saldo' => 'saldo',
      'ultimo_cheque' => 'último cheque',
      'mostrar_lugar' => 'mostrar lugar',
      'lugar_fecha_y' => 'posición Y de fecha',
      'lugar_fecha_x' => 'posición X de fecha',
      'beneficiario_y' => 'posición Y de beneficiario',
      'beneficiario_x' => 'posición X de beneficiario',
      'monto_y' => 'posición Y del monto',
      'monto_x' => 'posición X del monto',
      'monto_letras_y' => 'posición Y del monto en letras',
      'monto_letras_x' => 'posición X del monto en letras',
      'detalles_y' => 'posición Y de detalles',
      'detalles_x' => 'posición X de detalles',
      'is_cuenta_301' => 'es cuenta 301',
      'calcular_pendiente_registro' => 'calcular pendiente de registro',
      'calcular_traslado_gastos' => 'calcular traslado de gastos',
      'calcular_traslado_honorarios' => 'calcular traslado de honorarios',
      'banco_id' => 'banco',
      'perosna_sociedad' => 'persona o sociedad',
      'traslados_karla' => 'traslados Karla',
      'certifondo_bnfa' => 'certifondo BNFA',
      'colchon' => 'colchón',
      'tipo_cambio' => 'tipo de cambio',
    ];
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate();

    try {
      // Crear el usuario con la contraseña encriptada
      $record = Cuenta::create($validatedData);

      if ($record) {
        $record->banks()->sync($this->selected_banks);
        $record->locations()->sync($this->selected_locations);
        $record->departments()->sync($this->selected_departments);
      }

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

    $record = Cuenta::find($recordId);
    $this->recordId = $recordId;

    $this->numero_cuenta = $record->numero_cuenta;
    $this->nombre_cuenta = $record->nombre_cuenta;
    $this->moneda_id = $record->moneda_id;
    $this->balance = $record->balance;
    $this->saldo = $record->saldo;
    $this->ultimo_cheque = $record->ultimo_cheque;
    $this->mostrar_lugar = $record->mostrar_lugar;
    $this->lugar_fecha_y = $record->lugar_fecha_y;
    $this->lugar_fecha_x = $record->lugar_fecha_x;
    $this->beneficiario_y = $record->beneficiario_y;
    $this->beneficiario_x = $record->beneficiario_x;
    $this->monto_y = $record->monto_y;
    $this->monto_x = $record->monto_x;
    $this->monto_letras_y = $record->monto_letras_y;
    $this->monto_letras_x = $record->monto_letras_x;
    $this->detalles_y = $record->detalles_y;
    $this->detalles_x = $record->detalles_x;
    $this->is_cuenta_301 = $record->is_cuenta_301;
    $this->calcular_pendiente_registro = $record->calcular_pendiente_registro;
    $this->calcular_traslado_gastos = $record->calcular_traslado_gastos;
    $this->calcular_traslado_honorarios = $record->calcular_traslado_honorarios;
    $this->banco_id = $record->banco_id;
    $this->perosna_sociedad = $record->perosna_sociedad;
    $this->traslados_karla = $record->traslados_karla;
    $this->certifondo_bnfa = $record->certifondo_bnfa;
    $this->colchon = $record->colchon;
    $this->tipo_cambio = $record->tipo_cambio;
    $this->intruccionesPagoNacional = $record->intruccionesPagoNacional;
    $this->intruccionesPagoInternacional = $record->intruccionesPagoInternacional;

    $this->selected_banks = $record->banks->pluck('id')->toArray();
    $this->selected_locations = $record->locations->pluck('id')->toArray();
    $this->selected_departments = $record->departments->pluck('id')->toArray();

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validación de los datos de entrada
    $validatedData = $this->validate();
    try {
      // Encuentra el registro existente
      $record = Cuenta::findOrFail($recordId);

      // Actualiza el usuario
      $record->update($validatedData);

      $record->banks()->sync($this->selected_banks);
      $record->locations()->sync($this->selected_locations);
      $record->departments()->sync($this->selected_departments);

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
      $record = Cuenta::findOrFail($recordId);

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
      'numero_cuenta',
      'nombre_cuenta',
      'moneda_id',
      'balance',
      'saldo',
      'ultimo_cheque',
      'mostrar_lugar',
      'lugar_fecha_y',
      'lugar_fecha_x',
      'beneficiario_y',
      'beneficiario_x',
      'monto_y',
      'monto_x',
      'monto_letras_y',
      'monto_letras_x',
      'detalles_y',
      'detalles_x',
      'is_cuenta_301',
      'calcular_pendiente_registro',
      'calcular_traslado_gastos',
      'calcular_traslado_honorarios',
      'banco_id',
      'perosna_sociedad',
      'traslados_karla',
      'certifondo_bnfa',
      'colchon',
      'tipo_cambio',
      'intruccionesPagoNacional',
      'intruccionesPagoInternacional'
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
      ->where('datatable_name', 'classifier-cuentas-datatable')
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
    'filter_numero_cuenta' => NULL,
    'filter_nombre_cuenta' => NULL,
    'filter_perosna_sociedad' => NULL,
    'filter_moneda' => NULL,
    'filter_bank' => NULL,
    'filter_department' => NULL,
    'filter_location' => NULL,
    'filter_saldo' => NULL,
    'filter_balance' => NULL,
    'filter_ultimo_cheque' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'numero_cuenta',
        'orderName' => 'numero_cuenta',
        'label' => __('Cuenta'),
        'filter' => 'filter_numero_cuenta',
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
        'field' => 'nombre_cuenta',
        'orderName' => 'nombre_cuenta',
        'label' => __('Nombre de cuenta'),
        'filter' => 'filter_nombre_cuenta',
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
        'field' => 'perosna_sociedad',
        'orderName' => 'perosna_sociedad',
        'label' => __('Persona o sociedad'),
        'filter' => 'filter_perosna_sociedad',
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
        'field' => 'moneda',
        'orderName' => 'currencies.code',
        'label' => __('Currency'),
        'filter' => 'filter_moneda',
        'filter_type' => 'select',
        'filter_sources' => 'currencies',
        'filter_source_field' => 'code',
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
        'field' => 'bank',
        'orderName' => '',
        'label' => __('Bank'),
        'filter' => 'filter_bank',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-500',
        'function' => 'getHtmlcolumnBank',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'department',
        'orderName' => '',
        'label' => __('Department'),
        'filter' => 'filter_department',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-200',
        'function' => 'getHtmlcolumnDepartment',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'location',
        'orderName' => '',
        'label' => __('Issuer'),
        'filter' => 'filter_location',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => 'left',
        'columnClass' => 'wrap-col-300',
        'function' => 'getHtmlcolumnLocations',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'saldo',
        'orderName' => 'saldo',
        'label' => __('Saldo'),
        'filter' => 'filter_saldo',
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
        'field' => 'balance',
        'orderName' => 'balance',
        'label' => __('Balance'),
        'filter' => 'filter_balance',
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
        'field' => 'ultimo_cheque',
        'orderName' => 'ultimo_cheque',
        'label' => __('Último cheque'),
        'filter' => 'filter_ultimo_cheque',
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
}
