<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType;

use App\Models\Transaction;
use App\Models\Hacienda\CodigoMonedaType;
use App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\MedioPagoAType;
use App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\TotalDesgloseImpuestoAType;

/**
 * Class representing ResumenComprobanteAType
 */
class ResumenComprobanteAType
{
  /**
   * @var \App\Models\Hacienda\CodigoMonedaType $codigoTipoMoneda
   */
  private $codigoTipoMoneda = null;

  /**
   * Total de los servicios gravados con IVA
   *
   * @var float $totalServGravados
   */
  private $totalServGravados = null;

  /**
   * Total de los servicios exentos de IVA
   *
   * @var float $totalServExentos
   */
  private $totalServExentos = null;

  /**
   * Total servicios exonerados del IVA
   *
   * @var float $totalServExonerado
   */
  private $totalServExonerado = null;

  /**
   * Este campo será de condición obligatoria, cuando se seleccionen códigos CAByS que correspondan a un servicio y el servicio sea No Sujeto de IVA
   *
   * @var float $totalServNoSujeto
   */
  private $totalServNoSujeto = null;

  /**
   * Total mercancias gravadas con IVA
   *
   * @var float $totalMercGravadas
   */
  private $totalMercGravadas = null;

  /**
   * Total mercancias exentas de IVA
   *
   * @var float $totalMercExentas
   */
  private $totalMercExentas = null;

  /**
   * Total mercancías exoneradas del IVA
   *
   * @var float $totalMercExonerada
   */
  private $totalMercExonerada = null;

  /**
   * Este campo será de condición obligatoria, cuando se seleccionen códigos CAByS que correspondan a una mercancía y la mercancía sea No Sujeta de IVA
   *
   * @var float $totalMercNoSujeta
   */
  private $totalMercNoSujeta = null;

  /**
   * Total gravado. se obtiene de la suma del total servicios gravados con IV + total mercancias gravadas con IV
   *
   * @var float $totalGravado
   */
  private $totalGravado = null;

  /**
   * Total Exento, se obtiene de la suma de los campos total servicios exentos IV mas total mercancias exentas IV
   *
   * @var float $totalExento
   */
  private $totalExento = null;

  /**
   * Se obtiene de la suma de los campos "total servicios exonerados de IVA" mas "total de mercancías exoneradas del IVA".
   *
   * @var float $totalExonerado
   */
  private $totalExonerado = null;

  /**
   * Se obtiene de la suma de los campos "Total servicios No Sujetos de IVA" mas "Total mercancías No Sujetas de IVA".
   *
   * @var float $totalNoSujeto
   */
  private $totalNoSujeto = null;

  /**
   * Se obtiene de la sumatoria de los campos “total gravado”, “total exento”, “Total Exonerado” y “Total No Sujeto
   *
   * @var float $totalVenta
   */
  private $totalVenta = null;

  /**
   * Se obtiene de la suma de todos los campo de monto de descuento concedido
   *
   * @var float $totalDescuentos
   */
  private $totalDescuentos = null;

  /**
   * Se obtiene de la resta de los campos total venta menos total descuento
   *
   * @var float $totalVentaNeta
   */
  private $totalVentaNeta = null;

  /**
   * Tipo complejo que contiene los montos desglosados por impuesto cobrado en el comprobante electrónico.
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\TotalDesgloseImpuestoAType[] $totalDesgloseImpuesto
   */
  private $totalDesgloseImpuesto = [];

  /**
   * Se obtiene de la suma de todos campos monto del impuesto
   *
   * @var float $totalImpuesto
   */
  private $totalImpuesto = null;

  /**
   * Este campo es de condición obligatoria, cuando existen producto/servicio gravados con algún impuesto en las líneas de detalle que sean asumidos por el emisor
   *
   * @var float $totalImpAsumEmisorFabrica
   */
  private $totalImpAsumEmisorFabrica = null;

