<?php

namespace App\Models\Hacienda;

/**
 * Class representing TelefonoType
 *
 *
 * Hacienda Type: TelefonoType
 */
class TelefonoType
{
  /**
   * Código del país
   *
   * @var int $codigoPais
   */
  private $codigoPais = null;

  /**
   * Número de teléfono
   *
   * @var int $numTelefono
   */
  private $numTelefono = null;

  /**
   * Gets as codigoPais
   *
   * Código del país
   *
   * @return int
   */
  public function getCodigoPais()
  {
    return $this->codigoPais;
  }

  /**
   * Sets a new codigoPais
   *
   * Código del país
   *
   * @param int $codigoPais
   * @return self
   */
  public function setCodigoPais($codigoPais)
  {
    $this->codigoPais = $codigoPais;
    return $this;
  }

  /**
   * Gets as numTelefono
   *
   * Número de teléfono
   *
   * @return int
   */
  public function getNumTelefono()
  {
    return $this->numTelefono;
  }

  /**
   * Sets a new numTelefono
   *
   * Número de teléfono
   *
   * @param int $numTelefono
   * @return self
   */
  public function setNumTelefono($numTelefono)
  {
    $this->numTelefono = $numTelefono;
    return $this;
  }
}
