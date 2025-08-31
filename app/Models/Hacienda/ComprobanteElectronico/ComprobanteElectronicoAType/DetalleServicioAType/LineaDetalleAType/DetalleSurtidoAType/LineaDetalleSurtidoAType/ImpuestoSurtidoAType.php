<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType;

/**
 * Class representing ImpuestoSurtidoAType
 */
class ImpuestoSurtidoAType
{
  /**
   * Ver nota 8. Es un campo fijo de dos posiciones.
   * Al utilizar el código de Naturaleza del Descuento 01 correspondiente a "Regalías" o 03 de "Bonificaciones" y el código de impuesto 01, se debe utilizar para el cálculo del impuesto el campo denominado "Monto total componente de surtido" y la "Tarifa del Impuesto al Valor Agregado para componente de surtido"
   * Validación: Se verificará el cumplimiento de la nota 8
   *
   * @var string $codigoImpuestoSurtido
   */
  private $codigoImpuestoSurtido = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de "Otros" de la nota 8. Se debe describir puntualmente el impuesto utilizado.
   * Validación: Deberá contener mínimo 5 caracteres y un máximo de 100
   *
   * @var string $codigoImpuestoOTROSurtido
   */
  private $codigoImpuestoOTROSurtido = null;

  /**
   * Se convierte en obligatorio cuando se usa el código 01 de impuestos de surtido
   * Validación: Se verificará el cumplimiento de nota 8.1. cuando se utilice el código 01 de campo código del impuesto.
   *
   * @var string $codigoTarifaIVASurtido
   */
  private $codigoTarifaIVASurtido = null;

  /**
   * Este campo es de condición obligatoria, cuando el componente este gravado con alguna tarifa de impuesto, según corresponda. Debe de expresarse el porcentaje como número entero (Ejemplo: la tarifa del 13% se debe de reflejar como 13, la tarifa del 1% como 1, o bien la tarifa del 0.5% como 0.5)
   *
   * @var float $tarifaSurtido
   */
  private $tarifaSurtido = null;

  /**
   * Datos para Impuestos Específicos para componente de surtido
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType\DatosImpuestoEspecificoSurtidoAType $datosImpuestoEspecificoSurtido
   */
  private $datosImpuestoEspecificoSurtido = null;

  /**
   * Este campo será de condición obligatoria, cuando el componente este gravado con algún impuesto. Es un número decimal compuesto por 13 enteros y 5 decimales
   *
   * @var float $montoImpuestoSurtido
   */
  private $montoImpuestoSurtido = null;

  /**
   * Gets as codigoImpuestoSurtido
   *
   * Ver nota 8. Es un campo fijo de dos posiciones.
   * Al utilizar el código de Naturaleza del Descuento 01 correspondiente a "Regalías" o 03 de "Bonificaciones" y el código de impuesto 01, se debe utilizar para el cálculo del impuesto el campo denominado "Monto total componente de surtido" y la "Tarifa del Impuesto al Valor Agregado para componente de surtido"
   * Validación: Se verificará el cumplimiento de la nota 8
   *
   * @return string
   */
  public function getCodigoImpuestoSurtido()
  {
    return $this->codigoImpuestoSurtido;
  }

  /**
   * Sets a new codigoImpuestoSurtido
   *
   * Ver nota 8. Es un campo fijo de dos posiciones.
   * Al utilizar el código de Naturaleza del Descuento 01 correspondiente a "Regalías" o 03 de "Bonificaciones" y el código de impuesto 01, se debe utilizar para el cálculo del impuesto el campo denominado "Monto total componente de surtido" y la "Tarifa del Impuesto al Valor Agregado para componente de surtido"
   * Validación: Se verificará el cumplimiento de la nota 8
   *
   * @param string $codigoImpuestoSurtido
   * @return self
   */
  public function setCodigoImpuestoSurtido($codigoImpuestoSurtido)
  {
    $this->codigoImpuestoSurtido = $codigoImpuestoSurtido;
    return $this;
  }

  /**
   * Gets as codigoImpuestoOTROSurtido
   *
   * Será obligatorio en caso de utilizar el código 99 de "Otros" de la nota 8. Se debe describir puntualmente el impuesto utilizado.
   * Validación: Deberá contener mínimo 5 caracteres y un máximo de 100
   *
   * @return string
   */
  public function getCodigoImpuestoOTROSurtido()
  {
    return $this->codigoImpuestoOTROSurtido;
  }