  /**
   * IVA Devuelto
   *
   * @var float $totalIVADevuelto
   */
  private $totalIVADevuelto = null;

  /**
   * Total Otros Cargos
   *
   * @var float $totalOtrosCargos
   */
  private $totalOtrosCargos = null;

  /**
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\MedioPagoAType[] $medioPago
   */
  private $medioPago = [];

  /**
   * Se obtiene de la suma de los campos "total venta neta", "monto total del impuesto" y "total otros cargos" menos "total IVA devuelto", en caso de contar con dichos campos.
   *
   * @var float $totalComprobante
   */
  private $totalComprobante = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(Transaction $transaction)
  {
    $tipoMoneda = new CodigoMonedaType($transaction);

    $this->setCodigoTipoMoneda($tipoMoneda);
    $this->setTotalServGravados($transaction->totalServGravados);
    $this->setTotalServExentos($transaction->totalServExentos);
    $this->setTotalServExonerado($transaction->totalServExonerado);
    $this->setTotalServNoSujeto($transaction->totalServNoSujeto);

    $this->setTotalMercGravadas($transaction->totalMercGravadas);
    $this->setTotalMercExentas($transaction->totalMercExentas);
    $this->setTotalMercExonerada($transaction->totalMercExonerada);
    $this->setTotalMercNoSujeta($transaction->totalMercNoSujeta);

    $this->setTotalGravado($transaction->totalGravado);
    $this->setTotalExento($transaction->totalExento);
    $this->setTotalExonerado($transaction->totalExonerado);
    $this->setTotalNoSujeto($transaction->totalNoSujeto);

    $this->setTotalVenta($transaction->totalVenta);
    $this->setTotalDescuentos($transaction->totalDiscount);
    $this->setTotalVentaNeta($transaction->totalVentaNeta);

    // Adicionar los impuestos
    if (!empty($transaction->lines)) {
      foreach ($transaction->lines as $line) {
        foreach ($line->taxes as $tax) {
          $totalDesgloseImpuesto = new TotalDesgloseImpuestoAType($tax);
          $this->addToTotalDesgloseImpuesto($totalDesgloseImpuesto, $line);
        }
      }
    }

    $this->setTotalImpuesto($transaction->totalImpuesto);
    if ($transaction->document_type != 'FEC' && $transaction->document_type != 'PRC') {
      $this->setTotalImpAsumEmisorFabrica($transaction->totalImpAsumEmisorFabrica);
      $this->setTotalIVADevuelto($transaction->totalIVADevuelto);
    }
    $this->setTotalOtrosCargos($transaction->totalOtrosCargos);

    // Adicionar los impuestos
    if (!empty($transaction->payments)) {
      foreach ($transaction->payments as $payment) {
        $medioPago  = new MedioPagoAType($payment);
        $this->addToMedioPago($medioPago);
      }
    }

    $this->setTotalComprobante($transaction->totalComprobante);
  }

  /**
   * Gets as codigoTipoMoneda
   *
   * @return \App\Models\Hacienda\CodigoMonedaType
   */
  public function getCodigoTipoMoneda()
  {
    return $this->codigoTipoMoneda;
  }

  /**
   * Sets a new codigoTipoMoneda
   *
   * @param \App\Models\Hacienda\CodigoMonedaType $codigoTipoMoneda
   * @return self
   */
  public function setCodigoTipoMoneda(\App\Models\Hacienda\CodigoMonedaType $codigoTipoMoneda)
  {
    $this->codigoTipoMoneda = $codigoTipoMoneda;
    return $this;
  }

  /**
   * Gets as totalServGravados
   *
   * Total de los servicios gravados con IVA
   *
   * @return float
   */
  public function getTotalServGravados()
  {
    return $this->totalServGravados;
  }

  /**
   * Sets a new totalServGravados
   *
   * Total de los servicios gravados con IVA
   *
   * @param float $totalServGravados
   * @return self
   */
  public function setTotalServGravados($totalServGravados)
  {
    $this->totalServGravados = $totalServGravados;
    return $this;
  }

