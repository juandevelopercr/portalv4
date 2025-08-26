<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiBCCR
{
  private $baseUrl;
  private $token;
  private $email;
  private $nombre;

  public function __construct()
  {
    $this->baseUrl = 'https://gee.bccr.fi.cr/Indicadores/Suscripciones/WS/wsindicadoreseconomicos.asmx/ObtenerIndicadoresEconomicos';
    $this->token = config('services.bccr.token'); // Configura tu token en config/services.php
    $this->email = config('services.bccr.email'); // Configura tu correo en config/services.php
    $this->nombre = config('services.bccr.nombre'); // Configura tu nombre en config/services.php
  }

  public function obtenerIndicadorEconomico($indicador, $fechaInicio, $fechaFinal)
  {
    $queryParams = [
      'Indicador' => $indicador,
      'FechaInicio' => $fechaInicio,
      'FechaFinal' => $fechaFinal,
      'Nombre' => $this->nombre,
      'SubNiveles' => 'N',
      'CorreoElectronico' => $this->email,
      'Token' => $this->token,
    ];

    try {
      $response = Http::withOptions([
        'verify' => app()->environment('production')
          ? '/etc/ssl/certs/ca-bundle.crt' // ✅ SOLO en producción: usa CA personalizada
          : false
      ])->get($this->baseUrl, $queryParams);

      if ($response->successful()) {
        //Log::info('API Response:', ['body' => $response->body()]);
        return $this->procesarRespuestaXml($response->body());
      } else {
        Log::error('Error en la respuesta de la API BCCR', [
          'status' => $response->status(),
          'body' => $response->body(),
        ]);
        return null;
      }
    } catch (\Exception $e) {
      Log::error('Error al conectar con la API BCCR', ['message' => $e->getMessage()]);
      return null;
    }
  }

  private function procesarRespuestaXml($responseBody)
  {
    try {
      // Cargar el XML de la respuesta
      $xml = simplexml_load_string($responseBody, null, LIBXML_NOCDATA);

      if ($xml === false) {
        //throw new \Exception('No se pudo interpretar el XML.');
        Log::error('No se pudo interpretar el XML.');
      }

      // Navegar al nodo `diffgr:diffgram` y extraer el valor deseado
      $namespaces = $xml->getNamespaces(true);
      $diffgram = $xml->children($namespaces['diffgr'])->diffgram;
      $dataset = $diffgram->children()->Datos_de_INGC011_CAT_INDICADORECONOMIC;

      if (isset($dataset->INGC011_CAT_INDICADORECONOMIC)) {
        $indicator = $dataset->INGC011_CAT_INDICADORECONOMIC;
        $tipoCambio = (string) $indicator->NUM_VALOR;

        return $tipoCambio;
      }

      //throw new \Exception('No se encontró el nodo INGC011_CAT_INDICADORECONOMIC en la respuesta.');
      Log::error('No se encontró el nodo INGC011_CAT_INDICADORECONOMIC en la respuesta.');
    } catch (\Exception $e) {
      Log::error('Error al procesar la respuesta XML', ['message' => $e->getMessage()]);
      return null;
    }
  }
}