  /**
   * Sets a new codigoImpuestoOTROSurtido
   *
   * Será obligatorio en caso de utilizar el código 99 de "Otros" de la nota 8. Se debe describir puntualmente el impuesto utilizado.
   * Validación: Deberá contener mínimo 5 caracteres y un máximo de 100
   *
   * @param string $codigoImpuestoOTROSurtido
   * @return self
   */
  public function setCodigoImpuestoOTROSurtido($codigoImpuestoOTROSurtido)
  {
    $this->codigoImpuestoOTROSurtido = $codigoImpuestoOTROSurtido;
    return $this;
  }

  /**
   * Gets as codigoTarifaIVASurtido
   *
   * Se convierte en obligatorio cuando se usa el código 01 de impuestos de surtido
   * Validación: Se verificará el cumplimiento de nota 8.1. cuando se utilice el código 01 de campo código del impuesto.
   *
   * @return string
   */
  public function getCodigoTarifaIVASurtido()
  {
    return $this->codigoTarifaIVASurtido;
  }

  /**
   * Sets a new codigoTarifaIVASurtido
   *
   * Se convierte en obligatorio cuando se usa el código 01 de impuestos de surtido
   * Validación: Se verificará el cumplimiento de nota 8.1. cuando se utilice el código 01 de campo código del impuesto.
   *
   * @param string $codigoTarifaIVASurtido
   * @return self
   */
  public function setCodigoTarifaIVASurtido($codigoTarifaIVASurtido)
  {
    $this->codigoTarifaIVASurtido = $codigoTarifaIVASurtido;
    return $this;
  }

  /**
   * Gets as tarifaSurtido
   *
   * Este campo es de condición obligatoria, cuando el componente este gravado con alguna tarifa de impuesto, según corresponda. Debe de expresarse el porcentaje como número entero (Ejemplo: la tarifa del 13% se debe de reflejar como 13, la tarifa del 1% como 1, o bien la tarifa del 0.5% como 0.5)
   *
   * @return float
   */
  public function getTarifaSurtido()
  {
    return $this->tarifaSurtido;
  }

  /**
   * Sets a new tarifaSurtido
   *
   * Este campo es de condición obligatoria, cuando el componente este gravado con alguna tarifa de impuesto, según corresponda. Debe de expresarse el porcentaje como número entero (Ejemplo: la tarifa del 13% se debe de reflejar como 13, la tarifa del 1% como 1, o bien la tarifa del 0.5% como 0.5)
   *
   * @param float $tarifaSurtido
   * @return self
   */
  public function setTarifaSurtido($tarifaSurtido)
  {
    $this->tarifaSurtido = $tarifaSurtido;
    return $this;
  }

  /**
   * Gets as datosImpuestoEspecificoSurtido
   *
   * Datos para Impuestos Específicos para componente de surtido
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType\DatosImpuestoEspecificoSurtidoAType
   */
  public function getDatosImpuestoEspecificoSurtido()
  {
    return $this->datosImpuestoEspecificoSurtido;
  }

  /**
   * Sets a new datosImpuestoEspecificoSurtido
   *
   * Datos para Impuestos Específicos para componente de surtido
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType\DatosImpuestoEspecificoSurtidoAType $datosImpuestoEspecificoSurtido
   * @return self
   */
  public function setDatosImpuestoEspecificoSurtido(?\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType\DatosImpuestoEspecificoSurtidoAType $datosImpuestoEspecificoSurtido = null)
  {
    $this->datosImpuestoEspecificoSurtido = $datosImpuestoEspecificoSurtido;
    return $this;
  }

  /**
   * Gets as montoImpuestoSurtido
   *
   * Este campo será de condición obligatoria, cuando el componente este gravado con algún impuesto. Es un número decimal compuesto por 13 enteros y 5 decimales
   *
   * @return float
   */
  public function getMontoImpuestoSurtido()
  {
    return $this->montoImpuestoSurtido;
  }

  /**
   * Sets a new montoImpuestoSurtido
   *
   * Este campo será de condición obligatoria, cuando el componente este gravado con algún impuesto. Es un número decimal compuesto por 13 enteros y 5 decimales
   *
   * @param float $montoImpuestoSurtido
   * @return self
   */
  public function setMontoImpuestoSurtido($montoImpuestoSurtido)
  {
    $this->montoImpuestoSurtido = $montoImpuestoSurtido;
    return $this;
  }
}
