<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType;

/**
 * Class representing OtroContenidoAType
 */
class OtroContenidoAType
{
  /**
   * @var string $__value
   */
  private $__value = null;

  /**
   * Código opcional para facilitar la identificación del elemento.
   *
   * @var string $codigo
   */
  private $codigo = null;

  /**
   * Construct
   *
   * @param string $value
   */
  public function __construct($value)
  {
    $this->value($value);
  }

  /**
   * Gets or sets the inner value
   *
   * @param string $value
   * @return string
   */
  public function value()
  {
    if ($args = func_get_args()) {
      $this->__value = $args[0];
    }
    return $this->__value;
  }

  /**
   * Gets a string value
   *
   * @return string
   */
  public function __toString()
  {
    return strval($this->__value);
  }

  /**
   * Gets as codigo
   *
   * Código opcional para facilitar la identificación del elemento.
   *
   * @return string
   */
  public function getCodigo()
  {
    return $this->codigo;
  }

  /**
   * Sets a new codigo
   *
   * Código opcional para facilitar la identificación del elemento.
   *
   * @param string $codigo
   * @return self
   */
  public function setCodigo($codigo)
  {
    $this->codigo = $codigo;
    return $this;
  }
}