  /**
   * Gets as totalServExentos
   *
   * Total de los servicios exentos de IVA
   *
   * @return float
   */
  public function getTotalServExentos()
  {
    return $this->totalServExentos;
  }

  /**
   * Sets a new totalServExentos
   *
   * Total de los servicios exentos de IVA
   *
   * @param float $totalServExentos
   * @return self
   */
  public function setTotalServExentos($totalServExentos)
  {
    $this->totalServExentos = $totalServExentos;
    return $this;
  }

  /**
   * Gets as totalServExonerado
   *
   * Total servicios exonerados del IVA
   *
   * @return float
   */
  public function getTotalServExonerado()
  {
    return $this->totalServExonerado;
  }

  /**
   * Sets a new totalServExonerado
   *
   * Total servicios exonerados del IVA
   *
   * @param float $totalServExonerado
   * @return self
   */
  public function setTotalServExonerado($totalServExonerado)
  {
    $this->totalServExonerado = $totalServExonerado;
    return $this;
  }

  /**
   * Gets as totalServNoSujeto
   *
   * Este campo será de condición obligatoria, cuando se seleccionen códigos CAByS que correspondan a un servicio y el servicio sea No Sujeto de IVA
   *
   * @return float
   */
  public function getTotalServNoSujeto()
  {
    return $this->totalServNoSujeto;
  }

  /**
   * Sets a new totalServNoSujeto
   *
   * Este campo será de condición obligatoria, cuando se seleccionen códigos CAByS que correspondan a un servicio y el servicio sea No Sujeto de IVA
   *
   * @param float $totalServNoSujeto
   * @return self
   */
  public function setTotalServNoSujeto($totalServNoSujeto)
  {
    $this->totalServNoSujeto = $totalServNoSujeto;
    return $this;
  }

  /**
   * Gets as totalMercGravadas
   *
   * Total mercancias gravadas con IVA
   *
   * @return float
   */
  public function getTotalMercGravadas()
  {
    return $this->totalMercGravadas;
  }

  /**
   * Sets a new totalMercGravadas
   *
   * Total mercancias gravadas con IVA
   *
   * @param float $totalMercGravadas
   * @return self
   */
  public function setTotalMercGravadas($totalMercGravadas)
  {
    $this->totalMercGravadas = $totalMercGravadas;
    return $this;
  }

  /**
   * Gets as totalMercExentas
   *
   * Total mercancias exentas de IVA
   *
   * @return float
   */
  public function getTotalMercExentas()
  {
    return $this->totalMercExentas;
  }

  /**
   * Sets a new totalMercExentas
   *
   * Total mercancias exentas de IVA
   *
   * @param float $totalMercExentas
   * @return self
   */
  public function setTotalMercExentas($totalMercExentas)
  {
    $this->totalMercExentas = $totalMercExentas;
    return $this;
  }

  /**
   * Gets as totalMercExonerada
   *
   * Total mercancías exoneradas del IVA
   *
   * @return float
   */
  public function getTotalMercExonerada()
  {
    return $this->totalMercExonerada;
  }

  /**
   * Sets a new totalMercExonerada
   *
   * Total mercancías exoneradas del IVA
   *
   * @param float $totalMercExonerada
   * @return self
   */
  public function setTotalMercExonerada($totalMercExonerada)
  {
    $this->totalMercExonerada = $totalMercExonerada;
    return $this;
  }

  /**
   * Gets as totalMercNoSujeta
   *
   * Este campo será de condición obligatoria, cuando se seleccionen códigos CAByS que correspondan a una mercancía y la mercancía sea No Sujeta de IVA
   *
   * @return float
   */
  public function getTotalMercNoSujeta()
  {
    return $this->totalMercNoSujeta;
  }

