<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType;

/**
 * Class representing OtrosAType
 */
class OtrosAType
{
  /**
   * Elemento opcional que se puede utilizar para almacenar texto.
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroTextoAType[] $otroTexto
   */
  private $otroTexto = [];

  /**
   * Elemento opcional que se puede utilizar para almacenar contenido estructurado.
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroContenidoAType[] $otroContenido
   */
  private $otroContenido = [];

  /**
   * Adds as otroTexto
   *
   * Elemento opcional que se puede utilizar para almacenar texto.
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroTextoAType $otroTexto
   */
  public function addToOtroTexto(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroTextoAType $otroTexto)
  {
    $this->otroTexto[] = $otroTexto;
    return $this;
  }

  /**
   * isset otroTexto
   *
   * Elemento opcional que se puede utilizar para almacenar texto.
   *
   * @param int|string $index
   * @return bool
   */
  public function issetOtroTexto($index)
  {
    return isset($this->otroTexto[$index]);
  }

  /**
   * unset otroTexto
   *
   * Elemento opcional que se puede utilizar para almacenar texto.
   *
   * @param int|string $index
   * @return void
   */
  public function unsetOtroTexto($index)
  {
    unset($this->otroTexto[$index]);
  }

  /**
   * Gets as otroTexto
   *
   * Elemento opcional que se puede utilizar para almacenar texto.
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroTextoAType[]
   */
  public function getOtroTexto()
  {
    return $this->otroTexto;
  }

  /**
   * Sets a new otroTexto
   *
   * Elemento opcional que se puede utilizar para almacenar texto.
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroTextoAType[] $otroTexto
   * @return self
   */
  public function setOtroTexto(array $otroTexto = null)
  {
    $this->otroTexto = $otroTexto;
    return $this;
  }

  /**
   * Adds as otroContenido
   *
   * Elemento opcional que se puede utilizar para almacenar contenido estructurado.
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroContenidoAType $otroContenido
   */
  public function addToOtroContenido(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroContenidoAType $otroContenido)
  {
    $this->otroContenido[] = $otroContenido;
    return $this;
  }

  /**
   * isset otroContenido
   *
   * Elemento opcional que se puede utilizar para almacenar contenido estructurado.
   *
   * @param int|string $index
   * @return bool
   */
  public function issetOtroContenido($index)
  {
    return isset($this->otroContenido[$index]);
  }

  /**
   * unset otroContenido
   *
   * Elemento opcional que se puede utilizar para almacenar contenido estructurado.
   *
   * @param int|string $index
   * @return void
   */
  public function unsetOtroContenido($index)
  {
    unset($this->otroContenido[$index]);
  }

  /**
   * Gets as otroContenido
   *
   * Elemento opcional que se puede utilizar para almacenar contenido estructurado.
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroContenidoAType[]
   */
  public function getOtroContenido()
  {
    return $this->otroContenido;
  }

  /**
   * Sets a new otroContenido
   *
   * Elemento opcional que se puede utilizar para almacenar contenido estructurado.
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType\OtroContenidoAType[] $otroContenido
   * @return self
   */
  public function setOtroContenido(array $otroContenido = null)
  {
    $this->otroContenido = $otroContenido;
    return $this;
  }
}
