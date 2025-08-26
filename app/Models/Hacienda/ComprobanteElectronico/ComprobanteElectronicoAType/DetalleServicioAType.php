<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType;

use App\Models\TransactionLine;
use App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class representing DetalleServicioAType
 */
class DetalleServicioAType
{
  /**
   * Cada línea del detalle de la mercancia o servicio prestado.
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType[] $lineaDetalle
   */
  private $lineaDetalle = [];

  /**
   * Constructor para inicializar el detalle.
   */
  public function __construct(Collection $lines)
  {
    foreach ($lines as $index => $line) {
      $lineaDetalle = new LineaDetalleAType($line, $index + 1);
      $this->addToLineaDetalle($lineaDetalle);
    }
  }

  /**
   * Adds as lineaDetalle
   *
   * Cada línea del detalle de la mercancia o servicio prestado.
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType $lineaDetalle
   */
  public function addToLineaDetalle(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType $lineaDetalle)
  {
    $this->lineaDetalle[] = $lineaDetalle;
    return $this;
  }

  /**
   * isset lineaDetalle
   *
   * Cada línea del detalle de la mercancia o servicio prestado.
   *
   * @param int|string $index
   * @return bool
   */
  public function issetLineaDetalle($index)
  {
    return isset($this->lineaDetalle[$index]);
  }

  /**
   * unset lineaDetalle
   *
   * Cada línea del detalle de la mercancia o servicio prestado.
   *
   * @param int|string $index
   * @return void
   */
  public function unsetLineaDetalle($index)
  {
    unset($this->lineaDetalle[$index]);
  }

  /**
   * Gets as lineaDetalle
   *
   * Cada línea del detalle de la mercancia o servicio prestado.
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType[]
   */
  public function getLineaDetalle()
  {
    return $this->lineaDetalle;
  }

  /**
   * Sets a new lineaDetalle
   *
   * Cada línea del detalle de la mercancia o servicio prestado.
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType[] $lineaDetalle
   * @return self
   */
  public function setLineaDetalle(array $lineaDetalle)
  {
    $this->lineaDetalle = $lineaDetalle;
    return $this;
  }
}