  /**
   * Sets a new totalMercNoSujeta
   *
   * Este campo será de condición obligatoria, cuando se seleccionen códigos CAByS que correspondan a una mercancía y la mercancía sea No Sujeta de IVA
   *
   * @param float $totalMercNoSujeta
   * @return self
   */
  public function setTotalMercNoSujeta($totalMercNoSujeta)
  {
    $this->totalMercNoSujeta = $totalMercNoSujeta;
    return $this;
  }

  /**
   * Gets as totalGravado
   *
   * Total gravado. se obtiene de la suma del total servicios gravados con IV + total mercancias gravadas con IV
   *
   * @return float
   */
  public function getTotalGravado()
  {
    return $this->totalGravado;
  }

  /**
   * Sets a new totalGravado
   *
   * Total gravado. se obtiene de la suma del total servicios gravados con IV + total mercancias gravadas con IV
   *
   * @param float $totalGravado
   * @return self
   */
  public function setTotalGravado($totalGravado)
  {
    $this->totalGravado = $totalGravado;
    return $this;
  }

  /**
   * Gets as totalExento
   *
   * Total Exento, se obtiene de la suma de los campos total servicios exentos IV mas total mercancias exentas IV
   *
   * @return float
   */
  public function getTotalExento()
  {
    return $this->totalExento;
  }

  /**
   * Sets a new totalExento
   *
   * Total Exento, se obtiene de la suma de los campos total servicios exentos IV mas total mercancias exentas IV
   *
   * @param float $totalExento
   * @return self
   */
  public function setTotalExento($totalExento)
  {
    $this->totalExento = $totalExento;
    return $this;
  }

  /**
   * Gets as totalExonerado
   *
   * Se obtiene de la suma de los campos "total servicios exonerados de IVA" mas "total de mercancías exoneradas del IVA".
   *
   * @return float
   */
  public function getTotalExonerado()
  {
    return $this->totalExonerado;
  }

  /**
   * Sets a new totalExonerado
   *
   * Se obtiene de la suma de los campos "total servicios exonerados de IVA" mas "total de mercancías exoneradas del IVA".
   *
   * @param float $totalExonerado
   * @return self
   */
  public function setTotalExonerado($totalExonerado)
  {
    $this->totalExonerado = $totalExonerado;
    return $this;
  }

  /**
   * Gets as totalNoSujeto
   *
   * Se obtiene de la suma de los campos "Total servicios No Sujetos de IVA" mas "Total mercancías No Sujetas de IVA".
   *
   * @return float
   */
  public function getTotalNoSujeto()
  {
    return $this->totalNoSujeto;
  }

  /**
   * Sets a new totalNoSujeto
   *
   * Se obtiene de la suma de los campos "Total servicios No Sujetos de IVA" mas "Total mercancías No Sujetas de IVA".
   *
   * @param float $totalNoSujeto
   * @return self
   */
  public function setTotalNoSujeto($totalNoSujeto)
  {
    $this->totalNoSujeto = $totalNoSujeto;
    return $this;
  }

  /**
   * Gets as totalVenta
   *
   * Se obtiene de la sumatoria de los campos “total gravado”, “total exento”, “Total Exonerado” y “Total No Sujeto
   *
   * @return float
   */
  public function getTotalVenta()
  {
    return $this->totalVenta;
  }

  /**
   * Sets a new totalVenta
   *
   * Se obtiene de la sumatoria de los campos “total gravado”, “total exento”, “Total Exonerado” y “Total No Sujeto
   *
   * @param float $totalVenta
   * @return self
   */
  public function setTotalVenta($totalVenta)
  {
    $this->totalVenta = $totalVenta;
    return $this;
  }

  /**
   * Gets as totalDescuentos
   *
   * Se obtiene de la suma de todos los campo de monto de descuento concedido
   *
   * @return float
   */
  public function getTotalDescuentos()
  {
    return $this->totalDescuentos;
  }

