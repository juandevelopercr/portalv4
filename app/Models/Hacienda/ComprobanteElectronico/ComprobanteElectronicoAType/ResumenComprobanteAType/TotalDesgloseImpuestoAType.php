<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType;

use App\Models\TransactionLineTax;

/**
 * Class representing TotalDesgloseImpuestoAType
 */
class TotalDesgloseImpuestoAType
{
  /**
   * Indicará los códigos de impuesto registrados en las líneas de detalle.
   *
   * @var string $codigo
   */
  private $codigo = null;

  /**
   * @var string $codigoTarifaIVA
   */
  private $codigoTarifaIVA = null;

  /**
   * Se obtiene de la sumatoria del monto por código de impuesto cobrado en el comprobante electrónico
   *
   * @var float $totalMontoImpuesto
   */
  private $totalMontoImpuesto = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(TransactionLineTax $tax)
  {
    $this->setCodigo($tax->taxType->code);
    $this->setCodigoTarifaIVA($tax->taxRate->code);
    $this->setTotalMontoImpuesto($tax->tax_amount);
  }

  /**
   * Gets as codigo
   *
   * Indicará los códigos de impuesto registrados en las líneas de detalle.
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
   * Indicará los códigos de impuesto registrados en las líneas de detalle.
   *
   * @param string $codigo
   * @return self
   */
  public function setCodigo($codigo)
  {
    $this->codigo = $codigo;
    return $this;
  }

  /**
   * Gets as codigoTarifaIVA
   *
   * @return string
   */
  public function getCodigoTarifaIVA()
  {
    return $this->codigoTarifaIVA;
  }

  /**
   * Sets a new codigoTarifaIVA
   *
   * @param string $codigoTarifaIVA
   * @return self
   */
  public function setCodigoTarifaIVA($codigoTarifaIVA)
  {
    $this->codigoTarifaIVA = $codigoTarifaIVA;
    return $this;
  }

  /**
   * Gets as totalMontoImpuesto
   *
   * Se obtiene de la sumatoria del monto por código de impuesto cobrado en el comprobante electrónico
   *
   * @return float
   */
  public function getTotalMontoImpuesto()
  {
    return $this->totalMontoImpuesto;
  }

  /**
   * Sets a new totalMontoImpuesto
   *
   * Se obtiene de la sumatoria del monto por código de impuesto cobrado en el comprobante electrónico
   *
   * @param float $totalMontoImpuesto
   * @return self
   */
  public function setTotalMontoImpuesto($totalMontoImpuesto)
  {
    $this->totalMontoImpuesto = number_format($totalMontoImpuesto, 5, '.', '');
    return $this;
  }
}
