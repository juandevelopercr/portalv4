<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType;

/**
 * Class representing CodigoComercialSurtidoAType
 */
class CodigoComercialSurtidoAType
{
  /**
   * @var string $tipoSurtido
   */
  private $tipoSurtido = null;

  /**
   * @var string $codigoSurtido
   */
  private $codigoSurtido = null;

  /**
   * Gets as tipoSurtido
   *
   * @return string
   */
  public function getTipoSurtido()
  {
    return $this->tipoSurtido;
  }

  /**
   * Sets a new tipoSurtido
   *
   * @param string $tipoSurtido
   * @return self
   */
  public function setTipoSurtido($tipoSurtido)
  {
    $this->tipoSurtido = $tipoSurtido;
    return $this;
  }

  /**
   * Gets as codigoSurtido
   *
   * @return string
   */
  public function getCodigoSurtido()
  {
    return $this->codigoSurtido;
  }

  /**
   * Sets a new codigoSurtido
   *
   * @param string $codigoSurtido
   * @return self
   */
  public function setCodigoSurtido($codigoSurtido)
  {
    $this->codigoSurtido = $codigoSurtido;
    return $this;
  }
}
