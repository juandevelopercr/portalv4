<?php

namespace App\Models\Hacienda\ComprobanteElectronico;

use App\Models\TransactionLineDiscount;

/**
 * Class representing DescuentoType
 *
 *
 * Hacienda Type: DescuentoType
 */
class DescuentoType
{
  /**
   * Monto de descuento concedido. Este campo será de condición obligatoria, cuando se indique
   * un descuento, en el campo "Código del descuento"
   *
   * @var float $montoDescuento
   */
  private $montoDescuento = null;

  /**
   * Este campo será de condición obligatoria, cuando se incluya
   * información en el campo "monto de descuentos concedidos"
   *
   * @var string $codigoDescuento
   */
  private $codigoDescuento = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de "Otros"
   * de la nota 20. Se debe describir puntualmente el descuento
   * utilizado
   *
   * @var string $codigoDescuentoOTRO
   */
  private $codigoDescuentoOTRO = null;

  /**
   * Naturaleza del descuento, que es obligatorio si existe descuento
   *
   * @var string $naturalezaDescuento
   */
  private $naturalezaDescuento = null;

  /**
   * Constructor para inicializar el detalle.
   */
  public function __construct(TransactionLineDiscount $discount)
  {
    $this->setMontoDescuento($discount->discount_amount);
    $this->setCodigoDescuento($discount->discountType->code);
    if (!is_null($discount->discount_type_other) && !empty($discount->discount_type_other))
      $this->setCodigoDescuentoOTRO($discount->discount_type_other);

    if (!is_null($discount->nature_discount) && !empty($discount->nature_discount))
      $this->setNaturalezaDescuento($discount->nature_discount);
  }
  /**
   * Gets as montoDescuento
   *
   * Monto de descuento concedido. Este campo será de condición obligatoria, cuando se indique
   * un descuento, en el campo "Código del descuento"
   *
   * @return float
   */
  public function getMontoDescuento()
  {
    return $this->montoDescuento;
  }

  /**
   * Sets a new montoDescuento
   *
   * Monto de descuento concedido. Este campo será de condición obligatoria, cuando se indique
   * un descuento, en el campo "Código del descuento"
   *
   * @param float $montoDescuento
   * @return self
   */
  public function setMontoDescuento($montoDescuento)
  {
    $this->montoDescuento = $montoDescuento;
    return $this;
  }

  /**
   * Gets as codigoDescuento
   *
   * Este campo será de condición obligatoria, cuando se incluya
   * información en el campo "monto de descuentos concedidos"
   *
   * @return string
   */
  public function getCodigoDescuento()
  {
    return $this->codigoDescuento;
  }

  /**
   * Sets a new codigoDescuento
   *
   * Este campo será de condición obligatoria, cuando se incluya
   * información en el campo "monto de descuentos concedidos"
   *
   * @param string $codigoDescuento
   * @return self
   */
  public function setCodigoDescuento($codigoDescuento)
  {
    $this->codigoDescuento = $codigoDescuento;
    return $this;
  }

  /**
   * Gets as codigoDescuentoOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de "Otros"
   * de la nota 20. Se debe describir puntualmente el descuento
   * utilizado
   *
   * @return string
   */
  public function getCodigoDescuentoOTRO()
  {
    return $this->codigoDescuentoOTRO;
  }

  /**
   * Sets a new codigoDescuentoOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de "Otros"
   * de la nota 20. Se debe describir puntualmente el descuento
   * utilizado
   *
   * @param string $codigoDescuentoOTRO
   * @return self
   */
  public function setCodigoDescuentoOTRO($codigoDescuentoOTRO)
  {
    $this->codigoDescuentoOTRO = $codigoDescuentoOTRO;
    return $this;
  }

  /**
   * Gets as naturalezaDescuento
   *
   * Naturaleza del descuento, que es obligatorio si existe descuento
   *
   * @return string
   */
  public function getNaturalezaDescuento()
  {
    return $this->naturalezaDescuento;
  }

  /**
   * Sets a new naturalezaDescuento
   *
   * Naturaleza del descuento, que es obligatorio si existe descuento
   *
   * @param string $naturalezaDescuento
   * @return self
   */
  public function setNaturalezaDescuento($naturalezaDescuento)
  {
    $this->naturalezaDescuento = $naturalezaDescuento;
    return $this;
  }
}
