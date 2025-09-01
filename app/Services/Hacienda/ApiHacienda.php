<?php

namespace App\Services\Hacienda;

use App\Models\Comprobante;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiHacienda
{
  /**
   * Enviar transaction a la API de Hacienda
   *
   * @param string $comprobanteXML
   * @param string $token
   * @param object $transaction
   * @param object $emisor
   * @param string $tipo_comprobante
   * @return array
   */
  public function send($comprobanteXML, $token, $transaction, $emisor, $tipo_comprobante)
  {
    Log::info('Entró al send de la api');

    $url_api = $emisor->environment == 'produccion' ?
      'https://api.comprobanteselectronicos.go.cr/recepcion/v1/' :
      'https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';

    $fecha = Carbon::now('America/Costa_Rica')->toIso8601String(); // Usar Carbon para obtener la fecha en formato ISO 8601
    $callbackUrl = $this->getCallbackUrl($tipo_comprobante);

    Log::info('Callback enviado en send hacienda:', ['callback' => $callbackUrl]);

    $headers = [
      'Authorization' => 'bearer ' . $token,
    ];

    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      // Es un mensaje de receptor
      $payload = $this->buildPayloadMensajeReceptor($transaction, $comprobanteXML, $fecha, $callbackUrl);
      $key = $transaction->key . '-' . $transaction->consecutivo;
    } else {
      $payload = $this->buildPayload($transaction, $comprobanteXML, $fecha, $callbackUrl);
      $key = $transaction->key;
    }

    try {
      $response = Http::withHeaders($headers)
        ->withOptions([
          'verify' => false,  // Ignorar la verificación SSL
        ])
        ->post($url_api, $payload);

      // Verificar respuesta exitosa
      if ($response->successful()) {
        return [
          'error' => 0,
          'mensaje' => __("El comprobante electrónico con key: [" . $key . "] se recibió correctamente, queda pendiente la validación de esta y el envío de la respuesta de  Hacienda."),
          'type' => 'success',
          'titulo' => 'Éxito',
          'response' => $response->json()
        ];
      } else {
        return $this->handleError($response, $tipo_comprobante);
      }
    } catch (Exception $e) {
      // Log de errores
      Log::error('Error al enviar comprobante a hacienda: ' . $e->getMessage());
      return [
        'error' => 1,
        'mensaje' => __('Error al enviar el comprobante electrónico a hacienda: ') . $e->getMessage(),
        'type' => 'error',
        'titulo' => 'Error',
        'response' => null
      ];
    }
  }

  /**
   * Generar URL de callback dependiendo del tipo de documento.
   *
   * @param string $tipo_comprobante
   * @return string
   */
  private function getCallbackUrl($tipo_comprobante)
  {
    $url = config('app.url'); // Asegúrate de que config/app.php tenga la URL correcta
    switch ($tipo_comprobante) {
      case "01":
        return $url . '/api/factura-call-back';
      case "02":
        return $url . '/api/nota-debito-call-back';
      case "03":
        return $url . '/api/nota-credito-call-back';
      case "04":
        return $url . '/api/tiquete-call-back';
      case "05":
        return $url . '/api/mensaje-call-back';
      case "06":
        return $url . '/api/mensaje-call-back';
      case "07":
        return $url . '/api/mensaje-call-back';
      case "08":
        return $url . '/api/factura-compra-call-back';
      case "09":
        return $url . '/api/factura-exportacion-call-back';
      case "10":
        return $url . '/api/recibo-pago-call-back';
      default:
        return '';
    }
    return $url;
  }

  /**
   * Construir el payload para la API.
   *
   * @param object $transaction
   * @param object $emisor
   * @param string $comprobanteXML
   * @param string $fecha
   * @param string $callbackUrl
   * @return array
   */
  private function buildPayload($transaction, $comprobanteXML, $fecha, $callbackUrl)
  {
    if ($transaction->document_type == 'FEC') {
      $emisor = $transaction->contact;
      $receptor = $transaction->location;
    } else {
      $emisor = $transaction->location;
      $receptor = $transaction->contact;
    }

    $payLoad = [
      'clave' => $transaction->key,
      'fecha' => $fecha,
      'emisor' => [
        'tipoIdentificacion' => $emisor->identificationType->code,
        'numeroIdentificacion' => $emisor->identification
      ],
      'receptor' => [
        'tipoIdentificacion' => $receptor->identificationType->code,
        'numeroIdentificacion' => $receptor->identification
      ],
      'callbackUrl' => $callbackUrl,
      'comprobanteXml' => $comprobanteXML
    ];

    Log::info('payLoad enviado end buildPayload hacienda:', ['payLoad' => $payLoad]);

    return $payLoad;
  }

  public function buildPayloadMensajeReceptor($transaction, $comprobanteXML, $fecha, $callbackUrl)
  {
    $payLoad = [
      'clave' => $transaction->key,
      'fecha' => $fecha,
      'emisor' => [
        'tipoIdentificacion' => $transaction->emisor_tipo_identificacion,
        'numeroIdentificacion' => $transaction->emisor_numero_identificacion
      ],
      'receptor' => [
        'tipoIdentificacion' => $transaction->receptor_tipo_identificacion,
        'numeroIdentificacion' => $transaction->receptor_numero_identificacion
      ],
      'consecutivoReceptor' => $transaction->consecutivo,
      'callbackUrl' => $callbackUrl,
      'comprobanteXml' => $comprobanteXML
    ];

    Log::info('payLoad de mensaje de receptor enviado end buildPayload hacienda:', ['payLoad' => $payLoad]);

    return $payLoad;
  }

  /**
   * Manejar el error si la respuesta de la API no es exitosa.
   *
   * @param \Illuminate\Http\Client\Response $response
   * @param string $tipo_comprobante
   * @return array
   */
  private function handleError($response, $tipo_comprobante)
  {
    // Leer el contenido del cuerpo de la respuesta (si existe)
    $responseBody = $response->body();

    // Leer los encabezados para obtener más detalles
    $errorCause = $response->header('x-error-cause', 'No details available'); // Valor predeterminado si no existe

    Log::info('Error al enviar el payload:', [$responseBody, $response->headers()]);

    return [
      'error' => 1,
      'mensaje' => "Ha ocurrido un error al enviar el comprobante electrónico de tipo: " . $tipo_comprobante .
        ". " . $errorCause . " (más detalles en los encabezados)",
      'type' => 'danger',
      'titulo' => 'Error',
      'response' => $responseBody, // Aquí todavía estamos pasando el cuerpo de la respuesta
      'headers' => $response->headers() // Aquí mostramos los encabezados completos para inspección
    ];
  }

  public function getStatusComprobante($token, $transaction, $emisor, $tipo_comprobante)
  {
    // Determinar el URL según el entorno
    $url = $emisor->environment == 'produccion'
      ? 'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/'
      : 'https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';

    $key = $transaction->key;
    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      $key = $transaction->key . '-' . $transaction->consecutivo;
    }

    Log::info('El comprobante a consultar es:', ['key' => $key]);

    // Validación de la clave
    if (empty($key)) {
      return $this->generateErrorResponse('La clave no puede ser en blanco');
    }

    try {
      // Realizar la petición usando Laravel Http Client
      // Realizar la petición usando Laravel Http Client
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/x-www-form-urlencoded',
      ])
        ->withOptions([
          'verify' => false,  // Ignorar la verificación SSL
        ])
        ->get($url . $key);

      Log::info('Respuesta de la consulta de estado:', ['response' => $response]);

      // Verificar si la respuesta fue exitosa
      if ($response->failed()) {
        return $this->handleErrorStatus($response, $tipo_comprobante);
      }

      // Decodificar la respuesta JSON
      $responseData = $response->json();

      // Procesar la respuesta
      return $this->handleResponse($responseData, $transaction, $tipo_comprobante);
    } catch (\Exception $e) {
      // Capturar errores de red o problemas al ejecutar la solicitud
      return $this->generateErrorResponse('Error al comunicarse con la API: ' . $e->getMessage());
    }
  }

  public function handleResponse($responseData, $transaction, $tipo_comprobante)
  {
    $documento = '';
    switch ($tipo_comprobante) {
      case "01":
        $documento = 'La Factura Electrónica';
        break;
      case "02":
        $documento = 'La Nota de Débito Electrónica';
        break;
      case "03":
        $documento = 'La Nota de Crédito Electrónica';
        break;
      case "04":
        $documento = 'Tiquete electrónico';
        break;
      case "05":
        $documento = 'Confirmación de aceptación del comprobante electrónico ';
        break;
      case "06":
        $documento = 'Confirmación de aceptación parcial del comprobante electrónico';
        break;
      case "07":
        $documento = 'Confirmación de rechazo del comprobante electrónico';
        break;
      case "08":
        $documento = 'La factura Electrónica de Compra';
        break;
      case "09":
        $documento = 'Factura electrónica de exportación';
        break;
      case "10":
        $documento = 'Recibo Electrónico de Pago';
        break;
    }

    switch ($responseData['ind-estado']) {
      case 'rechazado':
        return $this->handleRejected($responseData, $transaction, $documento, $tipo_comprobante, 'error');
      case 'aceptado':
        return $this->handleAccepted($responseData, $transaction, $documento, $tipo_comprobante, 'success');
      case 'recibido':
        return $this->handleReceived($responseData, $transaction, $documento, $tipo_comprobante, 'warning');
      case 'procesando':
        return $this->handleProcessing($responseData, $transaction, $documento, $tipo_comprobante, 'warning');
      default:
        return $this->handleUnknownError($responseData, $transaction, $documento, $tipo_comprobante, 'error');
    }
  }

  private function handleRejected($responseData, $transaction, $documento, $tipo_comprobante, $type)
  {
    $estado = 'rechazado';
    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      $transaction->status = Comprobante::RECHAZADA;
      $key = $transaction->key . '-' . $transaction->consecutivo;
    } else {
      $key = $transaction->key;
      $transaction->status = Transaction::RECHAZADA;
      $transaction->proforma_status = Transaction::RECHAZADA;
    }
    $mensaje = "$documento con clave: [{$key}] fue rechazado por Hacienda.";

    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      return $this->saveXmlResponseMensaje($responseData, $transaction, $estado, $mensaje, $type);
    } else
      return $this->saveXmlResponse($responseData, $transaction, $estado, $mensaje, $type);
  }

  private function handleAccepted($responseData, $transaction, $documento, $tipo_comprobante, $type)
  {
    $estado = 'aceptado';
    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      $transaction->status = Comprobante::ACEPTADA;
      $key = $transaction->key . '-' . $transaction->consecutivo;
    } else {
      $transaction->status = Transaction::ACEPTADA;
      $key = $transaction->key;
    }
    $type = 'success';
    $titulo = "Información <hr class=\"kv-alert-separator\">";
    $mensaje = "$documento con clave: [{$key}] fue aceptada por Hacienda.";

    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      return $this->saveXmlResponseMensaje($responseData, $transaction, $estado, $mensaje, $type);
    } else
      return $this->saveXmlResponse($responseData, $transaction, $estado, $mensaje, $type);
  }

  private function handleReceived($responseData, $transaction, $documento, $tipo_comprobante, $type)
  {
    $estado = 'recibido';
    $mensaje = "$documento con clave: [{$transaction->key}] aún se encuentra en estado Recibida.";

    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      $transaction->status = Comprobante::RECIBIDA;
    } else {
      $transaction->status = Transaction::RECIBIDA;
    }

    $transaction->save();
    return ['mensaje' => $mensaje, 'type' => $type, 'titulo' => "Advertencia <hr class=\"kv-alert-separator\">", 'estado' => $estado];
  }

  private function handleProcessing($responseData, $transaction, $documento, $tipo_comprobante, $type)
  {
    $estado = 'procesando';
    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      $key = $transaction->key . '-' . $transaction->consecutivo;
    } else {
      $key = $transaction->key;
    }

    $mensaje = "$documento con clave: [{$key}] se encuentra en estado Procesando.";
    return ['mensaje' => $mensaje, 'type' => $type, 'titulo' => "Advertencia <hr class=\"kv-alert-separator\">", 'estado' => $estado];
  }

  private function handleUnknownError($responseData, $transaction, $documento, $tipo_comprobante, $type)
  {
    if (in_array($tipo_comprobante, ['05', '06', '07'])) {
      $key = $transaction->key . '-' . $transaction->consecutivo;
    } else {
      $key = $transaction->key;
    }

    $mensaje = "Ha ocurrido un error desconocido al consultar el estado de $documento con clave: [{$key}].";
    return ['mensaje' => $mensaje, 'type' => $type, 'titulo' => "Error <hr class=\"kv-alert-separator\">"];
  }

  private function saveXmlResponse($responseData, $transaction, $estado, $mensaje, $type)
  {
    Log::info('Entra al saveXmlResponse:', $responseData);
    // Decodificar la respuesta XML
    $xml_respuesta_hacienda = base64_decode($responseData['respuesta-xml']);

    // Obtener el año y mes de la fecha de la transacción
    $invoiceDate = \Carbon\Carbon::parse($transaction->invoice_date); // Asumiendo que invoice_date está en formato de fecha
    $year = $invoiceDate->format('Y');  // Año
    $month = $invoiceDate->format('m'); // Mes

    // Crear la carpeta de almacenamiento organizada por emisor, año y mes
    $emisorId = $transaction->location->id; // Obtener el ID del emisor
    $baseDir = storage_path('app/public/hacienda/' . $emisorId . '/' . $year . '/' . $month);

    // Crear las carpetas si no existen
    if (!file_exists($baseDir)) {
      mkdir($baseDir, 0777, true);
    }

    // Definir el nombre del archivo y la ruta completa
    $nombre_archivo = $transaction->key . '_respuesta.xml';
    $filePath = $baseDir . '/' . $nombre_archivo;

    // Guardar el archivo XML en la ruta especificada
    file_put_contents($filePath, $xml_respuesta_hacienda);

    // Actualizar la transacción con la ruta relativa del archivo
    $relativePath = 'hacienda/' . $emisorId . '/' . $year . '/' . $month . '/' . $nombre_archivo;
    $transaction->response_xml = $relativePath;
    $transaction->save();

    // Devolver mensaje de éxito
    return [
      'mensaje' => $mensaje,
      'type' => $type,
      'titulo' => "Información <hr class=\"kv-alert-separator\">",
      'estado' => $estado
    ];
  }

  private function saveXmlResponseMensaje($responseData, $transaction, $estado, $mensaje, $type)
  {
    Log::info('Entra al saveXmlResponse:', $responseData);
    // Decodificar la respuesta XML
    $xml_respuesta_hacienda = base64_decode($responseData['respuesta-xml']);

    // Obtener el año y mes de la fecha de la transacción
    $invoiceDate = \Carbon\Carbon::parse($transaction->created_at); // Asumiendo que invoice_date está en formato de fecha
    $year = $invoiceDate->format('Y');  // Año
    $month = $invoiceDate->format('m'); // Mes

    // Crear la carpeta de almacenamiento organizada por emisor, año y mes
    $emisorId = $transaction->location->id; // Obtener el ID del emisor
    $baseDir = storage_path('app/public/hacienda/' . $emisorId . '/' . $year . '/' . $month);

    // Crear las carpetas si no existen
    if (!file_exists($baseDir)) {
      mkdir($baseDir, 0777, true);
    }

    // Definir el nombre del archivo y la ruta completa
    $nombre_archivo = $transaction->key . '-' . $transaction->consecutivo . '_respuesta.xml';
    $filePath = $baseDir . '/' . $nombre_archivo;

    // Guardar el archivo XML en la ruta especificada
    file_put_contents($filePath, $xml_respuesta_hacienda);

    // Actualizar la transacción con la ruta relativa del archivo
    $relativePath = 'hacienda/' . $emisorId . '/' . $year . '/' . $month . '/' . $nombre_archivo;
    $transaction->xml_respuesta_confirmacion_path = $relativePath;
    $transaction->save();

    // Devolver mensaje de éxito
    return [
      'mensaje' => $mensaje,
      'type' => $type,
      'titulo' => "Información <hr class=\"kv-alert-separator\">",
      'estado' => $estado
    ];
  }

  private function generateErrorResponse($message)
  {
    return ['error' => 1, 'mensaje' => $message, 'type' => 'danger', 'titulo' => 'Error', 'estado' => 'rechazado', 'actualizar' => 0];
  }

  private function handleErrorStatus($response, $tipo_comprobante)
  {
    // Leer el contenido del cuerpo de la respuesta (si existe)
    $responseBody = $response->body();

    // Intentar decodificar la respuesta para obtener detalles más claros (si es JSON)
    $decodedResponse = json_decode($responseBody, true);

    // Leer los encabezados para obtener más detalles
    $errorCause = $response->header('x-error-cause', 'No details available'); // Valor predeterminado si no existe
    $errorMessage = $decodedResponse['message'] ?? $errorCause;

    // Mensaje de error optimizado con detalles
    $mensaje = "Ha ocurrido un error al consultar el estado del comprobante electrónico de tipo: $tipo_comprobante. ";
    $mensaje .= $errorMessage ? $errorMessage : 'Detalles no disponibles.';

    // Agregar más detalles del error si están disponibles
    if ($decodedResponse) {
      $mensaje .= ' ' . ($decodedResponse['error_detail'] ?? 'Sin detalles adicionales.');
    }

    // Log o manejo adicional de la respuesta (opcional)
    Log::error("Error en la consulta del estado del comprobante: " . $responseBody);

    return [
      'error' => 1,
      'mensaje' => $mensaje,
      'type' => 'danger',
      'titulo' => 'Error',
      'response' => $responseBody, // Aquí todavía estamos pasando el cuerpo de la respuesta
      'headers' => $response->headers(), // Aquí mostramos los encabezados completos para inspección
    ];
  }
}
