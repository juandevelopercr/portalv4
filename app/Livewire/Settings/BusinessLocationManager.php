<?php

namespace App\Livewire\Settings;

use App\Livewire\BaseComponent;
use App\Models\BusinessLocation;
use App\Models\Canton;
use App\Models\CentroCosto;
use App\Models\ConditionSale;
use App\Models\Country;
use App\Models\DataTableConfig;
use App\Models\District;
use App\Models\DocumentSequence;
use App\Models\EconomicActivity;
use App\Models\IdentificationType;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class BusinessLocationManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'Search', history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(as: 'SortBy', history: true)]
  public $sortBy = 'business_locations.name';

  #[Url(as: 'SortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'PerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $economicActivities = [];

  // Propiedades públicas
  public $business_id;
  public $location_parent_id;
  public $code;
  public $name;
  public $commercial_name;
  public $admin_invoice_layout_id;
  public $pos_invoice_layout_id;
  public $selling_price_group_id;
  public $admin_quotation_layout_id;
  public $pos_quotation_layout_id;
  public $print_receipt_on_invoice = true;
  public $receipt_printer_type = 'browser';
  public $printer_id;
  public $phone_code;
  public $phone;
  public $email;
  public $website;
  public $environment;
  public $api_key;
  public $password;
  public $identification_type_id;
  public $identification;
  public $country_id = 53;  // Costa Rica
  public $zip_code;
  public $province_id;
  public $canton_id;
  public $district_id;
  public $address;
  public $other_signs;
  public $certificate_pin;
  public $api_user_hacienda;
  public $api_password;
  public $certificate_digital_file;
  public $oldcertificate_digital_file;
  public $proveedor;
  public $registrofiscal8707;
  public $numero_sucursal;
  public $numero_punto_venta;

  public $columns;
  public $defaultColumns;

  public $closeForm = false;

  public $documentTypes = [
    'FE'  => 'Factura electrónica',
    'TE'  => 'Tiquete electrónico',
    'FEC' => 'Factura electrónica de compra',
    'NCE' => 'Nota de crédito electrónica',
    'NDE' => 'Nota de débito electrónica',
    'MR'  => 'Mensaje de receptor'
  ];

  public $sequences = [];
  public $location; // Se toma de la sesion

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
  ];

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

  protected function getModelClass(): string
  {
    return BusinessLocation::class;
  }

  public function mount($business_id)
  {
    $this->business_id = $business_id;
    $this->refresDatatable();

    $this->location = BusinessLocation::where('business_id', $this->business_id)->first();

    // Inicializar secuencias
    foreach (array_keys($this->documentTypes) as $type) {
      $sequence = DocumentSequence::firstOrNew([
        'emitter_id' => $this->location->id,
        'document_type' => $type
      ], ['current_sequence' => 0]);

      $this->sequences[$type] = $sequence->current_sequence;
    }
  }

  public function render()
  {
    $records = BusinessLocation::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->when($this->active !== '', function ($query) {
        $query->where('business_locations.active', $this->active);
      })
      ->where('business_id', '=', $this->business_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.settings.datatable', [
      'records' => $records,
    ]);
  }

  protected function rules()
  {
    return [
      'business_id' => 'required|exists:business,id',
      'location_parent_id' => 'nullable|exists:business_locations,id',
      'code' => 'required|string|max:3',
      'name' => 'required|string|max:100',
      'commercial_name' => 'nullable|string|max:80',
      'admin_invoice_layout_id' => 'nullable|exists:invoice_layouts,id',
      'pos_invoice_layout_id' => 'nullable|exists:invoice_layouts,id',
      'selling_price_group_id' => 'nullable|exists:selling_price_groups,id',
      'admin_quotation_layout_id' => 'nullable|exists:invoice_layouts,id',
      'pos_quotation_layout_id' => 'nullable|exists:invoice_layouts,id',
      'print_receipt_on_invoice' => 'boolean',
      'receipt_printer_type' => ['required', Rule::in(['browser', 'printer'])],
      'printer_id' => 'nullable|integer',
      'phone_code' => 'nullable|string|max:10',
      'phone' => 'nullable|string|max:191',
      'email' => 'nullable|email|max:191',
      'website' => 'nullable|string|max:191',
      'environment' => ['required', Rule::in(['produccion', 'prueba'])],
      'api_key' => 'nullable|string|max:255',
      'password' => 'nullable|string|max:255',
      'identification_type_id' => 'nullable|exists:identification_types,id',
      'identification' => 'nullable|string|max:12',
      'country_id' => 'required|exists:countries,id',
      'zip_code' => 'nullable|string|size:7',
      'province_id' => 'required|exists:provinces,id',
      'canton_id' => 'required|exists:cantons,id',
      'district_id' => 'required|exists:districts,id',
      'address' => 'nullable|string|max:255',
      'other_signs' => 'required|string|max:250',
      'certificate_pin' => 'nullable|string|max:100',
      'api_user_hacienda' => 'nullable|string|max:100',
      'api_password' => 'nullable|string|max:255',
      'proveedor' => 'nullable|string|max:255',
      'registrofiscal8707' => 'nullable|string|max:12',
      'economicActivities' => 'required|array',
      'economicActivities.*' => 'integer|exists:economic_activities,id',
      'numero_sucursal' => 'required|string|max:3',
      'numero_punto_venta' => 'required|string|max:5',
      'active' => 'boolean',
    ];
  }

  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'string' => 'El campo :attribute debe ser una cadena de texto.',
      'max' => 'El campo :attribute no debe exceder :max caracteres.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'email' => 'El campo :attribute debe ser un correo electrónico válido.',
      'size' => 'El campo :attribute debe tener exactamente :size caracteres.',
      'exists' => 'El :attribute seleccionado no es válido.',
      'in' => 'El valor del campo :attribute no es válido.',
    ];
  }

  protected function validationAttributes()
  {
    return [
      'business_id' => 'ID del negocio',
      'location_parent_id' => 'Ubicación principal',
      'code' => 'Código',
      'name' => 'Nombre',
      'commercial_name' => 'Nombre comercial',
      'admin_invoice_layout_id' => 'Formato de factura administrativa',
      'pos_invoice_layout_id' => 'Formato de factura POS',
      'selling_price_group_id' => 'Grupo de precios de venta',
      'admin_quotation_layout_id' => 'Formato de cotización administrativa',
      'pos_quotation_layout_id' => 'Formato de cotización POS',
      'print_receipt_on_invoice' => 'Imprimir recibo en factura',
      'receipt_printer_type' => 'Tipo de impresora de recibo',
      'printer_id' => 'ID de la impresora',
      'phone_code' => 'Código de teléfono',
      'phone' => 'Teléfono',
      'email' => 'Correo electrónico',
      'website' => 'Sitio web',
      'environment' => 'Ambiente',
      'api_key' => 'Clave API',
      'password' => 'Contraseña',
      'identification_type_id' => 'Tipo de identificación',
      'identification' => 'Identificación',
      'country_id' => 'País',
      'zip_code' => 'Código postal',
      'province_id' => 'Provincia',
      'canton_id' => 'Cantón',
      'district_id' => 'Distrito',
      'address' => 'Dirección',
      'other_signs' => 'Señas adicionales',
      'certificate_pin' => 'PIN del certificado',
      'api_user_hacienda' => 'Usuario API Hacienda',
      'api_password' => 'Contraseña API Hacienda',
      'economicActivities' => 'Código de actividad económica',
      'proveedor' => 'Proveedor',
      'registrofiscal8707' => 'Registro Fiscal 8707',
      'numero_sucursal' => 'Número de sucursal',
      'numero_punto_venta' => 'Número de caja',
      'active' => 'Activo',
    ];
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
    $this->print_receipt_on_invoice = !empty($this->print_receipt_on_invoice) ? 1 : 0;
    $this->receipt_printer_type = !empty($this->receipt_printer_type) ? $this->receipt_printer_type : 'browser';

    // Validar
    $validatedData = $this->validate();

    // Validar la imagen solo si existe una nueva imagen
    if ($this->certificate_digital_file) {
      $this->validate([
        //'certificate_digital_file' => 'file|mimes:p12|max:5120',
        //'certificate_digital_file' => 'required|file|mimes:pem,application/x-pem-file,application/octet-stream|max:5120',
        'certificate_digital_file' => 'required|file|mimetypes:pem,application/octet-stream,application/x-x509-user-cert|max:5120',
      ]);
    }

    try {
      // Procesa la nueva imagen si se subió
      if ($this->certificate_digital_file instanceof \Illuminate\Http\UploadedFile) {
        // Crear la carpeta si no existe
        $directory = 'assets/certificates';
        if (!Storage::disk('public')->exists($directory)) {
          Storage::disk('public')->makeDirectory($directory);
        }

        // Guardar la nueva imagen
        //$certificateName = uniqid() . '.' . $this->certificate_digital_file->extension();
        $certificateName = uniqid() . '.' . $this->certificate_digital_file->getClientOriginalExtension();
        $this->certificate_digital_file->storeAs($directory, $certificateName, 'public');
        $validatedData['certificate_digital_file'] = $certificateName;
      }

      // Crear el usuario con la contraseña encriptada
      $record = BusinessLocation::create([
        'business_id' => $validatedData['business_id'],
        'location_parent_id' => $validatedData['location_parent_id'] ?? null,
        'code' => $validatedData['code'],
        'name' => $validatedData['name'],
        'commercial_name' => $validatedData['commercial_name'],
        'admin_invoice_layout_id' => $validatedData['admin_invoice_layout_id'],
        'pos_invoice_layout_id' => $validatedData['pos_invoice_layout_id'],
        'selling_price_group_id' => $validatedData['selling_price_group_id'] ?? null,
        'admin_quotation_layout_id' => $validatedData['admin_quotation_layout_id'] ?? null,
        'pos_quotation_layout_id' => $validatedData['pos_quotation_layout_id'] ?? null,
        'print_receipt_on_invoice' => $validatedData['print_receipt_on_invoice'] ?? true,
        'receipt_printer_type' => $validatedData['receipt_printer_type'] ?? 'browser',
        'printer_id' => $validatedData['printer_id'] ?? null,
        'phone_code' => $validatedData['phone_code'] ?? null,
        'phone' => $validatedData['phone'] ?? null,
        'email' => $validatedData['email'] ?? null,
        'website' => $validatedData['website'] ?? null,
        'environment' => $validatedData['environment'] ?? null,
        'api_key' => $validatedData['api_key'] ?? null,
        'password' => $validatedData['password'] ?? null,
        'identification_type_id' => $validatedData['identification_type_id'] ?? null,
        'identification' => $validatedData['identification'] ?? null,
        'country_id' => $validatedData['country_id'],
        'zip_code' => $validatedData['zip_code'],
        'province_id' => $validatedData['province_id'],
        'canton_id' => $validatedData['canton_id'],
        'district_id' => $validatedData['district_id'],
        'address' => $validatedData['address'] ?? null,
        'other_signs' => $validatedData['other_signs'] ?? null,
        'certificate_pin' => $validatedData['certificate_pin'] ?? null,
        'api_user_hacienda' => $validatedData['api_user_hacienda'] ?? null,
        'api_password' => $validatedData['api_password'] ?? null,
        'proveedor' => $validatedData['proveedor'] ?? null,
        'registrofiscal8707' => $validatedData['registrofiscal8707'] ?? null,
        'numero_sucursal' => $validatedData['numero_sucursal'] ?? null,
        'numero_punto_venta' => $validatedData['numero_punto_venta'] ?? null,
        'certificate_digital_file' => $validatedData['certificate_digital_file'] ?? null,
      ]);

      // Obtener los IDs de actividades económicas o un array vacío si no hay datos
      $economicActivitiesIds = !empty($validatedData['economicActivities'])
        ? array_map(fn($activity) => is_array($activity) ? $activity['id'] : $activity, $validatedData['economicActivities'])
        : [];

      // Sincronizar la relación (esto eliminará todas las relaciones si el array es vacío)
      $record->economicActivities()->sync($economicActivitiesIds);

      // Guardar secuencias
      foreach ($this->sequences as $type => $value) {
        DocumentSequence::updateOrCreate(
          [
            'emitter_id' => $this->location->id,
            'document_type' => $type
          ],
          ['current_sequence' => $value]
        );
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

    $record = BusinessLocation::find($recordId);
    $this->recordId = $recordId;

    // Asignación de datos desde el registro encontrado
    $this->business_id = $record->business_id;
    $this->location_parent_id = $record->location_parent_id;
    $this->code = $record->code;
    $this->name = $record->name;
    $this->commercial_name = $record->commercial_name;
    $this->admin_invoice_layout_id = $record->admin_invoice_layout_id;
    $this->pos_invoice_layout_id = $record->pos_invoice_layout_id;
    $this->selling_price_group_id = $record->selling_price_group_id;
    $this->admin_quotation_layout_id = $record->admin_quotation_layout_id;
    $this->pos_quotation_layout_id = $record->pos_quotation_layout_id;
    $this->print_receipt_on_invoice = $record->print_receipt_on_invoice;
    $this->receipt_printer_type = $record->receipt_printer_type;
    $this->printer_id = $record->printer_id;
    $this->phone_code = $record->phone_code;
    $this->phone = $record->phone;
    $this->email = $record->email;
    $this->website = $record->website;
    $this->environment = $record->environment;
    $this->api_key = $record->api_key;
    $this->password = $record->password;
    $this->identification_type_id = $record->identification_type_id;
    $this->identification = $record->identification;
    $this->country_id = $record->country_id;
    $this->zip_code = $record->zip_code;
    $this->province_id = $record->province_id;
    $this->canton_id = $record->canton_id;
    $this->district_id = $record->district_id;
    $this->address = $record->address;
    $this->other_signs = $record->other_signs;
    $this->certificate_pin = $record->certificate_pin;
    $this->api_user_hacienda = $record->api_user_hacienda;
    $this->api_password = $record->api_password;
    $this->certificate_digital_file = $record->certificate_digital_file;
    $this->oldcertificate_digital_file = $record->certificate_digital_file;
    $this->proveedor = $record->proveedor;
    $this->registrofiscal8707 = $record->registrofiscal8707;
    $this->active = $record->active;
    $this->numero_sucursal = $record->numero_sucursal;
    $this->numero_punto_venta = $record->numero_punto_venta;

    $this->economicActivities = $record->economicActivities()->pluck('id')->toArray(); // Obtiene los IDs

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';

    $this->dispatch('select2');
  }

  public function update()
  {
    $recordId = $this->recordId;

    $this->print_receipt_on_invoice = !empty($this->print_receipt_on_invoice) ? 1 : 0;
    $this->receipt_printer_type = !empty($this->receipt_printer_type) ? $this->receipt_printer_type : 'browser';

    // Validar
    $validatedData = $this->validate();

    // Validar la imagen solo si existe una nueva imagen
    if ($this->certificate_digital_file && $this->certificate_digital_file !== $this->oldcertificate_digital_file) {
      $this->validate([
        //'certificate_digital_file' => 'required|file|mimes:pem,application/octet-stream|max:5120',
        'certificate_digital_file' => 'required|file|mimetypes:pem,application/octet-stream,application/x-x509-user-cert|max:5120',
      ]);
    }

    try {
      // Procesa la nueva imagen si se subió
      if ($this->certificate_digital_file instanceof \Illuminate\Http\UploadedFile) {
        // Crear la carpeta si no existe
        $directory = 'assets/certificates';
        if (!Storage::disk('public')->exists($directory)) {
          Storage::disk('public')->makeDirectory($directory);
        }

        // Eliminar la imagen anterior si existe
        if ($this->oldcertificate_digital_file) {
          Storage::disk('public')->delete($directory . '/' . $this->oldcertificate_digital_file);
        }

        // Guardar la nueva imagen
        //$certificateName = uniqid() . '.' . $this->certificate_digital_file->extension();
        $certificateName = uniqid() . '.' . $this->certificate_digital_file->getClientOriginalExtension();
        $this->certificate_digital_file->storeAs($directory, $certificateName, 'public');
        $validatedData['certificate_digital_file'] = $certificateName;
      } else {
        // Mantener la imagen anterior
        $validatedData['certificate_digital_file'] = $this->oldcertificate_digital_file;
      }

      // Encuentra el registro existente
      $record = BusinessLocation::findOrFail($recordId);

      // Actualiza el usuario
      $record->update([
        // Crear el usuario con la contraseña encriptada
        'business_id' => $validatedData['business_id'],
        'location_parent_id' => $validatedData['location_parent_id'] ?? null,
        'code' => $validatedData['code'],
        'name' => $validatedData['name'],
        'commercial_name' => $validatedData['commercial_name'],
        'admin_invoice_layout_id' => $validatedData['admin_invoice_layout_id'],
        'pos_invoice_layout_id' => $validatedData['pos_invoice_layout_id'],
        'selling_price_group_id' => $validatedData['selling_price_group_id'] ?? null,
        'admin_quotation_layout_id' => $validatedData['admin_quotation_layout_id'] ?? null,
        'pos_quotation_layout_id' => $validatedData['pos_quotation_layout_id'] ?? null,
        'print_receipt_on_invoice' => $validatedData['print_receipt_on_invoice'] ?? true,
        'receipt_printer_type' => $validatedData['receipt_printer_type'] ?? 'browser',
        'printer_id' => $validatedData['printer_id'] ?? null,
        'phone_code' => $validatedData['phone_code'] ?? null,
        'phone' => $validatedData['phone'] ?? null,
        'email' => $validatedData['email'] ?? null,
        'website' => $validatedData['website'] ?? null,
        'environment' => $validatedData['environment'] ?? null,
        'api_key' => $validatedData['api_key'] ?? null,
        'password' => $validatedData['password'] ?? null,
        'identification_type_id' => $validatedData['identification_type_id'] ?? null,
        'identification' => $validatedData['identification'] ?? null,
        'country_id' => $validatedData['country_id'],
        'zip_code' => $validatedData['zip_code'],
        'province_id' => $validatedData['province_id'],
        'canton_id' => $validatedData['canton_id'],
        'district_id' => $validatedData['district_id'],
        'address' => $validatedData['address'] ?? null,
        'other_signs' => $validatedData['other_signs'] ?? null,
        'certificate_pin' => $validatedData['certificate_pin'] ?? null,
        'api_user_hacienda' => $validatedData['api_user_hacienda'] ?? null,
        'api_password' => $validatedData['api_password'] ?? null,
        'proveedor' => $validatedData['proveedor'] ?? null,
        'certificate_digital_file' => $validatedData['certificate_digital_file'] ?? null,
        'registrofiscal8707' => $validatedData['registrofiscal8707'] ?? null,
        'numero_sucursal' => $validatedData['numero_sucursal'] ?? null,
        'numero_punto_venta' => $validatedData['numero_punto_venta'] ?? null,
      ]);

      // Obtener los IDs de actividades económicas o un array vacío si no hay datos
      $economicActivitiesIds = !empty($validatedData['economicActivities'])
        ? array_map(fn($activity) => is_array($activity) ? $activity['id'] : $activity, $validatedData['economicActivities'])
        : [];

      // Sincronizar la relación (esto eliminará todas las relaciones si el array es vacío)
      $record->economicActivities()->sync($economicActivitiesIds);

      // Guardar secuencias
      foreach ($this->sequences as $type => $value) {
        DocumentSequence::updateOrCreate(
          [
            'emitter_id' => $this->location->id,
            'document_type' => $type
          ],
          ['current_sequence' => $value]
        );
      }

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

  public function updated($property)
  {
    // $property: The name of the current property that was updated
    if ($property === 'country_id' || $property === 'province_id' || $property === 'canton_id' || $property === 'district_id') {
      if ($property == 'province_id') {
        $this->canton_id = null;
        $this->district_id = null;
      } else
      if ($property == 'canton_id') {
        $this->district_id = null;
      }
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
      $record = BusinessLocation::findOrFail($recordId);

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
      'location_parent_id',
      'code',
      'name',
      'commercial_name',
      'admin_invoice_layout_id',
      'pos_invoice_layout_id',
      'admin_quotation_layout_id',
      'pos_quotation_layout_id',
      'selling_price_group_id',
      'print_receipt_on_invoice',
      'receipt_printer_type',
      'printer_id',
      'phone_code',
      'phone',
      'email',
      'website',
      'environment',
      'identification_type_id',
      'identification',
      'country_id',
      'zip_code',
      'province_id',
      'canton_id',
      'district_id',
      'address',
      'other_signs',
      'certificate_pin',
      'api_user_hacienda',
      'api_password',
      'certificate_digital_file',
      'proveedor',
      'registrofiscal8707',
      'economicActivities',
      'active',
      'closeForm',
      'numero_sucursal',
      'numero_punto_venta',
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
      ->where('datatable_name', 'business-location-datatable')
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
    'filter_commercial_name' => NULL,
    'filter_identification' => NULL,
    'filter_phone' => NULL,
    'filter_email' => NULL,
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
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'name',
        'orderName' => 'name',
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
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'commercial_name',
        'orderName' => 'commercial_name',
        'label' => __('Commercial Name'),
        'filter' => 'filter_commercial_name',
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
        'field' => 'identification',
        'orderName' => 'business_locations.identification',
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
        'orderName' => 'business_locations.phone',
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
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'email',
        'orderName' => 'business_locations.email',
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

  public function resetCertificate()
  {
    $this->certificate_digital_file = null; // Limpia la propiedad `photo`
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
