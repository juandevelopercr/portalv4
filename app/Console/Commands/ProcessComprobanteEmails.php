<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Comprobante;
use App\Services\DocumentSequenceService;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;

class ProcessComprobanteEmails extends Command
{
  protected $signature = 'comprobantes:process-emails';
  protected $description = 'Procesa emails con comprobantes electrónicos';

  public function handle()
  {
    Log::info('Iniciando procesamiento de emails con comprobantes');

    $business = Business::first();
    if (!$business) {
      Log::error('No se encontró configuración de negocio');
      $this->error('No business configuration found');
      return;
    }

    // Configuración óptima para IMAP SSL
    $host = $business->host_imap;
    $port = $business->puerto_imap;
    $encryption = $business->imap_encryptation;;
    $validateCert = false;

    $this->info("Conectando a: {$host}:{$port} (SSL)");

    try {
      // Configuración para ClientManager (v6.x)
      $clientManager = new ClientManager([
        'accounts' => [
          'default' => [
            'host'          => $host,
            'port'          => $port,
            'encryption'    => $encryption,
            'validate_cert' => $validateCert,
            'username'      => $business->user_imap,
            'password'      => $business->pass_imap,
            'protocol'      => 'imap',
            'timeout'       => 30
          ]
        ]
      ]);

      $this->info("Creando cliente IMAP...");
      $client = $clientManager->account('default');

      $this->info("Estableciendo conexión...");
      $client->connect();

      $this->info("✓ Conexión exitosa. Obteniendo bandeja de entrada...");
      $inbox = $client->getFolder('INBOX');
      $messages = $inbox->messages()->all()->get();

      $this->info("Procesando " . count($messages) . " mensajes...");
      foreach ($messages as $message) {
        $this->processMessage($message, $business);
      }

      $client->disconnect();
      $this->info("✔ Proceso completado exitosamente");
    } catch (ConnectionFailedException $e) {
      $this->handleConnectionError($e, $host);
    } catch (\Exception $e) {
      $this->handleGenericError($e);
    }
  }

  protected function handleConnectionError($e, $host)
  {
    $errorMsg = "Error de conexión IMAP: " . $e->getMessage();
    $this->error($errorMsg);
    Log::error($errorMsg);

    $this->error("\nPosibles soluciones:");
    $this->line("1. Verifica las credenciales con un cliente como Thunderbird");
    $this->line("2. Prueba con:");
    $this->line("   - 'validate_cert' => false");
    $this->line("   - Puerto 143 con 'encryption' => 'tls'");
    $this->line("3. Prueba la conexión manual:");
    $this->line("   openssl s_client -connect {$host}:993 -crlf");
  }

  protected function handleGenericError($e)
  {
    $errorMsg = "Error: " . $e->getMessage();
    $this->error($errorMsg);
    Log::error($errorMsg, [
      'exception' => $e,
      'trace' => $e->getTraceAsString()
    ]);
  }

