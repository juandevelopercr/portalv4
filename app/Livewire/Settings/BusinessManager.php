<?php

namespace App\Livewire\Settings;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\CentroCosto;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BusinessManager extends Component
{
  use WithFileUploads;

  public $recordId = 1; // Siempre trabajaremos con el registro con id = 1
  public $name;
  public $business_type = 'retail';
  public $currency_id;
  public $start_date;
  public $owner_id;
  public $time_zone = 'America/Costa_Rica';
  public $logo;
  public $oldlogo;
  public $sku_prefix;
  public $enable_product_expiry = 0;
  public $expiry_type = 'add_expiry';
  public $on_product_expiry = 'keep_selling';
  public $stock_expiry_alert_days = 30;
  public $enable_brand = 1;
  public $enable_category = 1;
  public $default_unit;
  public $enable_sub_units = 0;
  public $enable_racks = 0;
  public $enable_row = 0;
  public $enable_editing_product_from_purchase = 1;
  public $enable_inline_tax = 1;
  public $enable_inline_tax_purchase = 1;
  public $currency_symbol_placement = 'before';
  public $host_smpt;
  public $user_smtp;
  public $pass_smtp;
  public $puerto_smpt;
  public $smtp_encryptation;
  public $email_notificacion_smtp;
  public $proveedorSistemas;
  public $registro_medicamento;
  public $forma_farmaceutica;
  public $notification_email;

  public $host_imap;
  public $user_imap;
  public $pass_imap;
  public $puerto_imap;
  public $imap_encryptation;

  public $active;

  public $currencies;

  public $emisores;

  public $validatedEmails; // Almacena correos válidos
  public $invalidEmails; // Almacena correos inválidos

  public function mount()
  {
    // Cargar el registro con id = 1 al iniciar el componente
    $this->loadRecord();
    $this->emisores = BusinessLocation::orderBy('name', 'ASC')->get();
    $this->currencies = Currency::orderBy('code', 'ASC')->get();

    $this->validatedEmails = [];
    $this->invalidEmails = [];
  }

