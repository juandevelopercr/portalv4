<?php

namespace App\Services\Hacienda\Login;

use App\Models\Token as TokenModel;

class TokenStorage
{
  /**
   * Guarda los tokens en la base de datos.
   *
   * @param string $issuer Emisor (clientId).
   * @param string $accessToken Token de acceso.
   * @param int    $accessTokenExpiresIn Tiempo de expiración del access_token en segundos.
   * @param string $refreshToken Token de refresco.
   * @param int    $refreshTokenExpiresIn Tiempo de expiración del refresh_token en segundos.
   */
  public function saveTokens($issuer, $accessToken, $accessTokenExpiresIn, $refreshToken, $refreshTokenExpiresIn)
  {
    TokenModel::updateOrCreate(
      ['issuer' => $issuer],
      [
        'access_token'          => $accessToken,
        'access_token_expires_at' => now()->addSeconds($accessTokenExpiresIn),
        'refresh_token'         => $refreshToken,
        'refresh_token_expires_at' => now()->addSeconds($refreshTokenExpiresIn),
      ]
    );
  }

  /**
   * Obtiene los tokens para un emisor específico.
   *
   * @param string $issuer Emisor (clientId).
   *
   * @return array|null
   */
  public function getTokens($issuer)
  {
    $token = TokenModel::where('issuer', $issuer)->first();
    return $token ? $token->toArray() : null;
  }

  /**
   * Verifica si el access_token para un emisor es válido.
   *
   * @param string $issuer Emisor (clientId).
   *
   * @return bool
   */
  public function isAccessTokenValid($issuer)
  {
    $tokenData = $this->getTokens($issuer);
    return $tokenData && now()->lt($tokenData['access_token_expires_at']);
  }

  /**
   * Verifica si el refresh_token para un emisor es válido.
   *
   * @param string $issuer Emisor (clientId).
   *
   * @return bool
   */
  public function isRefreshTokenValid($issuer)
  {
    $tokenData = $this->getTokens($issuer);
    return $tokenData && now()->lt($tokenData['refresh_token_expires_at']);
  }

  /**
   * Elimina los tokens para un emisor específico.
   *
   * @param string $issuer Emisor (clientId).
   */
  public function deleteTokens($issuer)
  {
    TokenModel::where('issuer', $issuer)->delete();
  }
}
