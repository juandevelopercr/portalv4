<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType;

/**
 * Class representing DescuentoSurtidoAType
 */
class DescuentoSurtidoAType
{
  /**
   * Validación: Se deberá incluir un valor igual o menor al del
   * "Monto total componente surtido"
   *
   * @var float $montoDescuentoSurtido
   */
  private $montoDescuentoSurtido = null;

  /**
   * Este campo será de condición obligatoria, cuando se incluya información en el campo "Monto de descuentos concedidos al componente de surtido
   *
   * @var string $codigoDescuentoSurtido
   */
  private $codigoDescuentoSurtido = null;

  /**
   * Este campo será de condición obligatoria, cuando se utilice el código 99 de la Nota 20
   * Validación: En caso de utilizarse el código 99, se verificará que este campo se encuentre en el comprobante, caso contrario se rechazará. Además, deberá contener mínimo 3 caracteres y un máximo de 80
   *
   * @var string $descuentoSurtidoOtros
   */
  private $descuentoSurtidoOtros = null;

  /**
   * Gets as montoDescuentoSurtido
   *
   * Validación: Se deberá incluir un valor igual o menor al del
   * "Monto total componente surtido"
   *
   * @return float
   */
  public function getMontoDescuentoSurtido()
  {
    return $this->montoDescuentoSurtido;
  }

  /**
   * Sets a new montoDescuentoSurtido
   *
   * Validación: Se deberá incluir un valor igual o menor al del
   * "Monto total componente surtido"
   *
   * @param float $montoDescuentoSurtido
   * @return self
   */
  public function setMontoDescuentoSurtido($montoDescuentoSurtido)
  {
    $this->montoDescuentoSurtido = $montoDescuentoSurtido;
    return $this;
  }

  /**
   * Gets as codigoDescuentoSurtido
   *
   * Este campo será de condición obligatoria, cuando se incluya información en el campo "Monto de descuentos concedidos al componente de surtido
   *
   * @return string
   */
  public function getCodigoDescuentoSurtido()
  {
    return $this->codigoDescuentoSurtido;
  }

  /**
   * Sets a new codigoDescuentoSurtido
   *
   * Este campo será de condición obligatoria, cuando se incluya información en el campo "Monto de descuentos concedidos al componente de surtido
   *
   * @param string $codigoDescuentoSurtido
   * @return self
   */
  public function setCodigoDescuentoSurtido($codigoDescuentoSurtido)
  {
    $this->codigoDescuentoSurtido = $codigoDescuentoSurtido;
    return $this;
  }

  /**
   * Gets as descuentoSurtidoOtros
   *
   * Este campo será de condición obligatoria, cuando se utilice el código 99 de la Nota 20
   * Validación: En caso de utilizarse el código 99, se verificará que este campo se encuentre en el comprobante, caso contrario se rechazará. Además, deberá contener mínimo 3 caracteres y un máximo de 80
   *
   * @return string
   */
  public function getDescuentoSurtidoOtros()
  {
    return $this->descuentoSurtidoOtros;
  }

  /**
   * Sets a new descuentoSurtidoOtros
   *
   * Este campo será de condición obligatoria, cuando se utilice el código 99 de la Nota 20
   * Validación: En caso de utilizarse el código 99, se verificará que este campo se encuentre en el comprobante, caso contrario se rechazará. Además, deberá contener mínimo 3 caracteres y un máximo de 80
   *
   * @param string $descuentoSurtidoOtros
   * @return self
   */
  public function setDescuentoSurtidoOtros($descuentoSurtidoOtros)
  {
    $this->descuentoSurtidoOtros = $descuentoSurtidoOtros;
    return $this;
  }
}