  /*
  private function processMessage($message, Business $business)
  {
    $attachments = $message->getAttachments();
    $comprobanteData = null;
    $xmlComprobante = null;
    $xmlRespuesta = null;
    $pdf = null;

    // Procesar adjuntos
    foreach ($attachments as $attachment) {
      $extension = strtolower(pathinfo($attachment->name, PATHINFO_EXTENSION));

      if ($extension === 'xml') {
        $content = $attachment->content;
        $xml = $this->parseXml($content);

        if ($xml && $this->isComprobanteXml($xml)) {
          $comprobanteData = $this->extractComprobanteData($xml, $business);
          $xmlComprobante = $content;
        } elseif ($xml && $this->isRespuestaXml($xml)) {
          $xmlRespuesta = $content;
        }
      } elseif ($extension === 'pdf') {
        $pdf = $attachment->content;
      }
    }

    // Crear comprobante si tenemos datos válidos
    if ($comprobanteData) {
      try {
        $comprobante = $this->createComprobante($comprobanteData, $xmlComprobante, $xmlRespuesta, $pdf);
        $message->move('PROCESADOS');
        Log::info('Comprobante creado: ' . $comprobante->key);
      } catch (\Exception $e) {
        Log::error('Error creando comprobante: ' . $e->getMessage());
        $message->move('ERRORES');
      }
    } else {
      $message->move('RECHAZADOS');
    }
  }
  */
  private function processMessage($message, Business $business)
  {
    try {
      $this->info("Procesando mensaje ID: " . $message->getUid());

      $attachments = $message->getAttachments();
      $this->info("Adjuntos encontrados: " . count($attachments));

      if (count($attachments) === 0) {
        $this->warn("⚠ Mensaje sin adjuntos - Moviendo a RECHAZADOS");
        $message->move('RECHAZADOS');
        return;
      }

      $comprobanteData = null;
      $xmlComprobante = null;
      $xmlRespuesta = null;
      $pdf = null;

      foreach ($attachments as $attachment) {
        try {
          $extension = strtolower(pathinfo($attachment->name, PATHINFO_EXTENSION));
          $this->info("Procesando adjunto: " . $attachment->name);

          if ($extension === 'xml') {
            $content = $attachment->content;
            if (empty($content)) {
              $this->warn("XML vacío en adjunto: " . $attachment->name);
              continue;
            }

            $xml = $this->parseXml($content);
            if (!$xml) {
              $this->warn("XML inválido en adjunto: " . $attachment->name);
              continue;
            }

            if ($this->isComprobanteXml($xml)) {
              $comprobanteData = $this->extractComprobanteData($xml, $business);
              $xmlComprobante = $content;
              $this->info("✓ XML de comprobante válido encontrado");
            } elseif ($this->isRespuestaXml($xml)) {
              $xmlRespuesta = $content;
              $this->info("✓ XML de respuesta encontrado");
            }
          } elseif ($extension === 'pdf') {
            $pdf = $attachment->content;
            $this->info("✓ PDF adjunto encontrado");
          }
        } catch (\Exception $e) {
          $this->error("Error procesando adjunto: " . $e->getMessage());
          Log::error("Error en adjunto " . $attachment->name . ": " . $e->getMessage());
        }
      }

      if ($comprobanteData) {
        // Verificar duplicado con más criterios (opcional)
        $existing = Comprobante::where('key', $comprobanteData['key'])
          ->where('status', '!=', 'ERROR')
          ->first();

        if ($existing) {
          $this->info("✔ Comprobante ya existe [ID: {$existing->id}]: {$comprobanteData['key']}");

          // Opcional: Actualizar fecha de último visto
          $existing->touch();

          // Mover el mensaje
          try {
            $message->move('PROCESADOS');
            Log::info("Comprobante duplicado manejado", [
              'comprobante_id' => $existing->id,
              'key' => $comprobanteData['key']
            ]);
          } catch (\Exception $e) {
            $this->error("Error moviendo mensaje a PROCESADOS: " . $e->getMessage());
            Log::error("Error moviendo mensaje a procesado: " . $e->getMessage(), [
              'trace' => $e->getTraceAsString()
            ]);
          }
          return;
        }

        try {
          $comprobante = $this->createComprobante($comprobanteData, $xmlComprobante, $xmlRespuesta, $pdf);
          $message->move('PROCESADOS');
          $this->info("✔ Comprobante creado: " . $comprobante->key);
        } catch (\Exception $e) {
          $this->error("Error creando comprobante: " . $e->getMessage());
          $message->move('ERRORES');
        }
      } else {
        $this->warn("⚠ No se encontró XML válido - Moviendo a RECHAZADOS");
        $message->move('RECHAZADOS');
      }
    } catch (\Exception $e) {
      $this->error("Error procesando mensaje: " . $e->getMessage());
      Log::error("Error procesando mensaje: " . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
      ]);
    }
  }

  private function parseXml(string $content): ?SimpleXMLElement
  {
    try {
      return new SimpleXMLElement($content);
    } catch (\Exception $e) {
      Log::warning('XML inválido: ' . $e->getMessage());
      return null;
    }
  }

  private function isComprobanteXml(SimpleXMLElement $xml): bool
  {
    $rootNode = $xml->getName();
    return in_array($rootNode, [
      'FacturaElectronica',
      'NotaDebitoElectronica',
      'NotaCreditoElectronica',
      'TiqueteElectronico',
      'FacturaElectronicaCompra',
      'FacturaElectronicaExportacion',
      'ReciboElectronicoPago'
    ]);
  }

  private function isRespuestaXml(SimpleXMLElement $xml): bool
  {
    return $xml->getName() === 'MensajeHacienda';
  }

  private function extractComprobanteData(SimpleXMLElement $xml, Business $business): ?array
  {
    try {
      $tipoDocumento = $this->getTipoDocumento($xml);
      $emisorId = (string)$xml->Emisor->Identificacion->Numero;
      $receptorId = (string)$xml->Receptor->Identificacion->Numero;

      // Validar que pertenezca a la empresa
      $location = BusinessLocation::where('identification', $receptorId)->first();
      if (!$location) {
        $location = BusinessLocation::where('identification', $emisorId)->first();
      }

      if (!$location) {
        Log::warning('Ubicación no encontrada para: ' . $receptorId . ' o ' . $emisorId);
        return null;
      }

      return [
        'location_id' => $location->id,
        'key' => (string)$xml->Clave,
        //'consecutivo' => (string)$xml->NumeroConsecutivo,
        'fecha_emision' => (string)$xml->FechaEmision,
        'emisor_nombre' => (string)$xml->Emisor->Nombre,
        'emisor_tipo_identificacion' => (string)$xml->Emisor->Identificacion->Tipo,
        'emisor_numero_identificacion' => $emisorId,
        'receptor_nombre' => (string)$xml->Receptor->Nombre,
        'receptor_tipo_identificacion' => (string)$xml->Receptor->Identificacion->Tipo,
        'receptor_numero_identificacion' => $receptorId,
        'total_comprobante' => (float)$xml->ResumenFactura->TotalComprobante,
        'tipo_cambio' => (float)($xml->ResumenFactura->TipoCambio ?? 1),
        'moneda' => (string)($xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda ?? 'CRC'),
        'tipo_documento' => $tipoDocumento,
        'condicion_venta' => (string)($xml->CondicionVenta ?? '01'),
        'plazo_credito' => (int)($xml->PlazoCredito ?? 0),
        'medio_pago' => (string)($xml->MedioPago ?? '01'),
        'status' => 'PENDIENTE',
        'detalle' => 'Comprobante Aceptado',
        'mensajeConfirmacion' => 'ACEPTADO',
        'codigo_actividad' => (string)($xml->CodigoActividad ?? ''),
        'situacion_comprobante' => (string)($xml->SituacionComprobante ?? '1'),
      ];
    } catch (\Exception $e) {
      Log::error('Error extrayendo datos comprobante: ' . $e->getMessage());
      return null;
    }
  }

  private function getTipoDocumento(SimpleXMLElement $xml): string
  {
    $rootNode = $xml->getName();
    $map = [
      'FacturaElectronica' => '01',
      'NotaDebitoElectronica' => '02',
      'NotaCreditoElectronica' => '03',
      'TiqueteElectronico' => '04',
      'mensajeReceptor' => '05',
      'mensajeReceptor' => '06',
      'mensajeReceptor' => '07',
      'FacturaElectronicaCompra' => '08',
      'FacturaElectronicaExportacion' => '09',
      'ReciboElectronicoPago' => '10',
    ];

    return $map[$rootNode] ?? '01';
  }

  /*
  private function createComprobante(array $data, string $xmlComprobante, ?string $xmlRespuesta, ?string $pdf): ?Comprobante
  {
    try {
      $locationId = $data['location_id'];
      $fechaEmision = Carbon::parse($data['fecha_emision']);
      $year = $fechaEmision->format('Y');
      $month = $fechaEmision->format('m');

      // Crear estructura de carpetas
      $basePath = "comprobantes/{$locationId}/{$year}/{$month}";
      Storage::disk('public')->makeDirectory($basePath);

      // Guardar archivos
      $comprobanteFilename = $data['key'] . '.xml';
      $comprobantePath = "{$basePath}/{$comprobanteFilename}";
      $bytesWritten = Storage::disk('public')->put($comprobantePath, $xmlComprobante);

      if ($bytesWritten === false) {
        throw new \Exception("Error al guardar el XML del comprobante");
      }

      $respuestaPath = null;
      if ($xmlRespuesta) {
        $respuestaFilename = $data['key'] . '_respuesta.xml';
        $respuestaPath = "{$basePath}/{$respuestaFilename}";
        if (Storage::disk('public')->put($respuestaPath, $xmlRespuesta) === false) {
          throw new \Exception("Error al guardar el XML de respuesta");
        }
      }

      $pdfPath = null;
      if ($pdf) {
        $pdfFilename = $data['key'] . '.pdf';
        $pdfPath = "{$basePath}/{$pdfFilename}";
        if (Storage::disk('public')->put($pdfPath, $pdf) === false) {
          throw new \Exception("Error al guardar el PDF");
        }
      }

      // Crear registro en BD con transacción
      return DB::transaction(function () use ($data, $comprobantePath, $respuestaPath, $pdfPath) {
        $comprobante = Comprobante::create([
          ...$data,
          'xml_path' => $comprobantePath,
          'xml_respuesta_path' => $respuestaPath,
          'pdf_path' => $pdfPath
        ]);

        if (!$comprobante) {
          throw new \Exception("No se pudo crear el registro en la base de datos");
        }

        return $comprobante;
      });

      // Necesito asegurarme que si hay un error continue procesando el resto de las entradas
      // Además necesito aqui si se creo el comprobante poder ejecutar otro proceso como lo haria
    } catch (\Exception $e) {
      Log::error("Error al crear comprobante: " . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'data' => [
          'key' => $data['key'] ?? null,
          'location_id' => $data['location_id'] ?? null
        ]
      ]);

      $this->error("Error al crear comprobante: " . $e->getMessage());

      // Opcional: Revertir archivos guardados si falla la BD
      if (isset($comprobantePath) && Storage::disk('public')->exists($comprobantePath)) {
        Storage::disk('public')->delete($comprobantePath);
      }
      if (isset($respuestaPath) && Storage::disk('public')->exists($respuestaPath)) {
        Storage::disk('public')->delete($respuestaPath);
      }
      if (isset($pdfPath) && Storage::disk('public')->exists($pdfPath)) {
        Storage::disk('public')->delete($pdfPath);
      }

      return null;
    }
  }
  */

  private function createComprobante(array $data, string $xmlComprobante, ?string $xmlRespuesta, ?string $pdf): ?Comprobante
  {
    // Variables para almacenar las rutas de los archivos creados
    $comprobantePath = null;
    $respuestaPath = null;
    $pdfPath = null;

    try {
        $locationId = $data['location_id'];
        $fechaEmision = Carbon::parse($data['fecha_emision']);
        $year = $fechaEmision->format('Y');
        $month = $fechaEmision->format('m');

        // Crear estructura de carpetas
        $basePath = "comprobantes/{$locationId}/{$year}/{$month}";
        Storage::disk('public')->makeDirectory($basePath);

        // Guardar archivos
        $comprobanteFilename = $data['key'] . '.xml';
        $comprobantePath = "{$basePath}/{$comprobanteFilename}";
        $bytesWritten = Storage::disk('public')->put($comprobantePath, $xmlComprobante);

        if ($bytesWritten === false) {
            throw new \Exception("Error al guardar el XML del comprobante");
        }

        if ($xmlRespuesta) {
            $respuestaFilename = $data['key'] . '_respuesta.xml';
            $respuestaPath = "{$basePath}/{$respuestaFilename}";
            if (Storage::disk('public')->put($respuestaPath, $xmlRespuesta) === false) {
                throw new \Exception("Error al guardar el XML de respuesta");
            }
        }

        if ($pdf) {
            $pdfFilename = $data['key'] . '.pdf';
            $pdfPath = "{$basePath}/{$pdfFilename}";
            if (Storage::disk('public')->put($pdfPath, $pdf) === false) {
                throw new \Exception("Error al guardar el PDF");
            }
        }

        // Crear registro en BD con transacción
        $comprobante = DB::transaction(function () use ($data, $comprobantePath, $respuestaPath, $pdfPath) {
            $comprobante = Comprobante::create([
                ...$data,
                'xml_path' => $comprobantePath,
                'xml_respuesta_path' => $respuestaPath,
                'pdf_path' => $pdfPath
            ]);

            return $comprobante;
        });

        // ============================================================
        // PUNTO 2: Ejecutar procesos adicionales después de crear el comprobante
        // ============================================================
        // Aquí puedes llamar a otros procesos que necesiten ejecutarse después de crear el comprobante
        $this->sendDocumentToHacienda($comprobante);

        return $comprobante;

    } catch (\Exception $e) {
        Log::error("Error al crear comprobante: " . $e->getMessage(), [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => [
                'key' => $data['key'] ?? null,
                'location_id' => $data['location_id'] ?? null
            ]
        ]);

        $this->error("Error al crear comprobante: " . $e->getMessage());

        // ============================================================
        // PUNTO 1: Limpiar archivos creados si hubo error
        // ============================================================
        // Eliminar archivos guardados si falla la transacción
        //$this->limpiarArchivosCreados($comprobantePath, $respuestaPath, $pdfPath);

        return null;
    }
  }

  private function sendDocumentToHacienda(Comprobante $comprobante)
  {
    Log::info('Iniciando el envio del comprobante a hacienda en comando');

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
      Log::error('Ha ocurrido un error al intentar identificarse en la api de hacienda en comando: ' . $e->getMessage());
      throw new \Exception("Ha ocurrido un error al intentar identificarse en la api de hacienda en comando: ". $e->getMessage());
    }

    $tipoDocumento = $comprobante->getComprobanteCode();

    $api = new ApiHacienda();
    $result = $api->send($xml, $token, $comprobante, $comprobante->location, $tipoDocumento);
    if ($result['error'] == 0) {
      $comprobante->status = Comprobante::RECIBIDA;
      $comprobante->created_at = \Carbon\Carbon::now();
    } else {
      Log::error('Ha ocurrido un error al enviar el comprobante a hacienda en comando: ' . $result['mensaje']);
      throw new \Exception('Ha ocurrido un error al enviar el comprobante a hacienda en comando: ' . $result['mensaje']);
    }

    // Guardar la transacción
    if (!$comprobante->save()) {
      Log::error('Ha ocurrido un error al intentar guardar el comprobante en comando');
      throw new \Exception('Ha ocurrido un error al intentar guardar el comprobante en comando');
    }
  }

  // ============================================================
  // FUNCIÓN PARA LIMPIAR ARCHIVOS CREADOS (PUNTO 1)
  // ============================================================
  private function limpiarArchivosCreados(?string $comprobantePath, ?string $respuestaPath, ?string $pdfPath)
  {
    try {
        $disk = Storage::disk('public');

        if ($comprobantePath && $disk->exists($comprobantePath)) {
            $disk->delete($comprobantePath);
        }

        if ($respuestaPath && $disk->exists($respuestaPath)) {
            $disk->delete($respuestaPath);
        }

        if ($pdfPath && $disk->exists($pdfPath)) {
            $disk->delete($pdfPath);
        }

        $this->info("Archivos temporales eliminados después de error");
    } catch (\Exception $e) {
        Log::error("Error al limpiar archivos: " . $e->getMessage(), [
            'paths' => compact('comprobantePath', 'respuestaPath', 'pdfPath')
        ]);
        $this->error("Error al limpiar archivos: " . $e->getMessage());
    }
  }
}
