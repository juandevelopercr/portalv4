<?php

namespace App\Livewire\Contacts;

use App\Livewire\BaseComponent;
use App\Models\Canton;
use App\Models\ConditionSale;
use App\Models\Contact;
use App\Models\Country;
use App\Models\DataTableConfig;
use App\Models\District;
use App\Models\EconomicActivity;
use App\Models\IdentificationType;
use App\Models\Province;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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

class ContactManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'contacts.name';

  #[Url(history: true)]
  public $sortDir = 'ASC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $business_id = 1;
  public $type;
  public $name;
  public $commercial_name;
  public $email;
  public $email_cc;
  public $code;
  public $condition_sale_id;
  public $identification_type_id;
  public $identification;
  public $economic_activity_id;
  public $country_id = 53;
  public $province_id;
  public $canton_id;
  public $district_id;
  public $other_signs;
  public $address;
  public $zip_code;
  public $dob;
  public $phone;
  public $invoice_type;
  public $aplicarImpuesto;
  public $pay_term_number;
  public $pay_term_type;
  public $credit_limit;
  public $balance;
  public $total_rp;
  public $total_rp_used;
  public $total_rp_expired;
  public $shipping_address;
  public $customer_group_id;
  public $is_default;
  public $created_by;

  public $closeForm = false;

  public $enabledSelectedValue = false;

  public $listActives;

  public $clientData;

  public $searchResults;
  public $showModalCedula;

  public $validatedEmails; // Almacena correos válidos
  public $invalidEmails; // Almacena correos inválidos

  public $columns;
  public $defaultColumns;

  //#[Url()]
  public $economicActivities = [];

  public $invoicesTypes = [];

  #[Computed()]
  public function provinces()
  {
    return Province::orderBy('name', 'ASC')->get();
  }

  #[Computed()]
  public function cantons()
  {
    return Canton::where('province_id', $this->province_id)->orderBy('name', 'ASC')->get();
  }

  #[Computed()]
  public function districts()
  {
    return District::where('canton_id', $this->canton_id)->orderBy('name', 'ASC')->get();
  }

  #[Computed()]
  public function identificationTypes()
  {
    return IdentificationType::orderBy('code', 'ASC')->get();
  }

  #[Computed()]
  public function conditionSeles()
  {
    return ConditionSale::where('code', '<>', 99)->orderBy('code', 'ASC')->get();
  }

  #[Computed()]
  public function countries()
  {
    return Country::orderBy('name', 'ASC')->get();
  }

  #[Computed()]
  public function listEconomicActivities()
  {
    return EconomicActivity::orderBy('name', 'asc')->get();
  }

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  protected function getModelClass(): string
  {
    return Contact::class;
  }

  // El método mount recibe el parámetro
  public function mount($enabledSelectedValue, $type = Contact::CUSTOMER)
  {
    $this->validatedEmails = [];
    $this->invalidEmails = [];
    $this->listActives = [['id' => 1, 'name' => 'Activo'], ['id' => 0, 'name' => 'Inactivo']];
    $this->invoicesTypes = [['id' => 'FACTURA', 'name' => 'FACTURA'], ['id' => 'TIQUETE', 'name' => 'TIQUETE']];
    $this->refresDatatable();
    $this->enabledSelectedValue = $enabledSelectedValue;
    $this->type = $type;
  }

  public function render()
  {
    $records = Contact::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->when($this->active !== '', function ($query) {
        $query->where('contacts.active', $this->active);
      })
      ->where('type', $this->type)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.contacts.datatable', [
      'records' => $records,
    ]);
  }

  public function updatedActive($value)
  {
    $this->active = (int) $value;
  }

  public function updatedAplicarImpuesto($value)
  {
    $this->aplicarImpuesto = (int) $value;
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->resetControls();

    $this->condition_sale_id = 1;
    $this->action = 'create';
    $this->active = 1;
    $this->aplicarImpuesto = 1;
    $this->dispatch('scroll-to-top');
    $this->dispatch('reinitContactSelec2Form');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    $rules = [
      'business_id' => 'required|exists:business,id',
      'type' => 'required|in:customer,supplier',
      'name' => 'required|string|max:100',
      'commercial_name' => 'nullable|string|max:80',
      'email' => 'required|email|max:191',
      'email_cc' => 'nullable|string',
      'condition_sale_id' => 'nullable|exists:condition_sales,id',
      'identification_type_id' => 'required|exists:identification_types,id',
      'identification' => 'required|string|max:12',
      'economicActivities' => 'nullable|array|min:0', // Verifica que al menos un rol sea seleccionado
      'economicActivities.*' => 'exists:economic_activities,id', // Valida cada rol seleccionado
      'country_id' => 'required|exists:countries,id',
      'province_id' => 'nullable|exists:provinces,id', // Permite que province_id sea null, pero si no es null debe existir en la tabla 'provinces'
      'canton_id' => 'nullable|exists:cantons,id|required_with:province_id',
      'district_id' => 'nullable|exists:districts,id|required_with:canton_id',
      'other_signs' => 'required|string|min:5|max:160',
      'address' => 'nullable|string',
      'zip_code' => 'nullable|string|max:191',
      'dob' => 'nullable|date',
      'phone' => 'nullable|string|max:191',
      'invoice_type' => 'required|in:FACTURA,TIQUETE',
      'pay_term_number' => 'nullable|integer|min:0',
      'pay_term_number' => $this->condition_sale_id == 2 ? 'required|integer|min:0' : 'nullable|integer|min:0',
      'pay_term_type' => 'nullable|in:days,months',
      'credit_limit' => 'nullable|numeric|min:0|max:9999999999999999.9999',
      'balance' => 'nullable|numeric|min:0|max:9999999999999999.9999',
      'total_rp' => 'nullable|integer|min:0',
      'total_rp_used' => 'nullable|integer|min:0',
      'total_rp_expired' => 'nullable|integer|min:0',
      'shipping_address' => 'nullable|string',
      'customer_group_id' => 'nullable|exists:customer_groups,id',
      'is_default' => 'nullable|boolean',
      'active' => 'required|integer|in:0,1',
      'aplicarImpuesto' => 'required|integer|in:0,1',
      'created_by' => 'required|integer', // Asegurándote de que sea requerido y sea un entero
    ];

    // Validación dinámica según tipo de identificación
    /*
    if ($this->identification_type_id == IdentificationType::CEDULA_FISICA) {
      $rules['identification'] .= '|regex:/^\d{9}$/';  // 9 dígitos
    } elseif ($this->identification_type_id == IdentificationType::CEDULA_JURIDICA) {
      $rules['identification'] .= '|regex:/^\d{10}$/'; // 10 dígitos
    } elseif ($this->identification_type_id == IdentificationType::DIMEX) {
      $rules['identification'] .= '|regex:/^\d{11,12}$/'; // 11 o 12 dígitos
    } elseif ($this->identification_type_id == IdentificationType::NITE) {
      $rules['identification'] .= '|regex:/^\d{10}$/'; // 10 dígitos
    }
    */

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
      'identification.regex' => 'El campo identificación debe tener el formato adecuado para el tipo de identificación seleccionado.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    return [
      'business_id' => 'negocio',
      'type' => 'tipo',
      'name' => 'nombre',
      'commercial_name' => 'nombre comercial',
      'email' => 'correo electrónico',
      'email_cc' => 'correo electrónico CC',
      'condition_sale_id' => 'condición de venta',
      'identification_type_id' => 'tipo de identificación',
      'identification' => 'identificación',
      'economic_activity_id' => 'actividad económica',
      'country_id' => 'país',
      'province_id' => 'provincia',
      'canton_id' => 'cantón',
      'district_id' => 'distrito',
      'other_signs' => 'otros señas',
      'address' => 'dirección',
      'zip_code' => 'código postal',
      'dob' => 'fecha de nacimiento',
      'phone' => 'teléfono',
      'invoice_type' => 'tipo de factura',
      'pay_term_number' => 'plazo de pago (número)',
      'pay_term_type' => 'plazo de pago (tipo)',
      'credit_limit' => 'límite de crédito',
      'balance' => 'saldo',
      'total_rp' => 'total de puntos de recompensa',
      'total_rp_used' => 'puntos de recompensa usados',
      'total_rp_expired' => 'puntos de recompensa expirados',
      'shipping_address' => 'dirección de envío',
      'customer_group_id' => 'grupo de clientes',
      'is_default' => 'es predeterminado',
      'active' => 'activo',
    ];
  }

  public function store()
  {
    $this->created_by = Auth::user()->id; // Asignar el usuario logueado

    // Validar primero
    $validatedData = $this->validate();

    $validatedData['created_by'] = $this->created_by;

    // Autocompletar identificación antes de guardar
    /*
    $validatedData['identification'] = $this->autofillIdentification(
      $validatedData['identification_type_id'],
      $validatedData['identification']
    );
    */

    // Convertir valores vacíos a null
    $validatedData['province_id'] = empty($validatedData['province_id']) ? null : $validatedData['province_id'];
    $validatedData['canton_id'] = empty($validatedData['canton_id']) ? null : $validatedData['canton_id'];
    $validatedData['district_id'] = empty($validatedData['district_id']) ? null : $validatedData['district_id'];

    $validatedData['pay_term_number'] = empty($validatedData['pay_term_number']) ? null : $validatedData['pay_term_number'];

    try {

      // Crear el usuario con la contraseña encriptada
      $record = Contact::create($validatedData);

      $closeForm = $this->closeForm;

      if ($record) {
        $record->economicActivities()->sync($validatedData['economicActivities'] ?? []);
      }

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

    try {
      $record = Contact::findOrFail($recordId);
      $this->recordId = $recordId;

      $this->business_id          = $record->business_id;
      $this->type                 = $record->type;
      $this->name                 = $record->name;
      $this->commercial_name      = $record->commercial_name;
      $this->email                = $record->email;
      $this->email_cc             = $record->email_cc;
      $this->code                 = $record->code;
      $this->condition_sale_id    = $record->condition_sale_id;
      $this->identification_type_id = $record->identification_type_id;
      $this->identification       = $record->identification;
      $this->economic_activity_id = $record->economic_activity_id;
      $this->country_id           = $record->country_id;
      $this->province_id          = $record->province_id;
      $this->canton_id            = $record->canton_id;
      $this->district_id          = $record->district_id;
      $this->other_signs          = $record->other_signs;
      $this->address              = $record->address;
      $this->zip_code             = $record->zip_code;
      $this->dob                  = $record->dob;
      $this->phone                = $record->phone;
      $this->invoice_type         = $record->invoice_type;
      $this->pay_term_number      = $record->pay_term_number;
      $this->pay_term_type        = $record->pay_term_type;
      $this->credit_limit         = $record->credit_limit;
      $this->balance              = $record->balance;
      $this->total_rp             = $record->total_rp;
      $this->total_rp_used        = $record->total_rp_used;
      $this->total_rp_expired     = $record->total_rp_expired;
      $this->shipping_address     = $record->shipping_address;
      $this->customer_group_id    = $record->customer_group_id;
      $this->is_default           = $record->is_default;
      $this->aplicarImpuesto      = $record->aplicarImpuesto;
      $this->active               = $record->active;
    } catch (ModelNotFoundException $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. Record not found') . ' ' . $e->getMessage()]);
    }

    $this->economicActivities = $record->economicActivities->pluck('id')->toArray();

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
    $this->dispatch('reinitContactSelec2Form');
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Desautocompletar temporalmente (remover ceros)
    $this->identification = $this->stripLeadingZeros($this->identification);
    $this->created_by = !$this->created_by ? Auth::user()->id : $this->created_by; // Asignar el usuario logueado

    // Validar
    $validatedData = $this->validate();

    $validatedData['created_by'] = $this->created_by;


    // Convertir valores vacíos a null
    $validatedData['province_id'] = empty($validatedData['province_id']) ? null : $validatedData['province_id'];
    $validatedData['canton_id'] = empty($validatedData['canton_id']) ? null : $validatedData['canton_id'];
    $validatedData['district_id'] = empty($validatedData['district_id']) ? null : $validatedData['district_id'];
    $validatedData['pay_term_number'] = empty($validatedData['pay_term_number']) ? null : $validatedData['pay_term_number'];

    try {
      // Encuentra el registro existente
      $record = Contact::findOrFail($recordId);

      // Actualizar
      $record->update($validatedData);

      $closeForm = $this->closeForm;

      if ($record) {
        $record->economicActivities()->sync($validatedData['economicActivities'] ?? []);
      }

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

  protected function autofillIdentification($identificationType, $identification)
  {
    switch ($identificationType) {
      case IdentificationType::CEDULA_FISICA: // Cédula Física (9 dígitos)
        return str_pad($identification, 12, '0', STR_PAD_LEFT); // Añadir 3 ceros
      case IdentificationType::CEDULA_JURIDICA: // Cédula Jurídica (10 dígitos)
        return str_pad($identification, 12, '0', STR_PAD_LEFT); // Añadir 2 ceros
      case IdentificationType::DIMEX: // DIMEX (11 o 12 dígitos)
        return str_pad($identification, 12, '0', STR_PAD_LEFT); // Añadir 1 cero si tiene 11
      case IdentificationType::NITE: // NITE (10 dígitos)
        return str_pad($identification, 12, '0', STR_PAD_LEFT); // Añadir 2 ceros
      default:
        return $identification;  // Devolver tal como está si no coincide
    }
  }

  protected function stripLeadingZeros($identification)
  {
    return ltrim($identification, '0'); // Elimina ceros a la izquierda
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
      $record = Contact::findOrFail($recordId);

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
      'commercial_name',
      'email',
      'email_cc',
      'code',
      'condition_sale_id',
      'identification_type_id',
      'identification',
      'economic_activity_id',
      'country_id',
      'province_id',
      'canton_id',
      'district_id',
      'other_signs',
      'address',
      'zip_code',
      'dob',
      'phone',
      'invoice_type',
      'aplicarImpuesto',
      'pay_term_number',
      'pay_term_type',
      'credit_limit',
      'balance',
      'total_rp',
      'total_rp_used',
      'total_rp_expired',
      'shipping_address',
      'customer_group_id',
      'is_default',
      'active',
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
    if (
      $property === 'country_id' || $property === 'province_id' || $property === 'canton_id' || $property === 'district_id' ||
      $property === 'condition_sale_id' || $property === 'identification_type_id' || $property === 'pay_term_number' ||
      $property === 'economicActivities'
    ) {

      // Aquí verificamos si estamos en modo edición y no sobreescribimos los valores de canton y distrito si ya están definidos.
      if ($property == 'province_id') {
        // Solo limpiar canton_id y district_id si no hay valores preexistentes
        if (!$this->canton_id) {
          $this->canton_id = null;
        }
        if (!$this->district_id) {
          $this->district_id = null;
        }
        $this->dispatch('select2:refresh', ['id' => 'canton_id']);
        //$this->dispatch('select2:refresh', ['id' => 'district_id']);
      } else if ($property == 'canton_id') {
        // Limpiar el district_id solo si no tiene valor preexistente
        if (!$this->district_id) {
          $this->district_id = null;
        }
        //$this->dispatch('select2:refresh', ['id' => 'district_id']);
      }

      if ($property == 'condition_sale_id') {
        if ($this->condition_sale_id != '02')
          $this->pay_term_number = '';
      }

      if ($property == 'identification_type_id') {
        // cedula fisica, jurídica o NITE
        if ($this->identification_type_id == 1 || $this->identification_type_id == 2 || $this->identification_type_id == 4)
          $this->invoice_type = 'FACTURA';
        else
          $this->invoice_type = 'TIQUETE';
      }
    }

    if ($property == 'email_cc') {
      $this->updatedEmails();
    }

    $this->dispatch('reinitContactSelec2Form');
  }

  public function updatedEmails()
  {
    // Divide la cadena en correos separados por , o ;
    $emailList = preg_split('/[,;]+/', $this->email_cc);

    // Resetear las listas de correos válidos e inválidos
    $this->validatedEmails = [];
    $this->invalidEmails = [];

    // Validar cada correo
    foreach ($emailList as $email) {
      $email = trim($email); // Elimina espacios en blanco
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->validatedEmails[] = $email; // Correo válido
      } elseif (!empty($email)) {
        $this->invalidEmails[] = $email; // Correo inválido
      }
    }

    // Si hay correos inválidos, añadir error al campo email_cc
    if (!empty($this->invalidEmails)) {
      $this->addError('email_cc', 'Hay correos inválidos: ' . implode(', ', $this->invalidEmails));
    } else {
      $this->resetErrorBag('email_cc'); // Limpiar errores si todos son válidos
    }
  }

  public function selectCustomerData($id)
  {
    // Emite un evento para el componente principal
    // Dispatch para el componente principal
    try {
      // Buscar el cliente
      $customer = Contact::findOrFail($id);

      $data = [
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_comercial_name' => $customer->customer_comercial_name,
        'customer_email' => $customer->email,
        'email_cc' => $customer->email_cc,
        'condition_sale' => !is_null($customer->conditionSale) ? $customer->conditionSale->code : '',
        'pay_term_number' => (int)$customer->pay_term_number,
        'identification_type_id' => $customer->identification_type_id,
        'tipoIdentificacion' => !is_null($customer->identificationType) ? $customer->identificationType->name : '',
        'identification' => $customer->identification,
        'invoice_type' => $customer->invoice_type,
      ];
      // Emitir el evento con los datos del cliente
      $this->dispatch('customerSelected', $data);
    } catch (ModelNotFoundException $e) {
      // Si el cliente no existe, enviar datos vacíos
      $data = [
        'customer_id' => '',
        'customer_name' => '',
        'customer_comercial_name' => '',
        'customer_email' => '',
        'email_cc' => '',
        'condition_sale' => '',
        'pay_term_number' => '',
        'identification_type_id' => '',
        'tipoIdentificacion' => '',
        'identification' => '',
        'invoice_type' => '',
      ];

      $this->dispatch('customerSelected', $data);
    }
  }

  /*
  public function searchClient($field)
  {
    $query = $this->identification;

    if (!empty($query) && strlen($query) >= 9) {
      try {
        // Configuración con headers necesarios
        $response = Http::withOptions([
          'verify' => false,  // Deshabilitar verificación SSL
          'curl' => [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
          ],
          'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
          ]
        ])->get("https://api.hacienda.go.cr/fe/ae", [
          'identificacion' => $query
        ]);

        if ($response->failed()) {
          $errorMessage = match ($response->status()) {
            403 => __('Access denied to Hacienda API. Please check your connection or try again later'),
            404 => __('No results found for this identification'),
            default => __('There was an error connecting to the API. Status code: ') . $response->status()
          };

          $this->dispatch('show-notification', [
            'type' => 'error',
            'message' => $errorMessage
          ]);
          return;
        }

        $data = $response->json();

        if (isset($data['nombre'])) {
          $this->processApiResponse($data, $query);
        } else {
          $this->handleNoResults();
        }
      } catch (\Exception $e) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('API Error: ') . $e->getMessage()
        ]);
      }
    } else {
      $this->dispatch('show-notification', [
        'type' => 'warning',
        'message' => __('You must enter at least 9 characters')
      ]);
    }
  }

  protected function processApiResponse(array $data, string $query): void
  {
    $this->clientData = [
      'fullname' => $data['nombre'],
      'cedula' => $query,
      'type' => $this->mapIdentificationType($data['tipoIdentificacion'] ?? '01'),
      'regimen' => $data['regimen']['descripcion'] ?? null,
      'situacion' => $data['situacion'] ?? null,
      'actividades' => $data['actividades'] ?? []
    ];

    $this->name = $data['nombre'];
    $this->identification = $query;
    $this->identification_type_id = $this->getIdentificationType(
      $this->mapIdentificationType($data['tipoIdentificacion'] ?? '01')
    );

    $this->dispatch('show-notification', [
      'type' => 'success',
      'message' => __('Client data retrieved successfully')
    ]);
  }

  protected function handleNoResults(): void
  {
    $this->name = null;
    $this->identification = null;
    $this->identification_type_id = null;
    $this->dispatch('show-notification', [
      'type' => 'warning',
      'message' => __('No results found for this identification')
    ]);
  }

  protected function mapIdentificationType(string $tipo): string
  {
    return match ($tipo) {
      '01' => 'fisica',
      '02' => 'juridica',
      '03' => 'dimex',
      '04' => 'nite',
      default => 'fisica'
    };
  }
  */

  //Esta es la api antigua
  public function searchClient($field)
  {
    if ($field == 'cedula')
      $query = $this->identification; // Si cedula no está vacía, usa cedula, de lo contrario usa nombre.
    else
      $query = $this->name; // Si cedula no está vacía, usa cedula, de lo contrario usa nombre.

    if (!empty($query) && strlen($query) >= 9) {
      try {
        // Aquí asumimos que la API devuelve un JSON con los resultados de la búsqueda.
        $response = Http::withOptions(['verify' => false])
          ->get("https://apis.gometa.org/cedulas/" . $query);

        if ($response->failed()) {
          // Si la respuesta es un error (status 4xx o 5xx), maneja el error
          $this->dispatch('show-notification', ['type' => 'error', 'message' => __('There was an error connecting to the API. Please try again later')]);
          return;
        }

        $data = $response->json();

        if (isset($data['results'])) {
          if (count($data['results']) == 1) {
            // Si solo hay un resultado, rellena los campos automáticamente
            $this->clientData = $data['results'][0];
            $this->name = $this->clientData['fullname']; // Asume que 'name' es el campo que deseas
            $type = false;
            if (isset($this->clientData['guess_type']))
              $type = $this->getIdentificationType($this->clientData['guess_type']);

            if (!$type && isset($this->clientData['type']))
              $type = $this->getIdentificationType($this->clientData['type']);

            $this->identification = $this->clientData['cedula'];
            $this->identification_type_id = $type ?? null;

            $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The client has been successfully selected')]);

            // Rellena otros campos si es necesario
          } elseif (count($data['results']) > 1) {
            // Si hay varios resultados, muestra el modal
            $this->searchResults = $data['results'];
            $this->showModalCedula = true;
          } else {
            $this->name = NULL;
            $this->identification = NULL;
            $this->identification_type_id = NULL;
            // Si no se encuentra ningún resultado
            $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('No results found')]);
          }
          $this->resetErrorBag(); // Limpia los errores de validación previos
          $this->resetValidation(); // También puedes reiniciar los valores previos de val
        } else {
          // Si la API no devuelve 'results', muestra un mensaje de error.
          $this->dispatch('show-notification', ['type' => 'error', 'message' => __('No results found in API response')]);
        }
      } catch (\Exception $e) {
        // Captura cualquier excepción (como problemas de red, tiempo de espera, etc.)
        $this->dispatch('show-notification', ['type' => 'error', 'message' => __('There was an error connecting to the API') . ' ' . $e->getMessage()]);
      }
    } else {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('You must enter at least 9 characters')]);
    }
  }

  // Método para seleccionar un cliente de los resultados
  // Esto se usaba con la api antigual de consulta de cedula en el caso que arrojara varios registros
  public function selectClient($index)
  {
    // Encuentra el cliente seleccionado en los resultados
    //$client = collect($this->searchResults)->firstWhere('id', $clientId);
    $client = $this->searchResults[$index];
    if ($client) {
      $this->name = $client['fullname']; // Rellena los campos con la información seleccionada

      $type = false;
      if (isset($client['guess_type']))
        $type = $this->getIdentificationType($client['guess_type']);

      if (!$type && isset($client['type']))
        $type = $this->getIdentificationType($client['type']);

      $this->identification = $client['cedula'];
      $this->identification_type_id = $type ?? null;
      // Otros campos aquí si es necesario
    }
    $this->showModalCedula = false;
  }

  public function getIdentificationType($type)
  {
    $type = strtoupper($type);
    $founded = false;
    switch ($type) {
      case 'F':
        $type = 1; // Cédula Física
        $founded = true;
        break;
      case 'J':
        $type = 2; // Cédula Jurídica
        $founded = true;
        break;
      case 'D':
        $type = 3; // Cédula DIMEX
        $founded = true;
        break;
      case 'N':
        $type = 4; // Cédula NITE
        $founded = true;
        break;
      case 'FISICA':
        $type = 1; // Cédula Física
        $founded = true;
        break;
      case 'JURIDICA':
        $type = 2; // Cédula Jurídica
        $founded = true;
        break;
      case 'DIMEX':
        $type = 3; // Cédula DIMEX
        $founded = true;
        break;
      case 'NITE':
        $type = 4; // Cédula NITE
        $founded = true;
        break;
    }

    if ($founded)
      return $type;
    else
      return $founded;
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'contact-datatable')
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
    'filter_identification_type' => NULL,
    'filter_identification' => NULL,
    'filter_phone' => NULL,
    'filter_condition_sale_name' => NULL,
    'filter_email' => NULL,
    'filter_email_cc' => NULL,
    'filter_created_at' => NULL,
    'filter_active' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'name',
        'orderName' => 'contacts.name',
        'label' => __('Name'),
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
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'identification_type',
        'orderName' => 'identification_types.name',
        'label' => __('Identification Type'),
        'filter' => 'filter_identification_type',
        'filter_type' => 'select',
        'filter_sources' => 'identificationTypes',
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
      ],
      [
        'field' => 'identification',
        'orderName' => 'contacts.identification',
        'label' => __('Identification'),
        'filter' => 'filter_identification',
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
        'field' => 'phone',
        'orderName' => 'contacts.phone',
        'label' => __('Phone'),
        'filter' => 'filter_phone',
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
        'field' => 'condition_sale',
        'orderName' => 'condition_sales.name',
        'label' => __('Condition Sale'),
        'filter' => 'filter_condition_sale_name',
        'filter_type' => 'select',
        'filter_sources' => 'conditionSeles',
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
      ],
      [
        'field' => 'email',
        'orderName' => 'contacts.email',
        'label' => __('Email'),
        'filter' => 'filter_email',
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
        'field' => 'email_cc',
        'orderName' => 'contacts.email_cc',
        'label' => __('Copy Emails'),
        'filter' => 'filter_email_cc',
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
        'field' => 'created_at',
        'orderName' => 'contacts.created_at',
        'label' => __('Created at'),
        'filter' => 'filter_created_at',
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
        'field' => 'active',
        'orderName' => 'contacts.active',
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

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }
}
