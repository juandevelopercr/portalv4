<?php

namespace App\Livewire\Contacts\Contactos;

use App\Models\AreaPractica;
use App\Models\ContactContacto;
use App\Models\DataTableConfig;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

class ContactoManager extends Component
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'htSearch', history: true)]
  public $search = '';

  #[Url(as: 'htSortBy', history: true)]
  public $sortBy = 'contacts_contactos.name';

  #[Url(as: 'htSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'htPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $contact_id;
  public $name;
  public $email;
  public $telefono;
  public $ext;
  public $celular;
  public $department_id;

  public $closeForm = false;

  public $columns;
  public $defaultColumns;

  public $contactName;
  public $departments;

  protected function getModelClass(): string
  {
    return ContactContacto::class;
  }

  public function mount($contact_id, $contactName)
  {
    $this->contact_id = $contact_id;
    $this->contactName = $contactName;
    $this->departments = Department::orderBy('name', 'ASC')->get();
    $this->refresDatatable();
  }

  public function render()
  {
    $records = ContactContacto::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('contacts_contactos.contact_id', '=', $this->contact_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.contacts.contactos.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->action = 'create';
    $this->dispatch('reinitContactContactSelec2Form'); // Enviar evento al frontend
    $this->dispatch('scroll-to-top');
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate([
      'contact_id' => 'required|exists:contacts,id',
      'name' => 'required|max:100',
      'email' => 'required|email|max:59',
      'telefono' => 'required|max:14',
      'ext' => 'nullable|max:6',
      'celular' => 'nullable|max:14',
      'department_id' => 'nullable|exists:departments,id'
    ], [
      'contact_id.required' => 'El contacto es obligatorio',
      'contact_id.exists' => 'El contacto seleccionado no existe',
      'email.required' => 'El email es obligatorio',
      'email.email' => 'Debe ser un email válido',
      'telefono.required' => 'El teléfono es obligatorio',
      'department_id.exists' => 'El departamento no existe'
    ], [
      'contact_id' => 'contacto',
      'name' => 'nombre',
      'email' => 'correo electrónico',
      'telefono' => 'teléfono',
      'ext' => 'ext',
      'celular' => 'celular',
      'department_id' => 'depatamento'
    ]);

    try {

      // Crear el usuario con la contraseña encriptada
      $record = ContactContacto::create([
        'contact_id'                    => $validatedData['contact_id'],
        'name'                          => $validatedData['name'],
        'email'                         => $validatedData['email'],
        'telefono'                      => $validatedData['telefono'],
        'ext'                           => $validatedData['ext'],
        'celular'                       => $validatedData['celular'] ?? 0,
        'department_id'                 => $validatedData['department_id'] ?? 0
      ]);

      $closeForm = $this->closeForm;

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }

      $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
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

    $record = ContactContacto::find($recordId);
    $this->recordId = $recordId;

    $this->contact_id             = $record->contact_id;
    $this->name                   = $record->name;
    $this->email                  = $record->email;
    $this->telefono               = $record->telefono;
    $this->ext                    = $record->ext;
    $this->celular                = $record->celular;
    $this->department_id          = $record->department_id;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
    $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
  }

  public function update()
  {
    $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
    $recordId = $this->recordId;

    // Valida los datos
    $validatedData = $this->validate([
      'contact_id' => 'required|exists:contacts,id',
      'name' => 'required|max:100',
      'email' => 'required|email|max:59',
      'telefono' => 'required|max:14',
      'ext' => 'nullable|max:6',
      'celular' => 'nullable|max:14',
      'department_id' => 'nullable|exists:departments,id'
    ], [
      'contact_id.required' => 'El contacto es obligatorio',
      'contact_id.exists' => 'El contacto seleccionado no existe',
      'email.required' => 'El email es obligatorio',
      'email.email' => 'Debe ser un email válido',
      'telefono.required' => 'El teléfono es obligatorio',
      'department_id.exists' => 'El departamento no existe'
    ], [
      'contact_id' => 'contacto',
      'name' => 'nombre',
      'email' => 'correo electrónico',
      'telefono' => 'teléfono',
      'ext' => 'ext',
      'celular' => 'celular',
      'department_id' => 'depatamento'
    ]);

    try {
      // Encuentra el registro existente
      $record = Contactcontacto::findOrFail($recordId);

      // Actualiza el usuario
      $record->update([
        'contact_id'                    => $validatedData['contact_id'],
        'name'                          => $validatedData['name'],
        'email'                         => $validatedData['email'],
        'telefono'                      => $validatedData['telefono'],
        'ext'                           => $validatedData['ext'],
        'celular'                       => $validatedData['celular'] ?? 0,
        'department_id'                 => $validatedData['department_id'] ?? 0
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
      $record = Contactcontacto::findOrFail($recordId);

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
      'email',
      'telefono',
      'ext',
      'celular',
      'department_id'
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
    $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'contactContact-datatable')
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
    'filter_email' => NULL,
    'filter_phone' => NULL,
    'filter_ext' => NULL,
    'filter_celular' => NULL,
    'filter_department' => NULL
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'name',
        'orderName' => 'contacts_contactos.name',
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
        'field' => 'email',
        'orderName' => 'contacts_contactos.email',
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
        'field' => 'telefono',
        'orderName' => 'contacts_contactos.telefono',
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
        'field' => 'ext',
        'orderName' => 'contacts_contactos.ext',
        'label' => __('Ext'),
        'filter' => 'filter_ext',
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
        'field' => 'celular',
        'orderName' => 'contacts_contactos.celular',
        'label' => __('Celular'),
        'filter' => 'filter_celular',
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
        'field' => 'department_name',
        'orderName' => 'contacts_contactos.department_name',
        'label' => __('Department'),
        'filter' => 'filter_department',
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
