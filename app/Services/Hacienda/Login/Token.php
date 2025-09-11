<?php

namespace App\Services\Hacienda\Login;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Token
{
  /**
   * URL de autenticación del IDP.
   * @var string
   */
  protected $authUrl;

  /**
   * Client ID según el entorno.
   * @var string
   */
  protected $clientId;

  /**
   * Almacenamiento de tokens.
   * @var TokenStorage
   */
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

    $this->tokenStorage = new TokenStorage();
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
   * Solicita un nuevo token al IDP usando Http de Laravel.
   *
   * @param string $username
   * @param string $password
   *
   * @return string
   * @throws Exception
   */
  protected function requestNewToken($username, $password)
  {
    try {
      if (env('HACIENDA_ENVIRONMENT') == 'prod') {
        $response = Http::withOptions([
          'verify' => false,  // Deshabilitar la verificación SSL si es necesario
        ])
          ->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',  // Aquí defines el tipo de contenido
          ])
          ->asForm()
          ->post($this->authUrl, [
            'client_id'     => $this->clientId,
            'username'      => $username,
            'password'      => $password,
            'grant_type'    => 'password',
            'client_secret' => '',
          ]);
      } else {
        $response = Http::withOptions([
          'verify' => false,  // Deshabilitar la verificación SSL si es necesario
        ])
          ->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',  // Aquí defines el tipo de contenido
          ])
          ->asForm()
          ->post($this->authUrl, [
            'client_id'     => $this->clientId,
            'username'      => $username,
            'password'      => $password,
            'grant_type'    => 'password',
            'client_secret' => '',
            'scopes'         => '',
          ]);
      }
      //'scopes'        => '',

      dd($response);

      // 🚨 Si la respuesta es 401 => credenciales inválidas
      if ($response->status() === 401) {
        throw new \Exception("Error de autenticación: credenciales inválidas al intentar obtener el token.");
      }

      // Verificar si la respuesta es exitosa
      if ($response->failed()) {
        throw new Exception('Error obteniendo el token: ' . $response->body());
      }

      if (!$response->json())
        throw new Exception('Error obteniendo el token, no se ha autorizado con las credenciales suministradas');

      $data = $response->json();

      //dd($data);

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

      throw new Exception('Error obteniendo el token: ' . $response->body());
    } catch (\Throwable $e) {
      Log::error('Excepción en requestNewToken: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
      ]);

      throw new Exception('Error obteniendo el token: ' . $e->getTraceAsString());
    }
  }

  /**
   * Renueva el access_token usando el refresh_token con Http.
   *
   * @param string $issuerId
   * @param string $refreshToken
   *
   * @return string
   * @throws Exception
   */
  protected function refreshToken($issuerId, $refreshToken)
  {
    if (env('HACIENDA_ENVIRONMENT') == 'prod') {
      $response = Http::withOptions([
        'verify' => false,  // Deshabilitar la verificación SSL si es necesario
      ])
        ->withHeaders([
          'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',  // Aquí defines el tipo de contenido
        ])
        ->asForm()
        ->post($this->authUrl, [
          'client_id'     => $this->clientId,
          'grant_type'    => 'refresh_token',
          'refresh_token' => $refreshToken,
          'client_secret' => '',
        ]);
      //'scopes'        => ''
    } else {
      $response = Http::withOptions([
        'verify' => false,  // Deshabilitar la verificación SSL si es necesario
      ])
        ->withHeaders([
          'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',  // Aquí defines el tipo de contenido
        ])
        ->asForm()
        ->post($this->authUrl, [
          'client_id'     => $this->clientId,
          'grant_type'    => 'refresh_token',
          'refresh_token' => $refreshToken,
          'client_secret' => '',
          'scopes'        => ''
        ]);
    }

    // Verificar si la respuesta es exitosa
    if ($response->failed()) {
      throw new Exception('Error renovando el token: ' . $response->body());
    }

    $data = $response->json();

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

    throw new Exception('Error renovando el token: ' . $response->body());
  }

  /**
   * Cierra la sesión en el IDP usando Http.
   *
   * @param string $issuerId
   * @param string $refreshToken
   *
   * @return bool
   * @throws Exception
   */
  public function closeSession($issuerId, $refreshToken)
  {
    $response = Http::asForm()->post($this->authUrl, [
      'client_id'     => $this->clientId,
      'refresh_token' => $refreshToken,
    ]);

    // Verificar si la respuesta es exitosa
    if ($response->failed()) {
      throw new Exception('Error cerrando sesión: ' . $response->body());
    }

    // Eliminamos los tokens de la base de datos
    $this->tokenStorage->deleteTokens($issuerId);

    return true;
  }
}
