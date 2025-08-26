<?php

namespace App\Services\Hacienda\Login;

class AuthService
{
  protected $token;

  public function __construct()
  {
    $this->token = new Token(); // Por defecto usa el entorno de staging
  }

  /**
   * Obtiene el token de autenticación.
   *
   * @param string $issuerId
   * @param string $username
   * @param string $password
   *
   * @return string
   * @throws Exception
   */
  public function getToken($username, $password)
  {
    return $this->token->getToken($username, $password);
  }

  /**
   * Cierra la sesión.
   *
   * @param string $issuerId
   * @param string $refreshToken
   *
   * @return bool
   * @throws Exception
   */
  public function closeSession($issuerId, $refreshToken)
  {
    return $this->token->closeSession($issuerId, $refreshToken);
  }
}
