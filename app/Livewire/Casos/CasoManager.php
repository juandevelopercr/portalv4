<?php

namespace App\Livewire\Casos;

use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\Caratula;
use App\Models\Caso;
use App\Models\CasoEstado;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Department;
use App\Models\Garantia;
use App\Models\User;
use App\Services\DocumentSequenceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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

class CasoManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'casos.created_at';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Listados
  public $departments;
  public $banks;
  public $currencies;
  public $abogados;
  public $asistentes;
  public $garantias;
  public $caratulas;

  public $id;
  public $numero;
  public $numero_gestion;
  public $deudor;
  public $abogado_cargo_id;
  public $abogado_revisor_id;
  public $abogado_formalizador_id;
  public $asistente_id;
  public $currency_id;
  public $caratula_id;
  public $garantia_id;
  public $department_id;
  public $estado_id;
  public $numero_garantia;
  public $nombre_formalizo;
  public $bank_id;
  public $sucursal;
  public $monto;
  public $numero_tomo;
  public $asiento_presentacion;
  public $fecha_creacion;
  public $fecha_firma;
  public $fecha_presentacion;
  public $fecha_inscripcion;
  public $fecha_entrega;
  public $fecha_caratula;
  public $fecha_precaratula;
  public $costo_caso_retiro;
  public $observaciones;
  public $pendientes;
  public $fiduciaria;
  public $desarrollador;
  public $cedula;
  public $num_operacion;
  public $cedula_deudor;

  public $closeForm = false;
  // Para el consecutivo de los casos
  public $document_type = 'CASO';
  public $columns;
  public $defaultColumns;

  // calculables
  public $diasFirma = 0;
  public $diasEntrega = 0;
  public $oldAbogadoCargoId;

  // Esto para no eliminar le valor de bank_id al editar
  public bool $skipBankReset = false;

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  protected function getModelClass(): string
  {
    return Caso::class;
  }

  #[Computed]
  public function estados()
  {
    $user = Auth::user();
    if (Session::get('current_role_name') == User::BANCO)
      $estados = CasoEstado::whereIn('id', [CasoEstado::ASIGNADO, CasoEstado::FORMALIZADO, CasoEstado::EN_TRAMITE, CasoEstado::INSCRITO, CasoEstado::ENTREGADO])->orderBy('name', 'ASC')->get();
    else
      $estados = CasoEstado::orderBy('name', 'ASC')->get();
    return $estados;
  }

  public function mount()
  {
    $this->document_type = 'CASO'; // caso

    $this->currencies = Currency::orderBy('code', 'ASC')->get();
    $this->caratulas = Caratula::orderBy('name', 'ASC')->get();
    $this->setDepartments();
    $this->banks = [];

    $allowedRoles = User::ROLES_ALL_DEPARTMENTS;
    if (!in_array(Session::get('current_role_name'), $allowedRoles)) {
      $departments = Session::get('current_department');

      // Abogados (con roles y en los departamentos asignados)
      $this->abogados = User::where('active', 1)
        ->whereHas('roles', function ($query) {
          $query->whereIn('name', [User::ABOGADO, User::ABOGADO_EDITOR, User::JEFE_AREA_SENIOR]);
        })
        ->whereHas('departments', function ($query) use ($departments) {
          $query->whereIn('departments.id', $departments);
        })
        ->orderBy('name', 'ASC')
        ->get();

      // Asistentes (también deben estar en los mismos departamentos)
      $this->asistentes = User::where('active', 1)
        ->role(User::ABOGADO) // Si 'Abogado' es el rol que quieres para asistentes
        ->whereHas('departments', function ($query) use ($departments) {
          $query->whereIn('departments.id', $departments);
        })
        ->orderBy('name', 'ASC')
        ->get();

      // Garantias (también deben estar en los mismos departamentos)
      $this->garantias = Garantia::where('active', 1)
        ->whereHas('departments', function ($query) use ($departments) {
          $query->whereIn('departments.id', $departments);
        })
        ->orderBy('name', 'ASC')
        ->get();
    } else {
      $this->abogados = User::where('active', 1)
        ->whereHas('roles', function ($query) {
          $query->whereIn('name', [User::ABOGADO, User::ABOGADO_EDITOR, User::JEFE_AREA_SENIOR]);
        })
        ->orderBy('name', 'ASC')
        ->get();

      $this->asistentes = User::where('active', 1)
        ->role(User::ABOGADO) // usa el nombre exacto del rol
        ->orderBy('name', 'ASC')
        ->get();

      // Garantias (también deben estar en los mismos departamentos)
      $this->garantias = Garantia::where('active', 1)
        ->orderBy('name', 'ASC')
        ->get();
    }
    $this->refresDatatable();
  }

  public function render()
  {
    $query = Caso::search($this->search, $this->filters);

    // 🔒 Acceso según rol
    if (!in_array(session('current_role_name'),  User::ROLES_ALL_CASOS)) {
      // Puede ver casos solo de departamentos asignados
      $query->whereIn('casos.department_id', Session::get('current_department'))
        ->whereIn('casos.bank_id', Session::get('current_banks'));
    }

    // Ordenar resultados
    $query->orderBy($this->sortBy, $this->sortDir);

    // Paginación
    $records = $query->paginate($this->perPage);

    return view('livewire.casos.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    $this->active = 1;

    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  public function rules()
  {
    return [
      'document_type' => 'required|string',
      'numero' => 'nullable|string|max:18',
      'numero_gestion' => 'nullable|string|max:50',
      'deudor' => 'required|string|max:255',
      'abogado_cargo_id' => 'nullable|exists:users,id',
      'abogado_revisor_id' => 'nullable|exists:users,id',
      'abogado_formalizador_id' => 'nullable|exists:users,id',
      'asistente_id' => 'nullable|exists:users,id',
      'currency_id' => 'required|exists:currencies,id',
      'caratula_id' => 'nullable|exists:caratulas,id',
      'garantia_id' => 'nullable|exists:garantias,id',
      'department_id' => 'required|exists:departments,id',
      'estado_id' => 'required|exists:casos_estados,id',
      'numero_garantia' => 'nullable|string|max:200',
      'nombre_formalizo' => 'required|string|max:100',
      'bank_id' => 'required|exists:banks,id',
      'sucursal' => 'nullable|string|max:200',
      'monto' => 'required|numeric|min:0',
      'numero_tomo' => 'nullable|string|max:50',
      'asiento_presentacion' => 'nullable|string|max:50',
      'fecha_creacion' => 'nullable|date',
      'fecha_firma' => 'nullable|date',
      'fecha_presentacion' => 'nullable|date',
      'fecha_inscripcion' => 'nullable|date',
      'fecha_entrega' => 'nullable|date',
      'fecha_caratula' => 'nullable|date',
      'fecha_precaratula' => 'nullable|date',
      'costo_caso_retiro' => 'nullable|numeric|min:0',
      'observaciones' => 'nullable|string',
      'pendientes' => 'nullable|string',
      'fiduciaria' => 'nullable|string|max:100',
      'desarrollador' => 'nullable|string|max:100',
      'cedula' => 'nullable|string|max:20',
      'num_operacion' => 'nullable|string|max:50',
      'cedula_deudor' => 'nullable|string|max:100',
    ];
  }

  public function messages()
  {
    return [
      'deudor.required' => 'El nombre del deudor es obligatorio.',
      'nombre_formalizo.required' => 'Debe indicar quién formalizó el caso.',
      'currency_id.required' => 'Debe seleccionar la moneda.',
      'bank_id.required' => 'Debe seleccionar un banco.',
      'estado_id.required' => 'Debe seleccionar el estado del caso.',
      'department_id.required' => 'Debe seleccionar el departamento.',
      'monto.required' => 'Debe indicar el monto.',
      'monto.numeric' => 'El monto debe ser un número.',
      'fecha_*' => 'Formato de fecha inválido.',
      'costo_caso_retiro.numeric' => 'El costo debe ser un número.',
    ];
  }


  public function attributes()
  {
    return [
      'numero' => 'número',
      'numero_gestion' => 'número de gestión',
      'deudor' => 'deudor',
      'abogado_cargo_id' => 'abogado a cargo',
      'abogado_revisor_id' => 'abogado revisor',
      'abogado_formalizador_id' => 'abogado formalizador',
      'asistente_id' => 'asistente',
      'currency_id' => 'moneda',
      'caratula_id' => 'carátula',
      'garantia_id' => 'garantía',
      'department_id' => 'departamento',
      'estado_id' => 'estado',
      'numero_garantia' => 'número de garantía',
      'nombre_formalizo' => 'nombre quien formalizó',
      'bank_id' => 'banco',
      'sucursal' => 'sucursal',
      'monto' => 'monto',
      'numero_tomo' => 'número de tomo',
      'asiento_presentacion' => 'asiento de presentación',
      'fecha_creacion' => 'fecha de creación',
      'fecha_firma' => 'fecha de firma',
      'fecha_presentacion' => 'fecha de presentación',
      'fecha_inscripcion' => 'fecha de inscripción',
      'fecha_entrega' => 'fecha de entrega',
      'fecha_caratula' => 'fecha de carátula',
      'fecha_precaratula' => 'fecha de precarátula',
      'costo_caso_retiro' => 'costo del caso',
      'observaciones' => 'observaciones',
      'pendientes' => 'pendientes',
      'fiduciaria' => 'Fiduciaria',
      'desarrollador' => 'desarrollador',
      'cedula' => 'cédula',
      'num_operacion' => 'número de operación',
      'cedula_deudor' => 'cédula del deudor',
    ];
  }

  public function store()
  {
    // Validar
    $validatedData = $this->validate();

    $validatedData['fecha_firma'] = !empty($this->fecha_firma) ? Carbon::parse($this->fecha_firma)->format('Y-m-d') : $this->fecha_firma;
    $validatedData['fecha_presentacion'] = !empty($this->fecha_presentacion) ? Carbon::parse($this->fecha_presentacion)->format('Y-m-d') : $this->fecha_presentacion;
    $validatedData['fecha_inscripcion'] = !empty($this->fecha_inscripcion) ? Carbon::parse($this->fecha_inscripcion)->format('Y-m-d') : $this->fecha_inscripcion;
    $validatedData['fecha_entrega'] = !empty($this->fecha_entrega) ? Carbon::parse($this->fecha_entrega)->format('Y-m-d') : $this->fecha_entrega;
    $validatedData['fecha_caratula'] = !empty($this->fecha_caratula) ? Carbon::parse($this->fecha_caratula)->format('Y-m-d') : $this->fecha_caratula;
    $validatedData['fecha_precaratula'] = !empty($this->fecha_precaratula) ? Carbon::parse($this->fecha_precaratula)->format('Y-m-d') : $this->fecha_precaratula;

    // Generar consecutivo
    $consecutive = DocumentSequenceService::generateConsecutive(
      $validatedData['document_type'],
      $validatedData['location_id'] ?? null
    );

    $this->numero = $consecutive;
    $validatedData['numero'] = $consecutive;

    // Validar nuevamente para asegurar que el campo correcto esté presente
    $this->validate([
      'numero' => 'required|string|max:18',
    ]);

    try {
      // Iniciar la transacción
      DB::beginTransaction();

      // Crear la transacción
      $caso = Caso::create($validatedData);

      $closeForm = $this->closeForm;

      if ($caso) {
        // Commit: Confirmar todos los cambios
        DB::commit();
      }

      if ($caso->abogadoCargo) {
        $caso->estado_id = CasoEstado::ASIGNADO;
        $this->sendNotificationByEmail($caso->id);
        $caso->save();
      }

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($caso->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {
      // Rollback: Revertir los cambios en caso de error
      DB::rollBack();
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $this->skipBankReset = true;

    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Caso::with(
      'department',
      'abogadoCargo',
      'abogadoRevisor',
      'abogadoFormalizador',
      'asistente',
      'currency',
      'caratula',
      'garantia',
      'estado',
      'bank'
    )->findOrFail($recordId);
    $this->recordId = $recordId;

    $this->numero                = $record->numero;
    $this->numero_gestion        = $record->numero_gestion;
    $this->deudor                = $record->deudor;
    $this->abogado_cargo_id      = $record->abogado_cargo_id;
    $this->abogado_revisor_id    = $record->abogado_revisor_id;
    $this->abogado_formalizador_id = $record->abogado_formalizador_id;
    $this->asistente_id          = $record->asistente_id;
    $this->currency_id           = $record->currency_id;
    $this->caratula_id           = $record->caratula_id;
    $this->garantia_id           = $record->garantia_id;
    $this->department_id         = $record->department_id;
    $this->estado_id             = $record->estado_id;
    $this->numero_garantia       = $record->numero_garantia;
    $this->nombre_formalizo      = $record->nombre_formalizo;
    $this->bank_id               = $record->bank_id;
    $this->sucursal              = $record->sucursal;
    $this->monto                 = $record->monto;
    $this->numero_tomo           = $record->numero_tomo;
    $this->asiento_presentacion  = $record->asiento_presentacion;
    $this->fecha_creacion        = $record->fecha_creacion;
    $this->fecha_firma           = $record->fecha_firma;
    $this->fecha_presentacion    = $record->fecha_presentacion;
    $this->fecha_inscripcion     = $record->fecha_inscripcion;
    $this->fecha_entrega         = $record->fecha_entrega;
    $this->fecha_caratula        = $record->fecha_caratula;
    $this->fecha_precaratula     = $record->fecha_precaratula;
    $this->costo_caso_retiro     = $record->costo_caso_retiro;
    $this->observaciones         = $record->observaciones;
    $this->pendientes            = $record->pendientes;
    $this->fiduciaria            = $record->fiduciaria;
    $this->desarrollador         = $record->desarrollador;
    $this->cedula                = $record->cedula;
    $this->num_operacion         = $record->num_operacion;
    $this->cedula_deudor         = $record->cedula_deudor;


    $this->fecha_firma = !empty($record->fecha_firma) ? Carbon::parse($record->fecha_firma)->format('d-m-Y') : $record->fecha_firma;
    $this->fecha_presentacion = !empty($record->fecha_presentacion) ? Carbon::parse($record->fecha_presentacion)->format('d-m-Y') : $record->fecha_presentacion;
    $this->fecha_inscripcion = !empty($record->fecha_inscripcion) ? Carbon::parse($record->fecha_inscripcion)->format('d-m-Y') : $record->fecha_inscripcion;
    $this->fecha_entrega = !empty($record->fecha_entrega) ? Carbon::parse($record->fecha_entrega)->format('d-m-Y') : $record->fecha_entrega;
    $this->fecha_caratula = !empty($record->fecha_caratula) ? Carbon::parse($record->fecha_caratula)->format('d-m-Y') : $record->fecha_caratula;
    $this->fecha_precaratula = !empty($record->fecha_precaratula) ? Carbon::parse($record->fecha_precaratula)->format('d-m-Y') : $record->fecha_precaratula;


    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->oldAbogadoCargoId = $this->abogado_cargo_id;

    // Se emite este evento para los componentes hijos
    $this->dispatch('updateCasoContext', [
      'caso_id'    => $record->id,
    ]);

    $this->diasFirma = Helpers::getDiasTranscurridos($this->fecha_firma, $this->fecha_inscripcion);
    $this->diasEntrega = Helpers::getDiasTranscurridos($this->fecha_firma, $this->fecha_entrega);

    $this->action = 'edit';

    $this->setbanks();

    $this->dispatch('select2');
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validar
    $validatedData = $this->validate();

    $validatedData['fecha_firma'] = !empty($this->fecha_firma) ? Carbon::parse($this->fecha_firma)->format('Y-m-d') : $this->fecha_firma;
    $validatedData['fecha_presentacion'] = !empty($this->fecha_presentacion) ? Carbon::parse($this->fecha_presentacion)->format('Y-m-d') : $this->fecha_presentacion;
    $validatedData['fecha_inscripcion'] = !empty($this->fecha_inscripcion) ? Carbon::parse($this->fecha_inscripcion)->format('Y-m-d') : $this->fecha_inscripcion;
    $validatedData['fecha_entrega'] = !empty($this->fecha_entrega) ? Carbon::parse($this->fecha_entrega)->format('Y-m-d') : $this->fecha_entrega;
    $validatedData['fecha_caratula'] = !empty($this->fecha_caratula) ? Carbon::parse($this->fecha_caratula)->format('Y-m-d') : $this->fecha_caratula;
    $validatedData['fecha_precaratula'] = !empty($this->fecha_precaratula) ? Carbon::parse($this->fecha_precaratula)->format('Y-m-d') : $this->fecha_precaratula;

    try {
      // Encuentra el registro existente
      $record = Caso::findOrFail($recordId);

      // Actualizar
      $record->update($validatedData);

      if (trim($this->oldAbogadoCargoId) != trim($record->abogado_cargo_id) && !empty($this->abogado_cargo_id) && !is_null($this->abogado_cargo_id)) {
        $record->estado_id = CasoEstado::ASIGNADO;
        $this->sendNotificationByEmail($record->id);
        if ($record->save())
          $this->oldAbogadoCargoId = $this->abogado_cargo_id;
      }

      $closeForm = $this->closeForm;

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();
      $this->dispatch('scroll-to-top');

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);

      $this->dispatch('updateCasoContext', [
        'caso_id' => $record->id,
      ]);

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
      $record = Caso::findOrFail($recordId);

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

  public function confirmarAccionNotificaion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
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

  #[On('sendNotificacion')]
  public function sendNotificacion($recordId)
  {
    try {
      $record = Caso::findOrFail($recordId);

      $this->sendNotificationByEmail($record->id);
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Ha ocurrido un error al enviar la notificación') . ' ' . $e->getMessage()]);
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
      'numero',
      'numero_gestion',
      'deudor',
      'abogado_cargo_id',
      'abogado_revisor_id',
      'abogado_formalizador_id',
      'asistente_id',
      'currency_id',
      'caratula_id',
      'garantia_id',
      'department_id',
      'estado_id',
      'numero_garantia',
      'nombre_formalizo',
      'bank_id',
      'sucursal',
      'monto',
      'numero_tomo',
      'asiento_presentacion',
      'fecha_creacion',
      'fecha_firma',
      'fecha_presentacion',
      'fecha_inscripcion',
      'fecha_entrega',
      'fecha_caratula',
      'fecha_precaratula',
      'costo_caso_retiro',
      'observaciones',
      'pendientes',
      'fiduciaria',
      'desarrollador',
      'cedula',
      'num_operacion',
      'cedula_deudor',
      'closeForm'
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

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->dispatch('select2');
  }

  public function updatedDepartmentId($value)
  {
    if ($this->skipBankReset) {
      $this->skipBankReset = false;
      return;
    }

    $this->bank_id = null;
    $this->setbanks();
    //$this->dispatch('select2');
  }

  public function setDepartments()
  {
    $this->departments = Department::whereIn('id', Session::get('current_department'))->orderBy('name', 'ASC')->get();
  }

  public function setbanks()
  {
    $bancos = [];
    if ($this->action == 'list') {
      $bancos = Bank::whereIn('id', session('current_banks'))->orderBy('name', 'ASC')->get();
    } else {
      if ($this->department_id) {
        if ($this->department_id) {
          // Obtener todos los bancos del departamento seleccionado
          $departmentBanks = Department::find($this->department_id)->banks()->pluck('id')->toArray();

          // Filtrar solo los bancos a los que el usuario tiene acceso
          $authorizedBanks = session('current_banks', []);

          // Si el usuario tiene acceso completo, usar todos los bancos del departamento
          if (session('is_full_access', false)) {
            $bancos = Bank::whereIn('id', $departmentBanks)
              ->orderBy('name', 'ASC')
              ->get();
          }
          // Si no es acceso completo, intersectar con los bancos autorizados
          else {
            $allowedBanks = array_intersect($departmentBanks, $authorizedBanks);
            $bancos = Bank::whereIn('id', $allowedBanks)
              ->orderBy('name', 'ASC')
              ->get();
          }
        } else {
          $bancos = collect();
        }
      }
    }
    $this->banks = $bancos;
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'casos-datatable')
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
    'filter_numero' => NULL,
    'filter_numero_gestion' => NULL,
    'filter_fecha_creacion' => NULL,
    'filter_deudor' => NULL,
    'filter_department' => NULL,
    'filter_abogado_cargo' => NULL,
    'filter_bank' => NULL,
    'filter_numero_tomo' => NULL,
    'filter_asiento_presentacion' => NULL,
    'filter_garantia' => NULL,
    'filter_fecha_firma' => NULL,
    'filter_fecha_entrega' => NULL,
    'filter_fecha_presentacion' => NULL,
    'filter_fecha_inscripcion' => NULL,
    'filter_monto_usd' => NULL,
    'filter_monto_crc' => NULL,
    'filter_estado' => NULL,
    'filter_action' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'numero',
        'orderName' => 'numero',
        'label' => __('Número'),
        'filter' => 'filter_numero',
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
        'field' => 'numero_gestion',
        'orderName' => 'numero_gestion',
        'label' => __('Número de gestion'),
        'filter' => 'filter_numero_gestion',
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
        'field' => 'fecha_creacion',
        'orderName' => 'fecha_creacion',
        'label' => __('Fecha de creación'),
        'filter' => 'filter_fecha_creacion',
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
        'field' => 'deudor',
        'orderName' => 'deudor',
        'label' => __('Deudor'),
        'filter' => 'filter_deudor',
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
        'field' => 'department',
        'orderName' => 'departments.name',
        'label' => __('Department'),
        'filter' => 'filter_department',
        'filter_type' => 'select',
        'filter_sources' => 'departments',
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
        'field' => 'abogado_cargo',
        'orderName' => 'uc.name',
        'label' => __('Abogado a cargo'),
        'filter' => 'filter_abogado_cargo',
        'filter_type' => 'select',
        'filter_sources' => 'abogados',
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
        'field' => 'bank_name',
        'orderName' => 'banks.name',
        'label' => __('Bank'),
        'filter' => 'filter_bank',
        'filter_type' => 'select',
        'filter_sources' => 'banks',
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
        'field' => 'numero_tomo',
        'orderName' => 'filter_numero_tomo',
        'label' => __('Número de tomo'),
        'filter' => 'filter_numero_tomo',
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
        'field' => 'asiento_presentacion',
        'orderName' => 'filter_asiento_presentacion',
        'label' => __('Asiento presentación'),
        'filter' => 'filter_numero_tomo',
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
        'field' => 'garantia',
        'orderName' => 'garantias.name',
        'label' => __('Garantía / Acto'),
        'filter' => 'filter_garantia',
        'filter_type' => 'select',
        'filter_sources' => 'garantias',
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
        'field' => 'fecha_firma',
        'orderName' => 'fecha_firma',
        'label' => __('Fecha de firma'),
        'filter' => 'filter_fecha_firma',
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
        'field' => 'fecha_entrega',
        'orderName' => 'fecha_entrega',
        'label' => __('Fecha de entrega'),
        'filter' => 'filter_fecha_entrega',
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
        'field' => 'fecha_presentacion',
        'orderName' => 'filter_fecha_presentacion',
        'label' => __('Fecha de presentación'),
        'filter' => 'filter_fecha_presentacion',
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
        'field' => 'fecha_inscripcion',
        'orderName' => 'filter_fecha_inscripcion',
        'label' => __('Fecha de inscripción'),
        'filter' => 'filter_fecha_inscripcion',
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
        'field' => 'monto_usd',
        'orderName' => '',
        'label' => __('Monto USD'),
        'filter' => 'filter_monto_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tMontoUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'monto_crc',
        'orderName' => '',
        'label' => __('Monto CRC'),
        'filter' => 'filter_monto_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tMontoCrc',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'estado',
        'orderName' => 'casos_estados.name',
        'label' => __('Estado'),
        'filter' => 'filter_estado',
        'filter_type' => 'select',
        'filter_sources' => 'estados',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlStatus',
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
    foreach (array_keys($this->filters) as $key) {
      $this->filters[$key] = null;
    }

    $this->selectedIds = [];
    $this->dispatch('clearFilterselect2');
  }


  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  #[On('clonar')]
  public function clonar($recordId)
  {
    DB::beginTransaction();

    try {
      $original = Caso::findOrFail($recordId);

      // Clonar el producto principal
      $cloned = $original->replicate();
      $cloned->numero = $original->numero . '999 (Copia)';
      $cloned->fecha_creacion = Carbon::now()->toDateString();
      $cloned->save();

      DB::commit();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The case has been successfully cloned')]);

      return response()->json(['success' => true, 'message' => 'Caso clonado exitosamente', 'id' => $cloned->id]);
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. While cloning the case') . ' ' . $e->getMessage()]);
      Log::error('Error al clonar producto.', ['error' => $e->getMessage()]);
    }
  }

  public function downloadCasoPendiente($casoId)
  {
    $this->prepareExportPendientes($casoId);
  }

  private function prepareExportPendientes($casoId)
  {
    Log::warning("datos pasados a preparar exportación", [
      '$casoId' => $casoId
    ]);

    $key = uniqid('export_', true);

    if (empty($casoId) || !is_numeric($casoId)) {
      Log::warning("ID inválido al preparar exportación", ['casoId' => $casoId]);
      return;
    }

    cache()->put($key, [
      'casoId' => $casoId
    ], now()->addMinutes(5));

    $url = route('exportacion.caso.pendientes' . '.preparar', ['key' => $key]);
    $downloadBase = '/descargar-exportacion-caso-pendientes';

    Log::info('Reporte', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);

    $this->dispatch('exportReady', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);
  }

  public function sendNotificationByEmail($casoId)
  {
    $caso = Caso::findOrFail($casoId);

    $sent = Helpers::sendNotificacionCasoAsignadoByEmail($caso);

    if ($sent) {
      $menssage = __('An email has been sent to the following addresses:') . ' ' . $caso->abogadoCargo->email;

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => $menssage
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }
}
