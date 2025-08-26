<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType;

/**
 * Class representing DetalleSurtidoAType
 */
class DetalleSurtidoAType
{
  /**
   * Tipo complejo que representa cada línea del detalle del surtido
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType[] $lineaDetalleSurtido
   */
  private $lineaDetalleSurtido = [];

  /**
   * Adds as lineaDetalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle del surtido
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType $lineaDetalleSurtido
   */
  public function addToLineaDetalleSurtido(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType $lineaDetalleSurtido)
  {
    $this->lineaDetalleSurtido[] = $lineaDetalleSurtido;
    return $this;
  }

  /**
   * isset lineaDetalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle del surtido
   *
   * @param int|string $index
   * @return bool
   */
  public function issetLineaDetalleSurtido($index)
  {
    return isset($this->lineaDetalleSurtido[$index]);
  }

  /**
   * unset lineaDetalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle del surtido
   *
   * @param int|string $index
   * @return void
   */
  public function unsetLineaDetalleSurtido($index)
  {
    unset($this->lineaDetalleSurtido[$index]);
  }

  /**
   * Gets as lineaDetalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle del surtido
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType[]
   */
  public function getLineaDetalleSurtido()
  {
    return $this->lineaDetalleSurtido;
  }

  /**
   * Sets a new lineaDetalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle del surtido
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType[] $lineaDetalleSurtido
   * @return self
   */
  public function setLineaDetalleSurtido(array $lineaDetalleSurtido)
  {
    $this->lineaDetalleSurtido = $lineaDetalleSurtido;
    return $this;
  }
}
