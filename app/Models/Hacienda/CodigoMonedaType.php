<?php

namespace App\Models\Hacienda;

use App\Models\Transaction;

/**
 * Class representing CodigoMonedaType
 *
 *
 * Hacienda Type: CodigoMonedaType
 */
class CodigoMonedaType
{
  /**
   * Código de la moneda
   *
   * @var string $codigoMoneda
   */
  private $codigoMoneda = null;

  /**
   * Tipo de cambio
   *
   * @var float $tipoCambio
   */
  private $tipoCambio = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(Transaction $transaction)
  {
    $this->setCodigoMoneda($transaction->currency->code);
    if ($transaction->currency->code == 'CRC')
      $this->setTipoCambio(1);
    else
      $this->setTipoCambio($transaction->factura_change_type);
  }

  /**
   * Gets as codigoMoneda
   *
   * Código de la moneda
   *
   * @return string
   */
  public function getCodigoMoneda()
  {
    return $this->codigoMoneda;
  }

  /**
   * Sets a new codigoMoneda
   *
   * Código de la moneda
   *
   * @param string $codigoMoneda
   * @return self
   */
  public function setCodigoMoneda($codigoMoneda)
  {
    $this->codigoMoneda = $codigoMoneda;
    return $this;
  }

  /**
   * Gets as tipoCambio
   *
   * Tipo de cambio
   *
   * @return float
   */
  public function getTipoCambio()
  {
    return $this->tipoCambio;
  }

  /**
   * Sets a new tipoCambio
   *
   * Tipo de cambio
   *
   * @param float $tipoCambio
   * @return self
   */
  public function setTipoCambio($tipoCambio)
  {
    $this->tipoCambio = $tipoCambio;
    return $this;
  }
}
