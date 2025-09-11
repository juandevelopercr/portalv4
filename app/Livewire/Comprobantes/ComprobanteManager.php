<?php

namespace App\Livewire\Comprobantes;

use \Exception;
use App\Helpers\Helpers;
use App\Models\BusinessLocation;
use App\Models\Comprobante;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Transaction;
use App\Services\DocumentSequenceService;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use download;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use SimpleXMLElement;

class ComprobanteManager extends Component
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $sortBy = 'comprobantes.id';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public array $selectedIds = [];
  public bool $selectAll = false;

  // Campos del comprobante
  public $location_id;
  public $key;
  public $consecutivo;
  public $fecha_emision;
  public $emisor_nombre;
  public $emisor_tipo_identificacion;
  public $emisor_numero_identificacion;

  public $receptor_nombre;
  public $receptor_tipo_identificacion;
  public $receptor_numero_identificacion;

  public $tipo_cambio;
  public $total_impuestos;
  public $total_exento;
  public $total_gravado;
  public $total_descuentos;
  public $total_otros_cargos;
  public $total_comprobante;
  public $moneda = 'CRC';
  public $tipo_documento;
  public $condicion_venta = '01';
  public $plazo_credito;
  public $medio_pago;
  public $detalle;
  public $otros_cargos;
  public $status = 'PENDIENTE';
  public $mensajeConfirmacion;

  // ESTAS SON LAS PROPIEDADES CORRECTAS PARA MANEJAR SUBIDAS:
  public $xmlFile;               // Para el XML principal
  public $xml_respuestaFile;      // Para el XML de respuesta
  public $xml_confirmacionFile;   // Para el XML de confirmación
  public $pdfFile;                // Para el PDF

  public $currentXmlPath;
  public $currentXmlRespuestaPath;
  public $currentXmlConfirmacionPath; // Para mostrar en edición
  public $currentPdfPath;

  public $clave_referencia;
  public $codigo_actividad;
  public $situacion_comprobante = '1';

  public $closeForm = false;
  public $respuesta_hacienda;

  // Listas para selects
  public $listTiposDocumento;
  public $listEstadosHacienda;
  public $listTiposIdentificacion;
  public $listCondicionesVenta;
  public $listMediosPago;
  public $listSituacionesComprobante;
  public $listaMensajeconfirmacion;
  public $currencies;

  public $columns;
  public $defaultColumns;

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
  ];

  private $tipoDocumentoMap = [
    '01' => 'Factura Electrónica',
    '02' => 'Nota de Débito',
    '03' => 'Nota de Crédito',
    '04' => 'Tiquete Electrónico',
    '05' => 'Confirmación de aceptación del comprobante electrónico',
    '06' => 'Confirmación de aceptación parcial del comprobante electrónico',
    '07' => 'Confirmación de rechazo del comprobante electrónico',
    '08' => 'Factura de Compra',
    '09' => 'Confirmación de aceptación',
    '10' => 'Recibo Electrónico de Pago'
  ];

  public function mount()
  {
    $this->currencies = Currency::orderBy('code', 'ASC')->get();

    // Inicializar listas para formularios
    $this->listTiposDocumento = [
      ['id' => '01', 'name' => 'Factura Electrónica'],
      ['id' => '02', 'name' => 'Nota de Débito'],
      ['id' => '03', 'name' => 'Nota de Crédito'],
      ['id' => '04', 'name' => 'Tiquete Electrónico'],
      ['id' => '05', 'name' => 'Confirmación de aceptación del comprobante electrónico'],
      ['id' => '06', 'name' => 'Confirmación de aceptación parcial del comprobante electrónico'],
      ['id' => '07', 'name' => 'Confirmación de rechazo del comprobante electrónico'],
      ['id' => '08', 'name' => 'Factura de Compra'],
      ['id' => '09', 'name' => 'Confirmación de aceptación'],
      ['id' => '10', 'name' => 'Recibo Electrónico de Pago']
    ];

    $this->listEstadosHacienda = [
      ['id' => 'PENDIENTE', 'name' => 'PENDIENTE'],
      ['id' => 'RECIBIDA', 'name' => 'RECIBIDA'],
      ['id' => 'ACEPTADA', 'name' => 'ACEPTADA'],
      ['id' => 'RECHAZADA', 'name' => 'RECHAZADA'],
    ];

    $this->listTiposIdentificacion = [
      ['id' => '01', 'name' => 'Cédula Física'],
      ['id' => '02', 'name' => 'Cédula Jurídica'],
      ['id' => '03', 'name' => 'DIMEX'],
      ['id' => '04', 'name' => 'NITE'],
      ['id' => '05', 'name' => 'Extranjero No Domiciliado'],
      ['id' => '06', 'name' => 'No Contribuyente'],
    ];

    $this->listCondicionesVenta = [
      ['id' => '01', 'name' => 'Contado'],
      ['id' => '02', 'name' => 'Crédito'],
      ['id' => '03', 'name' => 'Consignación'],
      ['id' => '04', 'name' => 'Apartado'],
      ['id' => '05', 'name' => 'Arrendamiento con opción de compra'],
      ['id' => '06', 'name' => 'Arrendamiento en función financiera'],
      ['id' => '07', 'name' => 'Cobro a favor de un tercero'],
      ['id' => '08', 'name' => 'Servicios prestados al Estado'],
      ['id' => '09', 'name' => 'Pago de servicios prestado al Estado'],
      ['id' => '10', 'name' => 'Venta a crédito en IVA hasta 90 días (Artículo 27, LIVA)'],
      ['id' => '11', 'name' => 'Pago de venta a crédito en IVA hasta 90 días (Artículo 27,LIVA)'],
      ['id' => '12', 'name' => 'Venta Mercancía No Nacionalizada'],
      ['id' => '13', 'name' => 'Venta Bienes Usados No Contribuyente'],
      ['id' => '14', 'name' => 'Arrendamiento Operativo'],
      ['id' => '15', 'name' => 'Arrendamiento Financiero'],
      ['id' => '99', 'name' => 'Otros'],
    ];

    $this->listMediosPago = [
      ['id' => '01', 'name' => 'Efectivo'],
      ['id' => '02', 'name' => 'Tarjeta'],
      ['id' => '03', 'name' => 'Cheque'],
      ['id' => '04', 'name' => 'Transferencia'],
      ['id' => '05', 'name' => 'Recaudado por terceros'],
      ['id' => '06', 'name' => 'SINPE MOVIL'],
      ['id' => '07', 'name' => 'Plataforma Digital'],
      ['id' => '99', 'name' => 'Otros'],
    ];

    $this->listSituacionesComprobante = [
      ['id' => '1', 'name' => 'Normal'],
      ['id' => '2', 'name' => 'Contingencia'],
      ['id' => '3', 'name' => 'Sin Internet'],
    ];

    $this->listaMensajeconfirmacion = [
      ['id' => 'ACEPTADO', 'name' => 'ACEPTADO'],
      ['id' => 'ACEPTADOPARCIAL', 'name' => 'ACEPTADOPARCIAL'],
      ['id' => 'RECHAZADO', 'name' => 'RECHAZADO'],
    ];

    $this->refresDatatable();
  }

  public function render()
  {
    $records = Comprobante::search($this->search, $this->filters)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage)
      ->through(function ($item) {
        // Añadir propiedades computadas para la vista
        $item->tipo_documento_description = $item->tipo_documento_description;
        $item->estado_hacienda_description = $item->estado_hacienda_description;
        $item->fecha_emision_formatted = $item->fecha_emision;
        return $item;
      });

    return view('livewire.comprobantes.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetErrorBag();
    $this->resetValidation();
    $this->resetControls();
    $this->action = 'create';
    $this->mensajeConfirmacion = 'ACEPTADO';
    $this->dispatch('scroll-to-top');
  }

  // Reglas de validación CORREGIDAS
  protected function rules()
  {
    return [
      'location_id' => 'required|exists:business_locations,id',

      'xmlFile' => 'required|file|mimes:xml|max:10240',
      'xml_respuestaFile' => 'nullable|file|mimes:xml|max:10240',
      'xml_confirmacionFile' => 'nullable|file|mimes:xml|max:10240', // Agregar esta línea
      'pdfFile' => 'nullable|file|mimes:pdf|max:20480',

      // Hacer estos campos nullable ya que pueden faltar en algunos XML
      'receptor_nombre' => 'nullable|string|max:200',
      'receptor_tipo_identificacion' => 'nullable|string|in:01,02,03,04,05,06',
      'receptor_numero_identificacion' => 'nullable|string|max:20',
      'clave_referencia' => 'nullable|string|max:50',
      'medio_pago' => 'nullable|string|in:01,02,03,04,05,06,07,99',
      'plazo_credito' => 'nullable|integer|min:0',
      'total_otros_cargos' => 'nullable|numeric|min:0',
      'detalle' => 'nullable|array',
      'otros_cargos' => 'nullable|array',
      'respuesta_hacienda' => 'nullable|string',

      'mensajeConfirmacion' => 'required|in:ACEPTADO,ACEPTADOPARCIAL,RECHAZADO',
      'detalle' => 'required|string|max:80',

      // Mantener estos campos como requeridos
      'key' => 'required|string|max:50|unique:comprobantes,key',
      //'consecutivo' => 'required|string|max:20',
      'fecha_emision' => 'required|date',
      'emisor_nombre' => 'required|string|max:200',
      'emisor_tipo_identificacion' => 'required|string|in:01,02,03,04,05,06',
      'emisor_numero_identificacion' => 'required|string|max:12',
      'total_comprobante' => 'required|numeric|min:0',
      'tipo_cambio' => 'required|numeric|min:0',
      'moneda' => 'required|string|size:3',
      'tipo_documento' => 'required|string|in:01,02,03,04,05,06,07,08,09,10',
      'condicion_venta' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,99',
      'status' => 'required|in:PENDIENTE,RECIBIDA,ACEPTADA,RECHAZADA',
      'codigo_actividad' => 'required|string|max:6',
      'situacion_comprobante' => 'required|string|in:1,2,3',
    ];
  }

  // Método updatedXmlFile CORREGIDO
  public function updatedXmlFile()
  {
    $this->validateOnly('xmlFile', [
      'xmlFile' => 'required|file|mimes:xml|max:10240'
    ]);

    try {
      // Leer contenido del XML
      $xmlContent = file_get_contents($this->xmlFile->getRealPath());
      $xml = new SimpleXMLElement($xmlContent);

      // Obtener todos los namespaces del documento
      $namespaces = $xml->getNamespaces(true);
      $defaultNamespace = $namespaces[''] ?? null;

      // Registrar namespace por defecto si existe
      if ($defaultNamespace) {
        $xml->registerXPathNamespace('d', $defaultNamespace);
      }

      // Función mejorada para buscar elementos
      $findElement = function ($path) use ($xml, $defaultNamespace) {
        // Intento sin namespace
        $result = $xml->xpath($path);
        if (!empty($result)) return $result[0];

        // Intento con namespace
        if ($defaultNamespace) {
          $nsPath = str_replace('/', '/d:', $path);
          $result = $xml->xpath("//d:$path");
          if (!empty($result)) return $result[0];
        }

        // Búsqueda global en todo el documento
        $globalPath = "//*[local-name() = '{$path}']";
        $result = $xml->xpath($globalPath);
        return !empty($result) ? $result[0] : null;
      };

      // Extraer tipo de documento - Método mejorado
      $this->tipo_documento = $this->determinarTipoDocumento($xml, $findElement);

      // Extraer datos básicos del comprobante
      $this->key = (string)($findElement('Clave') ?? '');
      $this->fecha_emision = date('Y-m-d\TH:i', strtotime((string)($findElement('FechaEmision') ?? now())));
      $this->codigo_actividad = (string)($findElement('CodigoActividadEmisor') ?? '');
      $this->situacion_comprobante = (string)($findElement('SituacionComprobante') ?? '1');

      // Extraer datos del emisor
      $emisor = $findElement('Emisor');
      if ($emisor) {
        $this->emisor_nombre = (string)($emisor->Nombre ?? '');
        $this->emisor_tipo_identificacion = (string)($emisor->Identificacion->Tipo ?? '');
        $this->emisor_numero_identificacion = (string)($emisor->Identificacion->Numero ?? '');
      }

      // Extraer datos del receptor
      $receptor = $findElement('Receptor');
      if ($receptor) {
        $this->receptor_nombre = (string)($receptor->Nombre ?? '');
        $this->receptor_tipo_identificacion = (string)($receptor->Identificacion->Tipo ?? '');
        $this->receptor_numero_identificacion = (string)($receptor->Identificacion->Numero ?? '');
      }

      // Extraer datos financieros
      $resumen = $findElement('ResumenFactura') ?? $findElement('ResumenFactura');
      if ($resumen) {
        $this->tipo_cambio = (float)($resumen->CodigoTipoMoneda->TipoCambio ?? 1);
        $this->total_comprobante = (float)($resumen->TotalComprobante ?? 0);
        $this->total_impuestos = (float)($resumen->TotalImpuesto ?? 0);
        $this->total_gravado = (float)($resumen->TotalGravado ?? 0);
        $this->total_exento = (float)($resumen->TotalExento ?? 0);
        $this->total_descuentos = (float)($resumen->TotalDescuentos ?? 0);
        $this->moneda = (string)($resumen->CodigoTipoMoneda->CodigoMoneda ?? 'CRC');
      }

      // Extraer otros datos
      $this->condicion_venta = (string)($findElement('CondicionVenta') ?? '01');
      $this->plazo_credito = (int)($findElement('PlazoCredito') ?? 0);
      $this->medio_pago = (string)($findElement('MedioPago') ?? null);
      $this->clave_referencia = (string)($findElement('NumeroDocumento') ?? ''); // Para NC/ND

      // Valores por defecto para campos faltantes
      $this->status = 'PENDIENTE';
      $this->total_otros_cargos = 0;

      // 1. Validar tipo de comprobante
      $tiposValidos = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
      if (!in_array($this->tipo_documento, $tiposValidos)) {
        throw new \Exception("Tipo de comprobante no válido: {$this->tipo_documento}");
      }

      // 2. Validar identificación del emisor/receptor
      $identificacion = ($this->tipo_documento == '08')
        ? $this->emisor_numero_identificacion
        : $this->receptor_numero_identificacion;

      $location = BusinessLocation::where('identification', trim($identificacion))->first();
      if (!$location) {
        throw new \Exception("El emisor del comprobante con Identificación: $identificacion " . " no pertenece a la empresa");
      }

      $this->location_id = $location->id;

      // 3. Validar campos requeridos
      $camposRequeridos = [
        'key' => $this->key,
        'emisor_numero_identificacion' => $this->emisor_numero_identificacion,
        'total_comprobante' => $this->total_comprobante
      ];

      foreach ($camposRequeridos as $campo => $valor) {
        if (empty($valor)) {
          throw new \Exception("Campo requerido faltante en XML: $campo");
        }
      }

      $this->dispatch('show-notification', [
        'type' => 'warning',
        'message' => 'XML procesado correctamente. Tipo: ' . $this->tipoDocumentoMap[$this->tipo_documento]
      ]);
    } catch (\Exception $e) {
      Log::error('Error procesando XML: ' . $e->getMessage());
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'Error al procesar XML: ' . $e->getMessage()
      ]);
      $this->reset('xmlFile');
    }
  }

  // Función para determinar el tipo de documento
  private function determinarTipoDocumento($xml, $findElement)
  {
    // Primero intentar obtener directamente el campo TipoDocumento
    $tipo = (string)($findElement('TipoDocumento') ?? '');

    // Si no se encuentra, inferir por el nodo raíz
    if (empty($tipo)) {
      $rootNodeName = $xml->getName();

      $map = [
        'FacturaElectronica' => '01',
        'NotaDebitoElectronica' => '02',
        'NotaCreditoElectronica' => '03',
        'TiqueteElectronico' => '04',
        'MensajeReceptor' => '05',
        'MensajeReceptor' => '06',
        'MensajeReceptor' => '07',
        'FacturaElectronicaCompra' => '08',
        'ConfirmacionAceptacion' => '09',
        'ReciboElectronicoPago' => '10'
      ];

      $tipo = $map[$rootNodeName] ?? '00'; // 00 = Desconocido
    }

    // Validar que el tipo sea válido
    $tiposValidos = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
    if (!in_array($tipo, $tiposValidos)) {
      throw new \Exception("Tipo de documento no válido: $tipo");
    }

    return $tipo;
  }

  public function store()
  {
    //dd($this);
    $this->validate();
    try {
      // Obtener el emisor del comprobante
      // 2. Validar identificación del emisor/receptor
      $identificacionEmisor = ($this->tipo_documento == '08')
        ? $this->emisor_numero_identificacion
        : $this->receptor_numero_identificacion;

      $location = BusinessLocation::where('identification', trim($identificacionEmisor))->firstOrFail();

      // Obtener el año y mes de la fecha de la transacción
      $invoiceDate = \Carbon\Carbon::parse($this->fecha_emision);
      $year = $invoiceDate->format('Y');
      $month = $invoiceDate->format('m');

      // Determinar si es un comprobante de confirmación (tipos 05,06,07,09)
      $isConfirmacion = in_array($this->tipo_documento, ['05', '06', '07', '09']);

      // Generar nombre base para los archivos
      $nombreBase = $isConfirmacion
        ? $this->key . '-' . $this->consecutivo
        : $this->key;

      // Crear la ruta base de almacenamiento
      $basePath = "comprobantes/{$location->id}/{$year}/{$month}";

      // 1. Subir XML principal
      $xmlPath = $this->xmlFile->storeAs(
        $basePath,
        $nombreBase . '.xml',
        'public'
      );

      // 2. Subir XML de respuesta (si existe)
      $xmlRespuestaPath = null;
      if ($this->xml_respuestaFile) {
        // Para comprobantes de confirmación, el XML de respuesta no lleva sufijo
        $sufijoRespuesta = $isConfirmacion ? '' : '_respuesta';
        $xmlRespuestaPath = $this->xml_respuestaFile->storeAs(
          $basePath,
          $nombreBase . $sufijoRespuesta . '.xml',
          'public'
        );
      }

      // 3. Subir XML de confirmación (si existe)
      $xmlConfirmacionPath = null;
      if ($this->xml_confirmacionFile) {
        // SOLO para comprobantes normales (no de confirmación)
        $sufijoConfirmacion = $isConfirmacion ? '' : '_confirmacion';
        $xmlConfirmacionPath = $this->xml_confirmacionFile->storeAs(
          $basePath,
          $nombreBase . $sufijoConfirmacion . '.xml',
          'public'
        );
      }

      // 4. Subir PDF (si existe)
      $pdfPath = null;
      if ($this->pdfFile) {
        $pdfPath = $this->pdfFile->storeAs(
          $basePath,
          $nombreBase . '.pdf',
          'public'
        );
      }

      // Crear comprobante
      $comprobante = Comprobante::create([
        'location_id' => $this->location_id,
        'key' => $this->key,
        'consecutivo' => $this->consecutivo,
        'fecha_emision' => $this->fecha_emision,
        'emisor_nombre' => $this->emisor_nombre,
        'emisor_tipo_identificacion' => $this->emisor_tipo_identificacion,
        'emisor_numero_identificacion' => $this->emisor_numero_identificacion,
        'receptor_nombre' => $this->receptor_nombre,
        'receptor_tipo_identificacion' => $this->receptor_tipo_identificacion,
        'receptor_numero_identificacion' => $this->receptor_numero_identificacion,
        'tipo_cambio' => $this->tipo_cambio,
        'total_impuestos' => $this->total_impuestos,
        'total_exento' => $this->total_exento,
        'total_gravado' => $this->total_gravado,
        'total_descuentos' => $this->total_descuentos,
        'total_otros_cargos' => $this->total_otros_cargos,
        'total_comprobante' => $this->total_comprobante,
        'moneda' => $this->moneda,
        'tipo_documento' => $this->tipo_documento,
        'condicion_venta' => $this->condicion_venta,
        'plazo_credito' => $this->plazo_credito,
        'medio_pago' => $this->medio_pago,
        'detalle' => $this->detalle,
        'status' => $this->status,
        'respuesta_hacienda' => $this->respuesta_hacienda,
        'mensajeConfirmacion' => $this->mensajeConfirmacion,

        'xml_path' => $xmlPath,
        'xml_respuesta_path' => $xmlRespuestaPath,
        'xml_respuesta_confirmacion_path' => $xmlConfirmacionPath, // Nuevo campo
        'pdf_path' => $pdfPath,

        'clave_referencia' => $this->clave_referencia,
        'codigo_actividad' => $this->codigo_actividad,
        'situacion_comprobante' => $this->situacion_comprobante,
        'business_location_id' => $location->id, // Guardar referencia a la ubicación
      ]);

      // Resetear controles y mostrar notificación
      $this->resetControls();
      $this->action = 'list';

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('Comprobante creado correctamente')
      ]);
    } catch (\Exception $e) {
      Log::error('Error al crear comprobante: ' . $e->getMessage());
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Error al crear comprobante: ') . $e->getMessage()
      ]);
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $comprobante = Comprobante::findOrFail($recordId);
    $this->recordId = $recordId;

    // Almacenar rutas actuales para mostrar en vista
    $this->currentXmlPath = $comprobante->xml_path;
    $this->currentXmlRespuestaPath = $comprobante->xml_respuesta_path;
    $this->currentXmlConfirmacionPath = $comprobante->xml_respuesta_confirmacion_path;
    $this->currentPdfPath = $comprobante->pdf_path;

    // Asignar todos los campos del modelo
    $this->key = $comprobante->key;
    $this->consecutivo = $comprobante->consecutivo;
    $this->fecha_emision = $comprobante->fecha_emision->format('Y-m-d\TH:i');
    $this->emisor_nombre = $comprobante->emisor_nombre;
    $this->emisor_tipo_identificacion = $comprobante->emisor_tipo_identificacion;
    $this->emisor_numero_identificacion = $comprobante->emisor_numero_identificacion;
    $this->receptor_nombre = $comprobante->receptor_nombre;
    $this->receptor_tipo_identificacion = $comprobante->receptor_tipo_identificacion;
    $this->receptor_numero_identificacion = $comprobante->receptor_numero_identificacion;
    $this->tipo_cambio = $comprobante->tipo_cambio;
    $this->total_impuestos = $comprobante->total_impuestos;
    $this->total_exento = $comprobante->total_exento;
    $this->total_gravado = $comprobante->total_gravado;
    $this->total_descuentos = $comprobante->total_descuentos;
    $this->total_otros_cargos = $comprobante->total_otros_cargos;
    $this->total_comprobante = $comprobante->total_comprobante;
    $this->moneda = $comprobante->moneda;
    $this->tipo_documento = $comprobante->tipo_documento;
    $this->condicion_venta = $comprobante->condicion_venta;
    $this->plazo_credito = $comprobante->plazo_credito;
    $this->medio_pago = $comprobante->medio_pago;
    $this->detalle = $comprobante->detalle;
    $this->status = $comprobante->status;
    $this->respuesta_hacienda = $comprobante->respuesta_hacienda;
    $this->clave_referencia = $comprobante->clave_referencia;
    $this->codigo_actividad = $comprobante->codigo_actividad;
    $this->situacion_comprobante = $comprobante->situacion_comprobante;
    $this->mensajeConfirmacion = $comprobante->mensajeConfirmacion;

    // dd($this->mensajeConfirmacion);

    $this->resetErrorBag();
    $this->resetValidation();
    $this->action = 'edit';
  }

  public function update()
  {
    $this->validate([
      // Excluir key actual para validación unique
      'key' => 'required|string|max:50|unique:comprobantes,key,' . $this->recordId,
      // Resto de validaciones igual que en store
    ]);

    try {
      $comprobante = Comprobante::findOrFail($this->recordId);

      // Obtener el emisor del comprobante
      // 2. Validar identificación del emisor/receptor
      $identificacionEmisor = ($comprobante->tipo_documento == '08')
        ? $comprobante->emisor_numero_identificacion
        : $comprobante->receptor_numero_identificacion;

      $location = BusinessLocation::where('identification', trim($identificacionEmisor))->firstOrFail();

      // Obtener ruta base para almacenamiento
      $invoiceDate = \Carbon\Carbon::parse($comprobante->fecha_emision);

      // Determinar si es un comprobante de confirmación
      $isConfirmacion = in_array($comprobante->tipo_documento, ['05', '06', '07', '09']);

      // Generar nombre base para los archivos
      $nombreBase = $isConfirmacion
        ? $comprobante->key . '-' . $comprobante->consecutivo
        : $comprobante->key;

      $basePath = "comprobantes/{$location->id}/{$invoiceDate->format('Y')}/{$invoiceDate->format('m')}";

      // Manejar XML de respuesta
      if ($this->xml_respuestaFile) {
        // Eliminar anterior si existe
        if ($comprobante->xml_respuesta_path) {
          Storage::disk('public')->delete($comprobante->xml_respuesta_path);
        }

        $sufijoRespuesta = $isConfirmacion ? '' : '_respuesta';
        $xmlRespuestaPath = $this->xml_respuestaFile->storeAs(
          $basePath,
          $nombreBase . $sufijoRespuesta . '.xml',
          'public'
        );
        $comprobante->xml_respuesta_path = $xmlRespuestaPath;
      }

      // Manejar XML de confirmación
      if ($this->xml_confirmacionFile) {
        // ... (eliminar archivo anterior) ...
        if ($comprobante->xml_confirmacionFile) {
          Storage::disk('public')->delete($comprobante->xml_confirmacionFile);
        }

        $sufijoConfirmacion = $isConfirmacion ? '' : '_confirmacion';
        $xmlConfirmacionPath = $this->xml_confirmacionFile->storeAs(
          $basePath,
          $nombreBase . $sufijoConfirmacion . '.xml',
          'public'
        );
        $comprobante->xml_respuesta_confirmacion_path = $xmlConfirmacionPath;
      }

      // Manejar PDF
      if ($this->pdfFile) {
        // ... (eliminar archivo anterior) ...
        if ($comprobante->pdf_path) {
          Storage::disk('public')->delete($comprobante->pdf_path);
        }

        $pdfPath = $this->pdfFile->storeAs(
          $basePath,
          $nombreBase . '.pdf',
          'public'
        );
        $comprobante->pdf_path = $pdfPath;
      }

      // Actualizar campos
      $comprobante->update([
        'key' => $this->key,
        'consecutivo' => $this->consecutivo,
        'fecha_emision' => $this->fecha_emision,
        'emisor_nombre' => $this->emisor_nombre,
        'emisor_tipo_identificacion' => $this->emisor_tipo_identificacion,
        'emisor_numero_identificacion' => $this->emisor_numero_identificacion,
        'receptor_nombre' => $this->receptor_nombre,
        'receptor_tipo_identificacion' => $this->receptor_tipo_identificacion,
        'receptor_numero_identificacion' => $this->receptor_numero_identificacion,
        'total_comprobante' => $this->total_comprobante,
        'tipo_cambio' => $this->tipo_cambio,
        'total_impuestos' => $this->total_impuestos,
        'total_exento' => $this->total_exento,
        'total_gravado' => $this->total_gravado,
        'total_descuentos' => $this->total_descuentos,
        'total_otros_cargos' => $this->total_otros_cargos,
        'moneda' => $this->moneda,
        'tipo_documento' => $this->tipo_documento,
        'condicion_venta' => $this->condicion_venta,
        'plazo_credito' => $this->plazo_credito,
        'medio_pago' => $this->medio_pago,
        'detalle' => $this->detalle,
        'status' => $this->status,
        'respuesta_hacienda' => $this->respuesta_hacienda,
        'clave_referencia' => $this->clave_referencia,
        'codigo_actividad' => $this->codigo_actividad,
        'situacion_comprobante' => $this->situacion_comprobante,
        'mensajeConfirmacion' => $this->mensajeConfirmacion
      ]);

      $this->resetControls();
      $this->action = 'list';
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('Comprobante actualizado correctamente')]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Error al actualizar comprobante: ') . $e->getMessage()]);
    }
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $comprobante = Comprobante::findOrFail($recordId);

      // Crear lista de archivos a eliminar con verificación de existencia
      $filesToDelete = [];
      $disk = Storage::disk('public'); // Usa el disco correcto

      $paths = [
        'xml' => $comprobante->xml_path,
        'xml_respuesta' => $comprobante->xml_respuesta_path,
        'pdf' => $comprobante->pdf_path
      ];

      foreach ($paths as $type => $path) {
        if (!empty($path) && $disk->exists($path)) {
          $filesToDelete[] = $path;
        }
      }

      // Eliminar archivos
      if (!empty($filesToDelete)) {
        $disk->delete($filesToDelete);
      }

      // Eliminar el registro
      $comprobante->delete();

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('Comprobante eliminado correctamente')
      ]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Error al eliminar comprobante: ') . $e->getMessage()
      ]);
    }
  }

  public function downloadXml($id)
  {
    $comprobante = Comprobante::findOrFail($id);
    $disk = Storage::disk('public');

    if (!$disk->exists($comprobante->xml_path)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'El archivo XML no existe en el almacenamiento.'
      ]);
      return;
    }

    return $disk->download($comprobante->xml_path);
  }

  public function downloadXmlRespuesta($id)
  {
    $comprobante = Comprobante::findOrFail($id);
    $disk = Storage::disk('public');

    if (!$disk->exists($comprobante->xml_respuesta_path)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'El archivo XML de respuesta no existe en el almacenamiento.'
      ]);
      return;
    }

    return $disk->download($comprobante->xml_respuesta_path);
  }

  public function downloadXmlRespuestaConfirmacion($id)
  {
    $comprobante = Comprobante::findOrFail($id);
    $disk = Storage::disk('public');

    if (!$disk->exists($comprobante->xml_respuesta_confirmacion_path)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'El archivo XML de respuesta de hacienda no existe en el almacenamiento.'
      ]);
      return;
    }

    return $disk->download($comprobante->xml_respuesta_confirmacion_path);
  }

  public function downloadXmlConfirmacion($id)
  {
    try {
      // Buscar la transacción por su ID
      $comprobante = Comprobante::findOrFail($id);

      // Llamar al helper para generar el XML
      $encode = false;
      return Helpers::generateMensajeElectronicoXML($comprobante, $encode, 'browser');
    } catch (\Exception $e) {
      // Si ocurre un error, se captura la excepción y se muestra una notificación
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while downloading the XML:') . ' ' . $e->getMessage()
      ]);

      // Registrar el error en los logs para facilitar el diagnóstico
      logger()->error('Error while downloading XML: ' . $e->getMessage(), ['exception' => $e]);
    }
  }

  public function downloadPdf($id)
  {
    $comprobante = Comprobante::findOrFail($id);
    $disk = Storage::disk('public');

    if (!$disk->exists($comprobante->pdf_path)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'El archivo Pdf del comprobante no existe en el almacenamiento.'
      ]);
      return;
    }

    // Agregar esto para descargar el archivo:
    return $disk->download($comprobante->pdf_path);
  }

  public function resetControls()
  {
    $this->reset([
      'key',
      'consecutivo',
      'fecha_emision',
      'emisor_nombre',
      'emisor_tipo_identificacion',
      'emisor_numero_identificacion',
      'receptor_nombre',
      'receptor_tipo_identificacion',
      'receptor_numero_identificacion',
      'tipo_cambio',
      'total_comprobante',
      'total_impuestos',
      'total_exento',
      'total_gravado',
      'total_descuentos',
      'moneda',
      'tipo_documento',
      'condicion_venta',
      'plazo_credito',
      'medio_pago',
      'detalle',
      'otros_cargos',
      'status',
      'respuesta_hacienda',
      'xmlFile',
      'xml_respuestaFile',
      'pdfFile',
      'clave_referencia',
      'codigo_actividad',
      'situacion_comprobante',
      'mensajeConfirmacion'
    ]);

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public $filters = [
    'filter_clave' => null,
    'filter_consecutivo' => null,
    'filter_tipo_documento' => null,
    'filter_emisor' => null,
    'filter_receptor' => null,
    'filter_fecha_emision' => null,
    'filter_moneda' => null,
    'filter_total_impuesto' => null,
    'filter_total_descuento' => null,
    'filter_total' => null,
    'filter_estado_hacienda' => null,
  ];

  public function getDefaultColumns()
  {
    return [
      [
        'field' => 'key',
        'orderName' => 'key',
        'label' => __('Clave'),
        'filter' => 'filter_clave',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'font-mono text-xs',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 'w-1/12',
        'visible' => true,
      ],
      [
        'field' => 'consecutivo',
        'orderName' => 'consecutivo',
        'label' => __('Consecutivo'),
        'filter' => 'filter_consecutivo',
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
        'width' => 'w-1/12',
        'visible' => true,
      ],
      [
        'field' => 'tipo_documento_description',
        'orderName' => 'tipo_documento',
        'label' => __('Tipo'),
        'filter' => 'filter_tipo_documento',
        'filter_type' => 'select',
        'filter_sources' => 'listTiposDocumento',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 'w-1/12',
        'visible' => true,
      ],
      [
        'field' => 'emisor_nombre',
        'orderName' => 'emisor_nombre',
        'label' => __('Emisor'),
        'filter' => 'filter_emisor',
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
        'width' => 'w-2/12',
        'visible' => true,
      ],
      [
        'field' => 'receptor_nombre',
        'orderName' => 'receptor_nombre',
        'label' => __('Receptor'),
        'filter' => 'filter_receptor',
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
        'width' => 'w-2/12',
        'visible' => true,
      ],
      [
        'field' => 'fecha_emision_formatted',
        'orderName' => 'fecha_emision',
        'label' => __('Emmision Date'),
        'filter' => 'filter_fecha_emision',
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
        'field' => 'moneda',
        'orderName' => 'moneda',
        'label' => __('Currency'),
        'filter' => 'filter_moneda',
        'filter_type' => 'select',
        'filter_sources' => 'currencies',
        'filter_source_field' => 'code',
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
        'field' => 'total_impuestos',
        'orderName' => 'total_impuestos',
        'label' => __('Total impuesto'),
        'filter' => 'filter_total_impuesto',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'font-medium',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tImpuesto',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 'w-1/12',
        'visible' => true,
      ],
      [
        'field' => 'total_descuentos',
        'orderName' => 'total_descuentos',
        'label' => __('Total descuentos'),
        'filter' => 'filter_total_descuento',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'font-medium',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tDescuento',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 'w-1/12',
        'visible' => true,
      ],
      [
        'field' => 'total_comprobante',
        'orderName' => 'total_comprobante',
        'label' => __('Total'),
        'filter' => 'filter_total',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'font-medium',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tComprobante',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 'w-1/12',
        'visible' => true,
      ],
      [
        'field' => 'estado_hacienda_description',
        'orderName' => 'status',
        'label' => __('Estado'),
        'filter' => 'filter_estado_hacienda',
        'filter_type' => 'select',
        'filter_sources' => 'listEstadosHacienda',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => 'getHtmlStatus',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 'w-1/12',
        'visible' => true,
      ],
      [
        'field' => 'action',
        'orderName' => '',
        'label' => __('Acciones'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'action',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => 'getHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => 'w-1/12',
        'visible' => true,
      ],
    ];
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'comprobantes-datatable')
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

  public function debugXmlNamespaces()
  {
    try {
      $xmlContent = file_get_contents($this->xmlFile->getRealPath());
      $xml = new SimpleXMLElement($xmlContent);

      $namespaces = $xml->getNamespaces(true);
      $xmlStructure = [
        'key' => (string)($xml->Clave ?? 'No encontrado'),
        'emisor' => isset($xml->Emisor) ? 'Existe' : 'No existe',
        'namespaces' => $namespaces,
        'xml' => $xml->asXML() // Solo para desarrollo, no usar en producción
      ];

      $this->dispatch('show-notification', [
        'type' => 'info',
        'message' => 'Estructura XML: ' . json_encode($xmlStructure)
      ]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'Error: ' . $e->getMessage()
      ]);
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

  public function getRecordAction($recordId)
  {
    if (!isset($recordId) || is_null($recordId)) {
      if (empty($this->selectedIds)) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => 'Debe seleccionar un registro.'
        ]);
        return;
      }

      if (count($this->selectedIds) > 1) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => 'Solo se permite seleccionar un registro.'
        ]);
        return;
      }

      if (count($this->selectedIds) == 1) {
        $recordId = $this->selectedIds[0];
      }
    }

    return $recordId;
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

  public function updatedSelectedIds()
  {
    // Emite los IDs seleccionados cada vez que se actualice la selección
    $this->dispatch('updateSelectedIds', $this->selectedIds);
  }

  public function sendDocumentToHacienda($recordId)
  {
    try {
      $comprobante = Comprobante::findOrFail($recordId);
    } catch (Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "No se ha encontrado el documento",
      ]);
      //throw new \Exception("No se ha encontrado el documento" . ' ' . $e->getMessage());
    }

    // Obtener la secuencia que le corresponde según tipo de comprobante
    $secuencia = DocumentSequenceService::generateConsecutive(
      'MR',
      $comprobante->location_id
    );

    $this->consecutivo = $comprobante->getConsecutivo($secuencia);
    $comprobante->consecutivo = $this->consecutivo;
    $comprobante->save();

    // Obtener el xml firmado y en base64
    $encode = true;
    $xml = Helpers::generateMensajeElectronicoXML($comprobante, $encode, 'content');

    //Loguearme en hacienda para obtener el token
    $username = $comprobante->location->api_user_hacienda;
    $password = $comprobante->location->api_password;
    try {
      $authService = new AuthService();
      $token = $authService->getToken($username, $password);
    } catch (\Exception $e) {
      //throw new \Exception("An error occurred when trying to obtain the token in the hacienda api" . ' ' . $e->getMessage());
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "Ha ocurrido un error al intentar identificarse en la api de hacienda",
      ]);
    }
    dd($token);
    $tipoDocumento = $comprobante->getComprobanteCode();

    $api = new ApiHacienda();
    $result = $api->send($xml, $token, $comprobante, $comprobante->location, $tipoDocumento);
    if ($result['error'] == 0) {
      $comprobante->status = Comprobante::RECIBIDA;
      $comprobante->created_at = \Carbon\Carbon::now();
    } else {
      //throw new \Exception($result['mensaje']);
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => $result['mensaje'],
      ]);
    }

    // Guardar la transacción
    if (!$comprobante->save()) {
      //throw new \Exception(__('Un error ha ocurrido al enviar el comprobante a Hacienda'));
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'Un error ha ocurrido al guardar el comprobante',
      ]);
    } else {
      // Si todo fue exitoso, mostrar notificación de éxito
      $this->dispatch('show-notification', [
        'type' => $result['type'],
        'message' => $result['mensaje'],
      ]);
    }
  }

  public function getStatusDocumentInHacienda($recordId)
  {
    try {
      // Intenta obtener la transacción
      $comprobante = Comprobante::findOrFail($recordId);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      // Manejo más específico del error cuando no se encuentra el registro
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "Comprobante not found in the database for ID: $recordId"
      ]);
      return;
    }

    // Loguearme en hacienda para obtener el token
    $username = $comprobante->location->api_user_hacienda;
    $password = $comprobante->location->api_password;
    try {
      $authService = new AuthService();
      $token = $authService->getToken($username, $password);
    } catch (\Exception $e) {
      // Si falla la obtención del token, notificar al usuario
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => "Error obtaining token: " . $e->getMessage()
      ]);
      return;
    }

    $tipoDocumento = $comprobante->getComprobanteCode();

    // Consulta el estado del comprobante
    $api = new ApiHacienda();

    Log::info('getStatusComprobante:', ['tipoDocumento' => $tipoDocumento]);

    $result = $api->getStatusComprobante($token, $comprobante, $comprobante->location, $tipoDocumento);

    Log::info('resultado de getStatusComprobante:', ['result' => $result]);

    if ($result['estado'] == 'aceptado') {
      //$sent = Helpers::sendComprobanteElectronicoEmail($recordId);
      /*
      if ($sent) {
        $transaction->fecha_envio_email = now();
        $transaction->save();

        $menssage = __('An email has been sent to the following addresses:') . ' ' . $transaction->contact->email;
        if (!empty($transaction->email_cc)) {
          $menssage .= ' ' . __('with copy to') . ' ' . $transaction->email_cc;
        }

        $this->dispatch('show-notification', [
          'type' => $result['type'],
          'message' => $result['mensaje'] . '<br> ' . $menssage
        ]);
      } else {
      */
      $this->dispatch('show-notification', [
        'type' => $result['type'],
        'message' => $result['mensaje']
      ]);
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
      //}
    } else {
      // Mostrar mensaje de error según el resultado de la API
      $this->dispatch('show-notification', [
        'type' => $result['type'],
        'message' => $result['mensaje']
      ]);

      if ($result['estado'] == 'rechazado')
        $sent = Helpers::sendNotificationMensajeElectronicoRejected($recordId);
    }
  }

  public function resetFilters()
  {
    $this->reset('filters');
    $this->selectedIds = [];
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
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
}
