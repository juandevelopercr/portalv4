<?php

namespace App\Services\Hacienda;

use App\Services\Hacienda\Login\TokenStorage;
use Exception;
use Illuminate\Support\Facades\Log;

class ApiHacienda
{
  protected $authUrl;
  protected $clientId;
  protected $tokenStorage;

  public function __construct()
  {
    // Configuración según el entorno
    if (env('HACIENDA_ENVIRONMENT') == 'prod') {
      $this->authUrl = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';
      $this->clientId = 'api-prod';
    } else {
      $this->authUrl = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
      $this->clientId = 'api-stag';
    }

    $this->tokenStorage = new TokenStorage(); // Asegúrate de tener la clase TokenStorage para guardar y recuperar los tokens.
  }

  /**
   * Obtiene el token de autenticación.
   *
   * @param string $username
   * @param string $password
   *
   * @return string
   * @throws Exception
   */
  public function getToken($username, $password)
  {
    // Verificamos si el access_token es válido
    if ($this->tokenStorage->isAccessTokenValid($username)) {
      $tokenData = $this->tokenStorage->getTokens($username);
      return $tokenData['access_token'];
    }

    // Si el refresh_token es válido, lo usamos para renovar el access_token
    if ($this->tokenStorage->isRefreshTokenValid($username)) {
      $tokenData = $this->tokenStorage->getTokens($username);
      return $this->refreshToken($username, $tokenData['refresh_token']);
    }

    // Si no, solicitamos un nuevo token
    return $this->requestNewToken($username, $password);
  }

  /**
   * Solicita un nuevo token al IDP usando cURL.
   *
   * @param string $username
   * @param string $password
   *
   * @return string
   * @throws Exception
   */
  protected function requestNewToken($username, $password)
  {
    $data = [
      'client_id'     => $this->clientId,
      'username'      => $username,
      'password'      => $password,
      'grant_type'    => 'password',
      'client_secret' => '',
      'scope'         => ''
    ];

    $response = $this->makeCurlRequest($this->authUrl, $data);

    // Decodificamos la respuesta
    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
      // Guardamos los tokens en el almacenamiento
      $this->tokenStorage->saveTokens(
        $username,
        $data['access_token'],
        $data['expires_in'],
        $data['refresh_token'],
        $data['refresh_expires_in']
      );
      return $data['access_token'];
    }

    throw new Exception('Error obteniendo el token: ' . $response);
  }

  /**
   * Renueva el access_token usando el refresh_token.
   *
   * @param string $issuerId
   * @param string $refreshToken
   *
   * @return string
   * @throws Exception
   */
  protected function refreshToken($issuerId, $refreshToken)
  {
    $data = [
      'client_id'     => $this->clientId,
      'grant_type'    => 'refresh_token',
      'refresh_token' => $refreshToken,
      'client_secret' => '',
      'scope'         => ''
    ];

    $response = $this->makeCurlRequest($this->authUrl, $data);

    // Decodificamos la respuesta
    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
      // Guardamos los nuevos tokens en el almacenamiento
      $this->tokenStorage->saveTokens(
        $issuerId,
        $data['access_token'],
        $data['expires_in'],
        $data['refresh_token'],
        $data['refresh_expires_in']
      );
      return $data['access_token'];
    }

    throw new Exception('Error renovando el token: ' . $response);
  }

  /**
   * Realiza una solicitud cURL.
   *
   * @param string $url
   * @param array  $postData
   *
   * @return string
   * @throws Exception
   */
  protected function makeCurlRequest($url, $postData = [])
  {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/x-www-form-urlencoded',
    ]);

    // Configuración para evitar problemas con certificados SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      throw new Exception('Error en cURL: ' . curl_error($ch));
    }

    curl_close($ch);
    return $response;
  }

  /**
   * Enviar la factura al Ministerio de Hacienda.
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
    $url_api = $emisor->environment == 'produccion' ?
      'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/' :
      'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';

    $fecha = now()->toIso8601String(); // Usar Carbon para obtener la fecha en formato ISO 8601
    $callbackUrl = $this->getCallbackUrl($tipo_comprobante);

    Log::info('Callback enviado hacienda:', ['callback' => $callbackUrl]);

    $headers = [
      'Authorization' => 'Bearer ' . $token,
      'Content-Type' => 'application/json'
    ];

    $payload = $this->buildPayload($transaction, $emisor, $comprobanteXML, $fecha, $callbackUrl);

    try {
      $response = $this->makeCurlRequest($url_api, $payload, $headers);

      // Verificar respuesta exitosa
      $responseData = json_decode($response, true);

      if (isset($responseData['status']) && $responseData['status'] == 'success') {
        return [
          'error' => 0,
          'mensaje' => __('Comprobante enviado exitosamente.'),
          'type' => 'success',
          'titulo' => 'Éxito',
          'response' => $responseData
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
        'type' => 'danger',
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
  private function buildPayload($transaction, $emisor, $comprobanteXML, $fecha, $callbackUrl)
  {
    $payLoad = [
      'clave' => $transaction->clave,
      'fecha' => $fecha,
      'emisor' => [
        'tipoIdentificacion' => $emisor->identificationType->code,
        'numeroIdentificacion' => $emisor->identification
      ],
      'receptor' => [
        'tipoIdentificacion' => $transaction->contact->identificationType->code,
        'numeroIdentificacion' => $transaction->contact->identification
      ],
      'callbackUrl' => $callbackUrl,
      'comprobanteXml' => $comprobanteXML
    ];

    return $payLoad;
  }

  /**
   * Manejar el error si la respuesta de la API no es exitosa.
   *
   * @param string $response
   * @param string $tipo_comprobante
   * @return array
   */
  private function handleError($response, $tipo_comprobante)
  {
    return [
      'error' => 1,
      'mensaje' => "Ha ocurrido un error al enviar el comprobante electrónico de tipo: " . $tipo_comprobante .
        ". " . $response,
      'type' => 'danger',
      'titulo' => 'Error',
      'response' => $response
    ];
  }
}
