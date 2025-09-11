<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Models\BusinessLocation;
use App\Services\Hacienda\ApiHacienda;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Comprobante extends Model
{
  const PENDIENTE = 'PENDIENTE';
  const RECIBIDA  = 'RECIBIDA';
  const ACEPTADA  = 'ACEPTADA';
  const RECHAZADA = 'RECHAZADA';

  const MENSAJEACEPTADO = 2;
  const MENSAJEACEPTADOPARCIAL = 3;
  const MENSAJERECHAZADO = 4;

  protected $fillable = [
    'location_id',
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
    'total_impuestos',
    'total_exento',
    'total_gravado',
    'total_descuentos',
    'total_otros_cargos',
    'total_comprobante',
    'moneda',
    'tipo_documento',
    'condicion_venta',
    'plazo_credito',
    'medio_pago',
    'detalle',
    'otros_cargos',
    'status',
    'respuesta_hacienda',

    'xml_path',
    'xml_respuesta_path',
    'xml_respuesta_confirmacion_path',
    'pdf_path',

    'clave_referencia',
    'codigo_actividad',
    'situacion_comprobante',
    'mensajeConfirmacion'
  ];

  protected $casts = [
    'fecha_emision' => 'datetime',
    'total_comprobante' => 'decimal:2',
    'total_impuestos' => 'decimal:2',
    'total_exento' => 'decimal:2',
    'total_gravado' => 'decimal:2',
    'total_descuentos' => 'decimal:2',
  ];

  public function location()
  {
    return $this->belongsTo(BusinessLocation::class);
  }

  /**
   * Accesor para obtener la URL pública del XML
   */
  public function getXmlUrlAttribute()
  {
    return Storage::url($this->xml_path);
  }

  /**
   * Accesor para obtener la URL pública del XML de respuesta
   */
  public function getXmlRespuestaUrlAttribute()
  {
    return $this->xml_respuesta_path ? Storage::url($this->xml_respuesta_path) : null;
  }

  /**
   * Accesor para obtener la URL pública del PDF
   */
  public function getPdfUrlAttribute()
  {
    return $this->pdf_path ? Storage::url($this->pdf_path) : null;
  }

  /**
   * Accesor para el tipo de documento
   */
  protected function tipoDocumentoDescription(): Attribute
  {
    return Attribute::make(
      get: function ($value, $attributes) {
        $tipos = [
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
        return $tipos[$attributes['tipo_documento']] ?? 'Desconocido';
      }
    );
  }

  /**
   * Accesor para el estado de Hacienda
   */
  protected function estadoHaciendaDescription(): Attribute
  {
    return Attribute::make(
      get: function ($value, $attributes) {
        $estados = [
          'PENDIENTE' => 'PENDIENTE',
          'RECIBIDA' => 'RECIBIDA',
          'ACEPTADA' => 'ACEPTADA',
          'RECHAZADA' => 'RECHAZADA'
        ];
        return $estados[$attributes['status']] ?? $attributes['status'];
      }
    );
  }

  /**
   * Accesor para el tipo de identificación
   */
  protected function emisorTipoIdentificacionDescription(): Attribute
  {
    return Attribute::make(
      get: function ($value, $attributes) {
        $tipos = [
          '01' => 'Cédula Física',
          '02' => 'Cédula Jurídica',
          '03' => 'DIMEX',
          '04' => 'NITE',
          '05' => 'Extranjero No Domiciliado',
          '06' => 'No Contribuyente'
        ];
        return $tipos[$attributes['emisor_tipo_identificacion']] ?? $attributes['emisor_tipo_identificacion'];
      }
    );
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Columnas a seleccionar (todas las de la tabla comprobantes)
    $columns = [
      'comprobantes.*',
      DB::raw('SUBSTRING(comprobantes.key, 21, 20) as consecutivo_factura')
    ];

    $query->select($columns)
      ->where(function ($q) use ($value) {
        $q->where('comprobantes.key', 'like', "%{$value}%")
          ->orWhere('comprobantes.consecutivo', 'like', "%{$value}%")
          ->orWhere('comprobantes.emisor_nombre', 'like', "%{$value}%")
          ->orWhere('comprobantes.emisor_numero_identificacion', 'like', "%{$value}%")
          ->orWhere('comprobantes.receptor_nombre', 'like', "%{$value}%")
          ->orWhere('comprobantes.receptor_numero_identificacion', 'like', "%{$value}%")
          ->orWhere('comprobantes.total_comprobante', 'like', "%{$value}%")
          ->orWhere('comprobantes.respuesta_hacienda', 'like', "%{$value}%");
      });

    // Aplicar filtros adicionales
    if (!empty($filters['filter_clave'])) {
      $query->where('comprobantes.key', 'like', '%' . $filters['filter_clave'] . '%');
    }

    if (!empty($filters['filter_consecutivo'])) {
      $query->where('comprobantes.consecutivo', 'like', '%' . $filters['filter_consecutivo'] . '%');
    }

    if (!empty($filters['filter_consecutivo_factura'])) {
      $consecutivo = $filters['filter_consecutivo_factura'];
      // Filtra usando la expresión SUBSTRING también
      $query->whereRaw('SUBSTRING(comprobantes.key, 21, 20) like ?', ["%{$consecutivo}%"]);
    }

    if (!empty($filters['filter_emisor'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('comprobantes.emisor_nombre', 'like', '%' . $filters['filter_emisor'] . '%')
          ->orWhere('comprobantes.emisor_numero_identificacion', 'like', '%' . $filters['filter_emisor'] . '%');
      });
    }

    if (!empty($filters['filter_receptor'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('comprobantes.receptor_nombre', 'like', '%' . $filters['filter_receptor'] . '%')
          ->orWhere('comprobantes.receptor_numero_identificacion', 'like', '%' . $filters['filter_receptor'] . '%');
      });
    }

    if (!empty($filters['filter_tipo_documento'])) {
      $query->where('comprobantes.tipo_documento', $filters['filter_tipo_documento']);
    }

    if (!empty($filters['filter_estado_hacienda'])) {
      $query->where('comprobantes.status', $filters['filter_estado_hacienda']);
    }

    if (!empty($filters['filter_total'])) {
      $query->where('comprobantes.total_comprobante', '=', $filters['filter_total']);
    }

    if (!empty($filters['total_impuestos'])) {
      $query->where('comprobantes.total_impuestos', '=', $filters['total_impuestos']);
    }

    if (!empty($filters['total_descuentos'])) {
      $query->where('comprobantes.total_descuentos', '=', $filters['total_descuentos']);
    }

    if (!empty($filters['filter_moneda'])) {
      $moneda = 'CRC';
      if ($filters['filter_moneda'] == 1)
        $moneda = 'USD';
      $query->where('comprobantes.moneda', '=', $moneda);
    }

    // Filtro por rango de fechas
    if (!empty($filters['filter_fecha_emision'])) {
      $range = explode(' to ', $filters['filter_fecha_emision']);

      if (count($range) === 2) {
        try {
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->startOfDay();
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->endOfDay();
          $query->whereBetween('comprobantes.fecha_emision', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar error de formato de fecha
        }
      } else {
        try {
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_emision'])->startOfDay();
          $query->whereDate('comprobantes.fecha_emision', $singleDate);
        } catch (\Exception $e) {
          // Manejar error de formato de fecha
        }
      }
    }

    return $query;
  }

  public function getHtmlStatus()
  {
    $htmlData = '';
    switch ($this->status) {
      case 'PENDIENTE':
        $htmlData = "<span class=\"badge bg-light text-dark\">" . __('PENDIENTE') . "</span>";
        break;
      case 'RECIBIDA':
        $htmlData = "<span class=\"badge bg-warning\">" . __('RECIBIDA') . "</span>";
        break;
      case 'ACEPTADA':
        $htmlData = "<span class=\"badge bg-success\">" . __('ACEPTADA') . "</span>";
        break;
      case 'RECHAZADA':
        $htmlData = "<span class=\"badge bg-danger\">" . __('RECHAZADA') . "</span>";
        break;
    }
    return $htmlData;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';
    $html = '<div class="d-flex align-items-center flex-nowrap">'; // 👈 evita saltos de línea

    // Iconos mejorados y con verificación de existencia
    $html = '';

    // PDF del comprobante
    if ($user->can('download-comprobantes') && $this->pdf_path && Storage::disk('public')->exists($this->pdf_path)) {
      $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2"
            title="Descargar PDF del comprobante"
            wire:click="downloadPdf({$this->id})"
            wire:loading.attr="disabled"
            wire:target="downloadPdf({$this->id})">
            <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadPdf({$this->id})"></i>
            <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadPdf({$this->id})"></i>
        </button>
    HTML;
    }

    // XML del comprobante
    if ($user->can('download-comprobantes') && $this->xml_path && Storage::disk('public')->exists($this->xml_path)) {
      $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2 text-success"
            title="Descargar XML del comprobante"
            wire:click="downloadXml({$this->id})"
            wire:loading.attr="disabled"
            wire:target="downloadXml({$this->id})">
            <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXml({$this->id})"></i>
            <i class="bx bxs-file {$iconSize}" wire:loading.remove wire:target="downloadXml({$this->id})"></i>
        </button>
    HTML;
    }

    // Respuesta de hacienda XML
    if ($user->can('download-comprobantes') && $this->xml_respuesta_path && Storage::disk('public')->exists($this->xml_respuesta_path)) {
      $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2 text-warning"
            title="Descargar XML respuesta de hacienda"
            wire:click="downloadXmlRespuesta({$this->id})"
            wire:loading.attr="disabled"
            wire:target="downloadXmlRespuesta({$this->id})">
            <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXmlRespuesta({$this->id})"></i>
            <i class="bx bxs-file {$iconSize}" wire:loading.remove wire:target="downloadXmlRespuesta({$this->id})"></i>
        </button>
    HTML;
    }

    // XML del confirmación  esto es temporal solo para ver el funcionamiento
    if ($user->can('download-comprobantes')) {
      $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2 text-dark"
            title="Descargar XML de confirmación del comprobante"
            wire:click="downloadXmlConfirmacion({$this->id})"
            wire:loading.attr="disabled"
            wire:target="downloadXmlConfirmacion({$this->id})">
            <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXmlConfirmacion({$this->id})"></i>
            <i class="bx bxs-file {$iconSize}" wire:loading.remove wire:target="downloadXmlConfirmacion({$this->id})"></i>
        </button>
    HTML;
    }

    // Enviar comprobante Hacienda
    if ($user->can('view-comprobantes') && in_array($this->status, [self::PENDIENTE])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-warning"
                title="Enviar comprobante a Hacienda"
                wire:click="sendDocumentToHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="sendDocumentToHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="sendDocumentToHacienda({$this->id})"></i>
                <i class="bx bx-send {$iconSize}" wire:loading.remove wire:target="sendDocumentToHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    // Estado en Hacienda
    if ($user->can('view-comprobantes') && in_array($this->status, [self::RECIBIDA, self::RECHAZADA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-info"
                title="Obtener estado en Hacienda"
                wire:click="getStatusDocumentInHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="getStatusDocumentInHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="getStatusDocumentInHacienda({$this->id})"></i>
                <i class="bx bx-share {$iconSize}" wire:loading.remove wire:target="getStatusDocumentInHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    if ($user->can('download-comprobantes') && $this->xml_respuesta_confirmacion_path && Storage::disk('public')->exists($this->xml_respuesta_confirmacion_path)) {
      $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2 text-danger"
            title="Descargar XML de confirmación del comprobante"
            wire:click="downloadXmlRespuestaConfirmacion({$this->id})"
            wire:loading.attr="disabled"
            wire:target="downloadXmlRespuestaConfirmacion({$this->id})">
            <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXmlRespuestaConfirmacion({$this->id})"></i>
            <i class="bx bxs-file {$iconSize}" wire:loading.remove wire:target="downloadXmlRespuestaConfirmacion({$this->id})"></i>
        </button>
    HTML;
    }

    $html .= '</div>';
    return $html;
  }

  /**
   * Genera el XML de confirmación de mensaje electrónico como string.
   *
   * @return string XML generado.
   */
  public function toXml(): string
  {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    $namespace = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/mensajeReceptor';
    $rootElementName = 'MensajeReceptor';
    $schema = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/mensajeReceptor https://tribunet.hacienda.go.cr/docs/esquemas/2025/v4.4/mensajeReceptor.xsd';

    // Crear el nodo raíz con el namespace correcto
    $root = $doc->createElementNS(
      $namespace,
      $rootElementName
    );
    $doc->appendChild($root);

    // Agregar atributos de namespace
    $root->setAttributeNS(
      'http://www.w3.org/2000/xmlns/',
      'xmlns:xsd',
      'http://www.w3.org/2001/XMLSchema'
    );
    $root->setAttributeNS(
      'http://www.w3.org/2000/xmlns/',
      'xmlns:xsi',
      'http://www.w3.org/2001/XMLSchema-instance'
    );
    $root->setAttributeNS(
      'http://www.w3.org/2001/XMLSchema-instance',
      'xsi:schemaLocation',
      $schema
    );

    // Agregar elementos usando el método directo
    if ($this->key) {
      $nodo = $doc->createElement('Clave', $this->key);
      $root->appendChild($nodo);
    }

    if ($this->emisor_numero_identificacion) {
      $nodo = $doc->createElement('NumeroCedulaEmisor', $this->emisor_numero_identificacion);
      $root->appendChild($nodo);
    }

    $fechaEmisionDoc = Carbon::now('America/Costa_Rica')->format('Y-m-d\TH:i:sP');
    $nodo = $doc->createElement('FechaEmisionDoc', $fechaEmisionDoc);
    $root->appendChild($nodo);

    $mensaje = $this->getMensajeConfirmacion();
    if ($mensaje) {
      $nodo = $doc->createElement('Mensaje', $mensaje);
      $root->appendChild($nodo);
    }

    $nodo = $doc->createElement('DetalleMensaje', $this->detalle);
    $root->appendChild($nodo);

    $nodo = $doc->createElement('MontoTotalImpuesto', $this->total_impuestos);
    $root->appendChild($nodo);

    if ($this->location) {
      $nodo = $doc->createElement('CodigoActividad', $this->location->code);
      $root->appendChild($nodo);
    }

    /*
    $nodo = $doc->createElement('CondicionImpuesto', '01');
    $root->appendChild($nodo);

    $nodo = $doc->createElement('MontoTotalImpuestoAcreditar', $this->total_impuestos);
    $root->appendChild($nodo);

    $nodo = $doc->createElement('MontoTotalDeGastoAplicable', $this->total_comprobante);
    $root->appendChild($nodo);
    */

    $nodo = $doc->createElement('TotalFactura', $this->total_comprobante);
    $root->appendChild($nodo);


    $nodo = $doc->createElement('NumeroCedulaReceptor', $this->receptor_numero_identificacion);
    $root->appendChild($nodo);

    $nodo = $doc->createElement('NumeroConsecutivoReceptor', $this->consecutivo);
    $root->appendChild($nodo);

    // Retornar XML como string
    return $doc->saveXML();
  }

  public function getMensajeConfirmacion()
  {
    $mensaje = 1;
    switch ($this->mensajeConfirmacion) {
      case 'ACEPTADA':
        $mensaje = 1;
        break;
      case 'ACEPTADOPARCIAL':
        $mensaje = 2;
        break;
      case 'RECHAZADA':
        $mensaje = 3;
        break;
    }
    return $mensaje;
  }

  public function getConsecutivo($secuencia)
  {
    // Obtener el número de la sucursal, con ceros a la izquierda hasta 3 caracteres
    $a_number = str_pad($this->location->numero_sucursal, 3, "0", STR_PAD_LEFT);

    // Obtener el número del punto de venta, con ceros a la izquierda hasta 5 caracteres
    $b_number = str_pad($this->location->numero_punto_venta, 5, "0", STR_PAD_LEFT);

    // Obtener el código de tipo de comprobante
    $c_number = $this->getComprobanteCode();

    // Generar el consecutivo concatenando todos los componentes
    $consecutivo = $a_number . $b_number . $c_number . $secuencia;

    // Retornar el consecutivo generado
    return $consecutivo;
  }

  public function getComprobanteCode()
  {
    $code = '';
    switch ($this->mensajeConfirmacion) {
      case 'ACEPTADO':
        $code = '05';  //Confirmación de aceptación del comprobante
        break;
      case 'ACEPTADOPARCIAL':
        $code = '06'; //Confirmación de aceptación parcial del comprobante
        break;
      case 'RECHAZADO':
        $code = '07'; //Confirmación de rechazo del comprobante
        break;
      default:
        throw new \Exception(__('Type of confirmation unknown'));
        break;
    }
    return $code;
  }

  public static function verifyResponseStatusHacienda($responseData)
  {
    $partes = explode('-', $responseData['clave']);
    $key = $partes[0];

    $comprobante = Comprobante::where('key', trim($key))->first();
    // Consulta el estado del comprobante
    if (!$comprobante) {
      Log::info('No se ha encontrado el comprobante de mensaje de receptor con key en verifyResponseStatusHacienda:', $responseData);
      return false;
    }

    $api = new ApiHacienda();
    //Log::info('Se llama al handleResponse de la api:', $responseData);
    $documentType = $comprobante->getComprobanteCode();
    $result = $api->handleResponse($responseData, $comprobante, $documentType);
    //Log::info('el resultado de handleResponse de la api:', $result);

    if ($responseData['ind-estado'] == 'aceptado') {
      // Nota de crédito o nota de debito
      //$sent = Helpers::sendComprobanteElectronicoEmail($transaction->id);
      /*
      if ($sent) {
        $transaction->fecha_envio_email = now();
        $transaction->save();

        $menssage = __('An email has been sent to the following addresses:') . ' ' . $transaction->contact->email;
        if (!empty($transaction->email_cc)) {
          $menssage .= ' ' . __('with copy to') . ' ' . $transaction->email_cc;
        }
      }
        */
    } elseif ($responseData['ind-estado'] == 'rechazado') {
      $sent = Helpers::sendNotificationMensajeElectronicoRejected($comprobante->id);
      // Opcional: Log de la respuesta para auditoría
      if ($sent)
        Log::info('Se ha enviar una notificación de comprobante rechazado:', $responseData);
      else
        Log::info('No se ha podido enviar una notificación de comprobante rechazado:', $responseData);
    }
  }
}