  /**
   * Sets a new totalDescuentos
   *
   * Se obtiene de la suma de todos los campo de monto de descuento concedido
   *
   * @param float $totalDescuentos
   * @return self
   */
  public function setTotalDescuentos($totalDescuentos)
  {
    $this->totalDescuentos = $totalDescuentos;
    return $this;
  }

  /**
   * Gets as totalVentaNeta
   *
   * Se obtiene de la resta de los campos total venta menos total descuento
   *
   * @return float
   */
  public function getTotalVentaNeta()
  {
    return $this->totalVentaNeta;
  }

  /**
   * Sets a new totalVentaNeta
   *
   * Se obtiene de la resta de los campos total venta menos total descuento
   *
   * @param float $totalVentaNeta
   * @return self
   */
  public function setTotalVentaNeta($totalVentaNeta)
  {
    $this->totalVentaNeta = $totalVentaNeta;
    return $this;
  }

  /**
   * Adds as totalDesgloseImpuesto
   *
   * Tipo complejo que contiene los montos desglosados por impuesto cobrado en el comprobante electrónico.
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\TotalDesgloseImpuestoAType $totalDesgloseImpuesto
   */
  public function addToTotalDesgloseImpuesto(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\TotalDesgloseImpuestoAType $totalDesgloseImpuesto, $line)
  {
    $impuesto = 0;
    if (is_null($line->exoneration) || ($line->exoneration == 0) and (is_null($line->impuestoAsumidoEmisorFabrica) || $line->impuestoAsumidoEmisorFabrica == 0))
      $impuesto = $line->tax;
    else
    if (($line->exoneration > 0) || (!is_null($line->impuestoAsumidoEmisorFabrica) && $line->impuestoAsumidoEmisorFabrica >= 0))
      $impuesto = $line->impuestoNeto;

    $totalDesgloseImpuesto->setTotalMontoImpuesto($impuesto);
    $this->totalDesgloseImpuesto[] = $totalDesgloseImpuesto;
    return $this;
  }

  /**
   * isset totalDesgloseImpuesto
   *
   * Tipo complejo que contiene los montos desglosados por impuesto cobrado en el comprobante electrónico.
   *
   * @param int|string $index
   * @return bool
   */
  public function issetTotalDesgloseImpuesto($index)
  {
    return isset($this->totalDesgloseImpuesto[$index]);
  }

  /**
   * unset totalDesgloseImpuesto
   *
   * Tipo complejo que contiene los montos desglosados por impuesto cobrado en el comprobante electrónico.
   *
   * @param int|string $index
   * @return void
   */
  public function unsetTotalDesgloseImpuesto($index)
  {
    unset($this->totalDesgloseImpuesto[$index]);
  }

  /**
   * Gets as totalDesgloseImpuesto
   *
   * Tipo complejo que contiene los montos desglosados por impuesto cobrado en el comprobante electrónico.
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\TotalDesgloseImpuestoAType[]
   */
  public function getTotalDesgloseImpuesto()
  {
    return $this->totalDesgloseImpuesto;
  }

  /**
   * Sets a new totalDesgloseImpuesto
   *
   * Tipo complejo que contiene los montos desglosados por impuesto cobrado en el comprobante electrónico.
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\TotalDesgloseImpuestoAType[] $totalDesgloseImpuesto
   * @return self
   */
  public function setTotalDesgloseImpuesto(array $totalDesgloseImpuesto = null)
  {
    $this->totalDesgloseImpuesto = $totalDesgloseImpuesto;
    return $this;
  }

  /**
   * Gets as totalImpuesto
   *
   * Se obtiene de la suma de todos campos monto del impuesto
   *
   * @return float
   */
  public function getTotalImpuesto()
  {
    return $this->totalImpuesto;
  }

