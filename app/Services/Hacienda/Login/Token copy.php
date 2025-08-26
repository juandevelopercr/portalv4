<?php

namespace App\Services\Hacienda\Login;

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
      $this->authUrl  = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';
      $this->clientId = 'api-prod';
    } else {
      $this->authUrl  = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
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
   * Solicita un nuevo token al IDP.
   *
   * @param string $username
   * @param string $password
   *
   * @return string
   * @throws Exception
   */
  protected function requestNewToken($username, $password)
  {
    $response = $this->makeCurlRequest(
      $this->authUrl,
      [
        'client_id'     => $this->clientId,
        'username'      => $username,
        'password'      => $password,
        'grant_type'    => 'password',
        'client_secret' => '',
        'scopes'        => 'openid'
        //'scopes'        => '',
        //'scopes'        => 'offline_access openid', // Asegúrate de incluir los scopes necesarios
        //'scopes'        => 'offline_access', // Incluir el scope offline_access
      ]
    );

    dd($response);

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

    throw new \Exception('Error obteniendo el token: ' . $response);
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
    $response = $this->makeCurlRequest(
      $this->authUrl,
      [
        'client_id'     => $this->clientId,
        'grant_type'    => 'refresh_token',
        'refresh_token' => $refreshToken,
        'client_secret' => '',
        'scopes'        => '',
      ]
    );

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

    throw new \Exception('Error renovando el token: ' . $response);
  }

  /**
   * Cierra la sesión en el IDP.
   *
   * @param string $issuerId
   * @param string $refreshToken
   *
   * @return bool
   * @throws Exception
   */
  public function closeSession($issuerId, $refreshToken)
  {
    $response = $this->makeCurlRequest(
      $this->authUrl,
      [
        'client_id'     => $this->clientId,
        'refresh_token' => $refreshToken,
      ]
    );

    // Eliminamos los tokens de la base de datos
    $this->tokenStorage->deleteTokens($issuerId);

    return true;
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
      throw new \Exception('Error en cURL: ' . curl_error($ch));
    }

    curl_close($ch);
    return $response;
  }
}
