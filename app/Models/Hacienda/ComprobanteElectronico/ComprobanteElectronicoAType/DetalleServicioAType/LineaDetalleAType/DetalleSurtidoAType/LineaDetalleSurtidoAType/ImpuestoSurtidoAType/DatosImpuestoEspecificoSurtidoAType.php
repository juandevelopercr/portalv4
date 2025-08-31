<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType;

/**
 * Class representing DatosImpuestoEspecificoSurtidoAType
 */
class DatosImpuestoEspecificoSurtidoAType
{
  /**
   * Cantidad de la unidad de medida a utilizar para componente de surtido. Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 04, 05 y 06 de la nota 8
   *
   * @var float $cantidadUnidadMedidaSurtido
   */
  private $cantidadUnidadMedidaSurtido = null;

  /**
   * Porcentaje en componente de surtido. Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8. Debe de expresarse el porcentaje como número entero (Ejemplo: la tarifa del 13% se debe de reflejar como 13, la tarifa del 1% como 1, o bien la tarifa del 0.5% como 0.5)
   *
   * @var float $porcentajeSurtido
   */
  private $porcentajeSurtido = null;

  /**
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8
   * Este campo se obtiene de multiplicar la "Cantidad de la unidad de medida a utilizar" por el "Porcentaje"
   *
   * @var float $proporcionSurtido
   */
  private $proporcionSurtido = null;

  /**
   * Volumen por Unidad de Consumo componente de surtido. Este campo es de condición obligatoria, cuando se utilice el código de impuesto 05 de la nota 8
   *
   * @var float $volumenUnidadConsumoSurtido
   */
  private $volumenUnidadConsumoSurtido = null;

  /**
   * Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 04, 05 y 06 de la nota 8
   *
   * @var float $impuestoUnidadSurtido
   */
  private $impuestoUnidadSurtido = null;

  /**
   * Gets as cantidadUnidadMedidaSurtido
   *
   * Cantidad de la unidad de medida a utilizar para componente de surtido. Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 04, 05 y 06 de la nota 8
   *
   * @return float
   */
  public function getCantidadUnidadMedidaSurtido()
  {
    return $this->cantidadUnidadMedidaSurtido;
  }

  /**
   * Sets a new cantidadUnidadMedidaSurtido
   *
   * Cantidad de la unidad de medida a utilizar para componente de surtido. Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 04, 05 y 06 de la nota 8
   *
   * @param float $cantidadUnidadMedidaSurtido
   * @return self
   */
  public function setCantidadUnidadMedidaSurtido($cantidadUnidadMedidaSurtido)
  {
    $this->cantidadUnidadMedidaSurtido = $cantidadUnidadMedidaSurtido;
    return $this;
  }

  /**
   * Gets as porcentajeSurtido
   *
   * Porcentaje en componente de surtido. Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8. Debe de expresarse el porcentaje como número entero (Ejemplo: la tarifa del 13% se debe de reflejar como 13, la tarifa del 1% como 1, o bien la tarifa del 0.5% como 0.5)
   *
   * @return float
   */
  public function getPorcentajeSurtido()
  {
    return $this->porcentajeSurtido;
  }

  /**
   * Sets a new porcentajeSurtido
   *
   * Porcentaje en componente de surtido. Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8. Debe de expresarse el porcentaje como número entero (Ejemplo: la tarifa del 13% se debe de reflejar como 13, la tarifa del 1% como 1, o bien la tarifa del 0.5% como 0.5)
   *
   * @param float $porcentajeSurtido
   * @return self
   */
  public function setPorcentajeSurtido($porcentajeSurtido)
  {
    $this->porcentajeSurtido = $porcentajeSurtido;
    return $this;
  }

  /**
   * Gets as proporcionSurtido
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8
   * Este campo se obtiene de multiplicar la "Cantidad de la unidad de medida a utilizar" por el "Porcentaje"
   *
   * @return float
   */
  public function getProporcionSurtido()
  {
    return $this->proporcionSurtido;
  }

  /**
   * Sets a new proporcionSurtido
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8
   * Este campo se obtiene de multiplicar la "Cantidad de la unidad de medida a utilizar" por el "Porcentaje"
   *
   * @param float $proporcionSurtido
   * @return self
   */
  public function setProporcionSurtido($proporcionSurtido)
  {
    $this->proporcionSurtido = $proporcionSurtido;
    return $this;
  }

  /**
   * Gets as volumenUnidadConsumoSurtido
   *
   * Volumen por Unidad de Consumo componente de surtido. Este campo es de condición obligatoria, cuando se utilice el código de impuesto 05 de la nota 8
   *
   * @return float
   */
  public function getVolumenUnidadConsumoSurtido()
  {
    return $this->volumenUnidadConsumoSurtido;
  }

  /**
   * Sets a new volumenUnidadConsumoSurtido
   *
   * Volumen por Unidad de Consumo componente de surtido. Este campo es de condición obligatoria, cuando se utilice el código de impuesto 05 de la nota 8
   *
   * @param float $volumenUnidadConsumoSurtido
   * @return self
   */
  public function setVolumenUnidadConsumoSurtido($volumenUnidadConsumoSurtido)
  {
    $this->volumenUnidadConsumoSurtido = $volumenUnidadConsumoSurtido;
    return $this;
  }

  /**
   * Gets as impuestoUnidadSurtido
   *
   * Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 04, 05 y 06 de la nota 8
   *
   * @return float
   */
  public function getImpuestoUnidadSurtido()
  {
    return $this->impuestoUnidadSurtido;
  }

  /**
   * Sets a new impuestoUnidadSurtido
   *
   * Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 04, 05 y 06 de la nota 8
   *
   * @param float $impuestoUnidadSurtido
   * @return self
   */
  public function setImpuestoUnidadSurtido($impuestoUnidadSurtido)
  {
    $this->impuestoUnidadSurtido = $impuestoUnidadSurtido;
    return $this;
  }
}