  public function loadRecord()
  {
    // Buscar el registro con id = 1
    $record = Business::find($this->recordId);

    // Si no existe, crear una nueva instancia con valores predeterminados
    if (!$record) {
      $record = new Business();
      $record->active = 1; // Valor predeterminado
      $record->start_date = Carbon::now()->toDateString(); // Formato Y-m-d
    }

    if ($record) {
      // Asignar los valores del registro a las propiedades del componente
      $this->name = $record->name;
      $this->business_type = $record->business_type;
      $this->currency_id = $record->currency_id;
      $this->start_date = $record->start_date;
      $this->owner_id = $record->owner_id;
      $this->time_zone = $record->time_zone;
      $this->logo = $record->logo;
      $this->sku_prefix = $record->sku_prefix;
      $this->enable_product_expiry = $record->enable_product_expiry;
      $this->expiry_type = $record->expiry_type;
      $this->on_product_expiry = $record->on_product_expiry;
      $this->stock_expiry_alert_days = $record->stock_expiry_alert_days;
      $this->enable_brand = $record->enable_brand;
      $this->enable_category = $record->enable_category;
      $this->default_unit = $record->default_unit;
      $this->enable_sub_units = $record->enable_sub_units;
      $this->enable_racks = $record->enable_racks;
      $this->enable_row = $record->enable_row;
      $this->enable_editing_product_from_purchase = $record->enable_editing_product_from_purchase;
      $this->enable_inline_tax = $record->enable_inline_tax;
      $this->enable_inline_tax_purchase = $record->enable_inline_tax_purchase;
      $this->currency_symbol_placement = $record->currency_symbol_placement;
      $this->host_smpt = $record->host_smpt;
      $this->user_smtp = $record->user_smtp;
      $this->pass_smtp = $record->pass_smtp;
      $this->puerto_smpt = $record->puerto_smpt;
      $this->smtp_encryptation = $record->smtp_encryptation;
      $this->email_notificacion_smtp = $record->email_notificacion_smtp;
      $this->proveedorSistemas    = $record->proveedorSistemas;
      $this->registro_medicamento = $record->registro_medicamento;
      $this->forma_farmaceutica   = $record->forma_farmaceutica;
      $this->notification_email = $record->notification_email;
      $this->host_imap = $record->host_imap;
      $this->user_imap = $record->user_imap;
      $this->pass_imap = $record->pass_imap;
      $this->puerto_imap = $record->puerto_imap;
      $this->imap_encryptation = $record->imap_encryptation;
      $this->active = $record->active;
    }

    $this->oldlogo = $record->logo;
  }

  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'required_if' => 'El campo :attribute es obligatorio cuando el tipo es :value.',
      'required_with' => 'El campo :attribute es obligatorio.',
      'numeric' => 'El campo :attribute debe ser un número válido.',
      'min' => 'El campo :attribute debe tener al menos :min caracteres.',
      'max' => 'El campo :attribute no puede exceder :max caracteres.',
      'in' => 'El campo :attribute no es válido.',
      'exists' => 'El campo :attribute no existe en el sistema.',
      'string' => 'El campo :attribute debe ser texto.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'proforma_no.required' => 'El campo proforma es obligatorio cuando el tipo de documento es PR.',
      'consecutivo.required' => 'El campo consecutivo es obligatorio para documentos que no sean proforma.',
    ];
  }

  public function update()
  {
    // Validar los datos
    $validatedData = $this->validate([
      'name' => 'required|string|max:255',
      'business_type' => 'nullable|string',
      'currency_id' => 'required|integer|exists:currencies,id',
      'start_date' => 'nullable|date',
      'time_zone' => 'nullable|string',
      'sku_prefix' => 'nullable|string',
      'enable_product_expiry' => 'nullable|boolean',
      'expiry_type' => 'nullable|string',
      'on_product_expiry' => 'nullable|string',
      'stock_expiry_alert_days' => 'nullable|integer',
      'enable_brand' => 'nullable|boolean',
      'enable_category' => 'nullable|boolean',
      'default_unit' => 'nullable|string',
      'enable_sub_units' => 'nullable|boolean',
      'enable_racks' => 'nullable|boolean',
      'enable_row' => 'nullable|boolean',
      'enable_editing_product_from_purchase' => 'nullable|boolean',
      'enable_inline_tax' => 'nullable|boolean',
      'enable_inline_tax_purchase' => 'nullable|boolean',
      'currency_symbol_placement' => 'nullable|string',
      'host_smpt' => 'nullable|string',
      'user_smtp' => 'nullable|string',
      'pass_smtp' => 'nullable|string',
      'puerto_smpt' => 'nullable|string',
      'smtp_encryptation' => 'nullable|string',
      'email_notificacion_smtp' => 'nullable|string',
      'proveedorSistemas'   => 'required|string|max:20',
      'registro_medicamento' => 'nullable|string|max:100',
      'forma_farmaceutica'  => 'nullable|string|max:3',
      'notification_email' => 'nullable|string',
      'host_imap' => 'nullable|string',
      'user_imap' => 'nullable|string',
      'pass_imap' => 'nullable|string',
      'puerto_imap' => 'nullable|string',
      'imap_encryptation' => 'nullable|string',
      'active' => 'nullable|boolean'
    ]);

    // Validar la imagen solo si existe una nueva imagen
    if ($this->logo && $this->logo !== $this->oldlogo) {
      $this->validate([
        'logo' => 'image|mimes:jpg,jpeg,png,gif|max:2048',
      ]);
    }

    try {
      // Procesa la nueva imagen si se subió
      if ($this->logo instanceof \Illuminate\Http\UploadedFile) {
        // Crear la carpeta si no existe
        $directory = 'assets/img/logos';
        if (!Storage::disk('public')->exists($directory)) {
          Storage::disk('public')->makeDirectory($directory);
        }

        // Eliminar la imagen anterior si existe
        if ($this->oldlogo) {
          Storage::disk('public')->delete($directory . '/' . $this->oldlogo);
        }

        // Guardar la nueva imagen
        $imageName = uniqid() . '.' . $this->logo->extension();
        $this->logo->storeAs($directory, $imageName, 'public');
        $validatedData['logo'] = $imageName;
      } else {
        // Mantener la imagen anterior
        $validatedData['logo'] = $this->oldlogo;
      }

      // Usar updateOrCreate para actualizar o crear el registro
      $business = Business::updateOrCreate(
        ['id' => $this->recordId], // Condición de búsqueda
        $validatedData // Datos para actualizar o crear
      );

      // Actualizar el valor de oldlogo si se subió una nueva imagen
      if ($this->logo instanceof \Illuminate\Http\UploadedFile) {
        $this->oldlogo = $validatedData['logo'];
      }

      /*
      // 1. Actualizar configuración en tiempo de ejecución
      $this->setMailConfig();

      // 2. Actualizar .env para persistencia
      $validated = [
        'MAIL_HOST' => $this->host_smpt,
        'MAIL_PORT' => $this->puerto_smpt,
        'MAIL_USERNAME' => $this->user_smtp,
        'MAIL_PASSWORD' => $this->pass_smtp,
        'MAIL_ENCRYPTION' => $this->smtp_encryptation,
        'MAIL_FROM_ADDRESS' => $this->user_smtp,

        'IMAP_HOST' => $this->host_imap,
        'IMAP_PORT' => $this->puerto_imap,
        'IMAP_USERNAME' => $this->user_imap,
        'IMAP_PASSWORD' => $this->pass_imap,
        'IMAP_ENCRYPTION' => $this->imap_encryptation
      ];

      $this->updateEnvFile($validated);

      Artisan::call('config:clear');
      Artisan::call('cache:clear');
      */

      // Limpiar caché de configuración
      //Artisan::call('config:clear');

      // Mostrar notificación de éxito
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The record has been updated'),
      ]);
    } catch (\Exception $e) {
      // Mostrar notificación de error
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function resetPhoto()
  {
    $this->logo = null; // Limpia la propiedad `photo`
  }

  public function updated($property)
  {
    if ($property == 'notification_email') {
      $this->updatedEmails();
    }
  }

  public function updatedEmails()
  {
    // Divide la cadena en correos separados por , o ;
    $emailList = preg_split('/[,;]+/', $this->notification_email);

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
      $this->addError('notification_email', 'Hay correos inválidos: ' . implode(', ', $this->invalidEmails));
    } else {
      $this->resetErrorBag('notification_email'); // Limpiar errores si todos son válidos
    }
  }

  public function render()
  {
    return view('livewire.settings.business-manager');
  }

  protected function setMailConfig()
  {
    // Actualizar configuración en tiempo de ejecución
    config([
      'mail.mailers.smtp.host' => $this->host_smpt,
      'mail.mailers.smtp.port' => $this->puerto_smpt,
      'mail.mailers.smtp.encryption' => $this->smtp_encryptation,
      'mail.mailers.smtp.username' => $this->user_smtp,
      'mail.mailers.smtp.password' => $this->pass_smtp,
      'mail.from.address' => $this->user_smtp,

      // Configuración IMAP si usas la librería imap
      'imap.host' => $this->host_imap,
      'imap.port' => $this->puerto_imap,
      'imap.encryption' => $this->imap_encryptation,
      'imap.username' => $this->user_imap,
      'imap.password' => $this->pass_imap,
    ]);
  }

  private function updateEnvFile(array $values)
  {
    $envPath = base_path('.env');

    // Leer el contenido actual de manera segura
    $envContent = file_exists($envPath) ? File::get($envPath) : '';

    $updatedContent = $envContent;

    foreach ($values as $key => $value) {
      // Escapar comillas y saltos de línea
      $escapedValue = '"' . str_replace(['"', "\n", "\r"], ['\"', '', ''], $value) . '"';

      // Patrones para buscar: variable normal o comentada
      $patterns = [
        "/^{$key}=.*/m",           // Variable activa (MAIL_HOST=...)
        "/^#\s*{$key}=.*/m",       // Variable comentada (# MAIL_HOST=...)
      ];

      $replaced = false;

      // Intentar reemplazar en cada patrón
      foreach ($patterns as $pattern) {
        if (preg_match($pattern, $updatedContent)) {
          $updatedContent = preg_replace(
            $pattern,
            "{$key}={$escapedValue}",
            $updatedContent
          );
          $replaced = true;
          break;
        }
      }

      // Si no se encontró, agregar al final
      if (!$replaced) {
        $updatedContent .= PHP_EOL . "{$key}={$escapedValue}";
      }
    }

    // Escribir solo si hubo cambios
    if ($updatedContent !== $envContent) {
      File::put($envPath, $updatedContent);
    }

    // Actualizar entorno actual
    foreach ($values as $key => $value) {
      putenv("{$key}={$value}");
      $_ENV[$key] = $value;
      $_SERVER[$key] = $value;
    }
  }
}
