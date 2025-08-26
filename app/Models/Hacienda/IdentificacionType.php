<?php

namespace App\Models\Hacienda;

/**
 * Class representing IdentificacionType
 *
 *
 * Hacienda Type: IdentificacionType
 */
class IdentificacionType
{
  const EXTRANJERO = '05';

  /**
   * Tipo de identificación: 01 Cédula Física, 02 Cédula Jurídica, 03 DIMEX, 04 NITE, 05 Extranjero No Domiciliado, 06 No Contribuyente
   *
   * @var string $tipo
   */
  private $tipo = null;

  /**
   * Número de identificación, el contribuyente debe estar inscrito ante la Administración Tributaria
   *
   * @var string $numero
   */
  private $numero = null;

  /**
   * Gets as tipo
   *
   * Tipo de identificación: 01 Cédula Física, 02 Cédula Jurídica, 03 DIMEX, 04 NITE, 05 Extranjero No Domiciliado, 06 No Contribuyente
   *
   * @return string
   */
  public function getTipo()
  {
    return $this->tipo;
  }

  /**
   * Sets a new tipo
   *
   * Tipo de identificación: 01 Cédula Física, 02 Cédula Jurídica, 03 DIMEX, 04 NITE, 05 Extranjero No Domiciliado, 06 No Contribuyente
   *
   * @param string $tipo
   * @return self
   */
  public function setTipo($tipo)
  {
    $this->tipo = $tipo;
    return $this;
  }

  /**
   * Gets as numero
   *
   * Número de identificación, el contribuyente debe estar inscrito ante la Administración Tributaria
   *
   * @return string
   */
  public function getNumero()
  {
    return $this->numero;
  }

  /**
   * Sets a new numero
   *
   * Número de identificación, el contribuyente debe estar inscrito ante la Administración Tributaria
   *
   * @param string $numero
   * @return self
   */
  public function setNumero($numero)
  {
    $this->numero = $numero;
    return $this;
  }
}