  /**
   * Sets a new totalImpuesto
   *
   * Se obtiene de la suma de todos campos monto del impuesto
   *
   * @param float $totalImpuesto
   * @return self
   */
  public function setTotalImpuesto($totalImpuesto)
  {
    $this->totalImpuesto = $totalImpuesto;
    return $this;
  }

  /**
   * Gets as totalImpAsumEmisorFabrica
   *
   * Este campo es de condición obligatoria, cuando existen producto/servicio gravados con algún impuesto en las líneas de detalle que sean asumidos por el emisor
   *
   * @return float
   */
  public function getTotalImpAsumEmisorFabrica()
  {
    return $this->totalImpAsumEmisorFabrica;
  }

  /**
   * Sets a new totalImpAsumEmisorFabrica
   *
   * Este campo es de condición obligatoria, cuando existen producto/servicio gravados con algún impuesto en las líneas de detalle que sean asumidos por el emisor
   *
   * @param float $totalImpAsumEmisorFabrica
   * @return self
   */
  public function setTotalImpAsumEmisorFabrica($totalImpAsumEmisorFabrica)
  {
    $this->totalImpAsumEmisorFabrica = $totalImpAsumEmisorFabrica;
    return $this;
  }

  /**
   * Gets as totalIVADevuelto
   *
   * IVA Devuelto
   *
   * @return float
   */
  public function getTotalIVADevuelto()
  {
    return $this->totalIVADevuelto;
  }

  /**
   * Sets a new totalIVADevuelto
   *
   * IVA Devuelto
   *
   * @param float $totalIVADevuelto
   * @return self
   */
  public function setTotalIVADevuelto($totalIVADevuelto)
  {
    $this->totalIVADevuelto = $totalIVADevuelto;
    return $this;
  }

  /**
   * Gets as totalOtrosCargos
   *
   * Total Otros Cargos
   *
   * @return float
   */
  public function getTotalOtrosCargos()
  {
    return $this->totalOtrosCargos;
  }

  /**
   * Sets a new totalOtrosCargos
   *
   * Total Otros Cargos
   *
   * @param float $totalOtrosCargos
   * @return self
   */
  public function setTotalOtrosCargos($totalOtrosCargos)
  {
    $this->totalOtrosCargos = $totalOtrosCargos;
    return $this;
  }

  /**
   * Adds as medioPago
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\MedioPagoAType $medioPago
   */
  public function addToMedioPago(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\MedioPagoAType $medioPago)
  {
    $this->medioPago[] = $medioPago;
    return $this;
  }

  /**
   * isset medioPago
   *
   * @param int|string $index
   * @return bool
   */
  public function issetMedioPago($index)
  {
    return isset($this->medioPago[$index]);
  }

  /**
   * unset medioPago
   *
   * @param int|string $index
   * @return void
   */
  public function unsetMedioPago($index)
  {
    unset($this->medioPago[$index]);
  }

  /**
   * Gets as medioPago
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\MedioPagoAType[]
   */
  public function getMedioPago()
  {
    return $this->medioPago;
  }

  /**
   * Sets a new medioPago
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType\MedioPagoAType[] $medioPago
   * @return self
   */
  public function setMedioPago(array $medioPago = null)
  {
    $this->medioPago = $medioPago;
    return $this;
  }

  /**
   * Gets as totalComprobante
   *
   * Se obtiene de la suma de los campos "total venta neta", "monto total del impuesto" y "total otros cargos" menos "total IVA devuelto", en caso de contar con dichos campos.
   *
   * @return float
   */
  public function getTotalComprobante()
  {
    return $this->totalComprobante;
  }

  /**
   * Sets a new totalComprobante
   *
   * Se obtiene de la suma de los campos "total venta neta", "monto total del impuesto" y "total otros cargos" menos "total IVA devuelto", en caso de contar con dichos campos.
   *
   * @param float $totalComprobante
   * @return self
   */
  public function setTotalComprobante($totalComprobante)
  {
    $this->totalComprobante = $totalComprobante;
    return $this;
  }
}
