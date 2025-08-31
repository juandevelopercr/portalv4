<?php

namespace App\Models\Hacienda\ComprobanteElectronico;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class representing ComprobanteElectronicoAType
 */
class ComprobanteElectronicoAType
{
  /**
   * Corresponde a la clave del comprobante. Es un campo fijo de cincuenta posiciones y se tiene que utilizar para
   * la consulta del código QR
   *
   * @var string $clave
   */
  private $clave = null;

  /**
   * Se debe indicar el número de cedula de identificación del proveedor de sistemas que esté utilizando para la emisión de comprobantes
   * electrónicos
   *
   * @var string $proveedorSistemas
   */
  private $proveedorSistemas = null;

  /**
   * Se debe de indicar el código de la actividad económica inscrita a la cual corresponde el comprobante que se está generando
   *
   * @var string $codigoActividadEmisor
   */
  private $codigoActividadEmisor = null;

  /**
   * Se debe de indicar el código de la actividad económica inscrita del receptor a la cual corresponden los bienes o servicios que se le están
   * facturando al receptor en caso de ser requerido para un crédito o un gasto deducible.
   *
   * @var string $codigoActividadReceptor
   */
  private $codigoActividadReceptor = null;

  /**
   * Numeración consecutiva del comprobante
   *
   * @var string $numeroConsecutivo
   */
  private $numeroConsecutivo = null;

  /**
   * @var \DateTime $fechaEmision
   */
  private $fechaEmision = null;

  /**
   * Emisor del documento
   *
   * @var \App\Models\Hacienda\EmisorType $emisor
   */
  private $emisor = null;

  /**
   * Receptor del documento
   *
   * @var \App\Models\Hacienda\ReceptorType $receptor
   */
  private $receptor = null;

  /**
   * Condiciones de la venta: 01 Contado, 02 Crédito, 03 Consignación, 04 Apartado, 05 Arrendamiento con opción de compra, 06 Arrendamiento en función financiera, 07 Cobro a favor de un tercero, 08 servicxios prestados al estado a credito, 09 pago del servicio prestado al estado,10 venta a crédito hasta 90 dias,11 pago de venta a crédito en IVA hasta 90 dias, 99 Otros
   *
   * @var string $condicionVenta
   */
  private $condicionVenta = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de "Otros" de la nota
   * 5. Se debe describir puntualmente la condición de la venta utilizada.
   *
   * @var string $condicionVentaOtros
   */
  private $condicionVentaOtros = null;

  /**
   * Plazo del crédito, es obligatorio cuando la venta del producto o prestación del servicio sea a crédito
   *
   * @var int $plazoCredito
   */
  private $plazoCredito = null;

  /**
   * Detalle del Servicio,
   * Mercancía u otro
   *
   * @var \App\Models\Hacienda\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType[] $detalleServicio
   */
  private $detalleServicio = null;

  /**
   * Información sobre otros cargos
   *
   * @var \App\Models\Hacienda\OtrosCargosType[] $otrosCargos
   */
  private $otrosCargos = [];

  /**
   * @var \App\Models\Hacienda\ComprobanteElectronicoAType\ResumenComprobanteAType $resumenComprobante
   */
  private $resumenComprobante = null;

  /**
   * @var \App\Models\Hacienda\ComprobanteElectronicoAType\InformacionReferenciaAType[] $informacionReferencia
   */
  private $informacionReferencia = [];

  /**
   * @var \App\Models\Hacienda\ComprobanteElectronicoAType\OtrosAType $otros
   */
  private $otros = null;

  /**
   * Gets as clave
   *
   * Corresponde a la clave del comprobante. Es un campo fijo de cincuenta posiciones y se tiene que utilizar para
   * la consulta del código QR
   *
   * @return string
   */
  public function getClave()
  {
    return $this->clave;
  }

  /**
   * Sets a new clave
   *
   * Corresponde a la clave del comprobante. Es un campo fijo de cincuenta posiciones y se tiene que utilizar para
   * la consulta del código QR
   *
   * @param string $clave
   * @return self
   */
  public function setClave($clave)
  {
    $this->clave = $clave;
    return $this;
  }

  /**
   * Gets as proveedorSistemas
   *
   * Se debe indicar el número de cedula de identificación del proveedor de sistemas que esté utilizando para la emisión de comprobantes
   * electrónicos
   *
   * @return string
   */
  public function getProveedorSistemas()
  {
    return $this->proveedorSistemas;
  }

  /**
   * Sets a new proveedorSistemas
   *
   * Se debe indicar el número de cedula de identificación del proveedor de sistemas que esté utilizando para la emisión de comprobantes
   * electrónicos
   *
   * @param string $proveedorSistemas
   * @return self
   */
  public function setProveedorSistemas($proveedorSistemas)
  {
    $this->proveedorSistemas = $proveedorSistemas;
    return $this;
  }

  /**
   * Gets as codigoActividadEmisor
   *
   * Se debe de indicar el código de la actividad económica inscrita a la cual corresponde el comprobante que se está generando
   *
   * @return string
   */
  public function getCodigoActividadEmisor()
  {
    return $this->codigoActividadEmisor;
  }

  /**
   * Sets a new codigoActividadEmisor
   *
   * Se debe de indicar el código de la actividad económica inscrita a la cual corresponde el comprobante que se está generando
   *
   * @param string $codigoActividadEmisor
   * @return self
   */
  public function setCodigoActividadEmisor($codigoActividadEmisor)
  {
    $this->codigoActividadEmisor = $codigoActividadEmisor;
    return $this;
  }

  /**
   * Gets as codigoActividadReceptor
   *
   * Se debe de indicar el código de la actividad económica inscrita del receptor a la cual corresponden los bienes o servicios que se le están
   * facturando al receptor en caso de ser requerido para un crédito o un gasto deducible.
   *
   * @return string
   */
  public function getCodigoActividadReceptor()
  {
    return $this->codigoActividadReceptor;
  }

  /**
   * Sets a new codigoActividadReceptor
   *
   * Se debe de indicar el código de la actividad económica inscrita del receptor a la cual corresponden los bienes o servicios que se le están
   * facturando al receptor en caso de ser requerido para un crédito o un gasto deducible.
   *
   * @param string $codigoActividadReceptor
   * @return self
   */
  public function setCodigoActividadReceptor($codigoActividadReceptor)
  {
    $this->codigoActividadReceptor = $codigoActividadReceptor;
    return $this;
  }

  /**
   * Gets as numeroConsecutivo
   *
   * Numeración consecutiva del comprobante
   *
   * @return string
   */
  public function getNumeroConsecutivo()
  {
    return $this->numeroConsecutivo;
  }

  /**
   * Sets a new numeroConsecutivo
   *
   * Numeración consecutiva del comprobante
   *
   * @param string $numeroConsecutivo
   * @return self
   */
  public function setNumeroConsecutivo($numeroConsecutivo)
  {
    $this->numeroConsecutivo = $numeroConsecutivo;
    return $this;
  }

  /**
   * Gets as fechaEmision
   *
   * @return \DateTime
   */
  public function getFechaEmision()
  {
    return $this->fechaEmision;
  }

  /**
   * Sets a new fechaEmision
   *
   * @param \DateTime $fechaEmision
   * @return self
   */
  public function setFechaEmision(\DateTime $fechaEmision)
  {
    $this->fechaEmision = $fechaEmision;
    return $this;
  }

  /**
   * Gets as emisor
   *
   * Emisor del documento
   *
   * @return \App\Models\Hacienda\EmisorType
   */
  public function getEmisor()
  {
    return $this->emisor;
  }

  /**
   * Sets a new emisor
   *
   * Emisor del documento
   *
   * @param \App\Models\Hacienda\EmisorType $emisor
   * @return self
   */
  public function setEmisor(\App\Models\Hacienda\EmisorType $emisor)
  {
    $this->emisor = $emisor;
    return $this;
  }

  /**
   * Gets as receptor
   *
   * Receptor del documento
   *
   * @return \App\Models\Hacienda\ReceptorType
   */
  public function getReceptor()
  {
    return $this->receptor;
  }

  /**
   * Sets a new receptor
   *
   * Receptor del documento
   *
   * @param \App\Models\Hacienda\ReceptorType $receptor
   * @return self
   */
  public function setReceptor(\App\Models\Hacienda\ReceptorType $receptor)
  {
    $this->receptor = $receptor;
    return $this;
  }

  /**
   * Gets as condicionVenta
   *
   * Condiciones de la venta: 01 Contado, 02 Crédito, 03 Consignación, 04 Apartado, 05 Arrendamiento con opción de compra, 06 Arrendamiento en función financiera, 07 Cobro a favor de un tercero, 08 servicxios prestados al estado a credito, 09 pago del servicio prestado al estado,10 venta a crédito hasta 90 dias,11 pago de venta a crédito en IVA hasta 90 dias, 99 Otros
   *
   * @return string
   */
  public function getCondicionVenta()
  {
    return $this->condicionVenta;
  }

  /**
   * Sets a new condicionVenta
   *
   * Condiciones de la venta: 01 Contado, 02 Crédito, 03 Consignación, 04 Apartado, 05 Arrendamiento con opción de compra, 06 Arrendamiento en función financiera, 07 Cobro a favor de un tercero, 08 servicxios prestados al estado a credito, 09 pago del servicio prestado al estado,10 venta a crédito hasta 90 dias,11 pago de venta a crédito en IVA hasta 90 dias, 99 Otros
   *
   * @param string $condicionVenta
   * @return self
   */
  public function setCondicionVenta($condicionVenta)
  {
    $this->condicionVenta = $condicionVenta;
    return $this;
  }

  /**
   * Gets as condicionVentaOtros
   *
   * Será obligatorio en caso de utilizar el código 99 de "Otros" de la nota
   * 5. Se debe describir puntualmente la condición de la venta utilizada.
   *
   * @return string
   */
  public function getCondicionVentaOtros()
  {
    return $this->condicionVentaOtros;
  }

  /**
   * Sets a new condicionVentaOtros
   *
   * Será obligatorio en caso de utilizar el código 99 de "Otros" de la nota
   * 5. Se debe describir puntualmente la condición de la venta utilizada.
   *
   * @param string $condicionVentaOtros
   * @return self
   */
  public function setCondicionVentaOtros($condicionVentaOtros)
  {
    $this->condicionVentaOtros = $condicionVentaOtros;
    return $this;
  }

  /**
   * Gets as plazoCredito
   *
   * Plazo del crédito, es obligatorio cuando la venta del producto o prestación del servicio sea a crédito
   *
   * @return int
   */
  public function getPlazoCredito()
  {
    return $this->plazoCredito;
  }

  /**
   * Sets a new plazoCredito
   *
   * Plazo del crédito, es obligatorio cuando la venta del producto o prestación del servicio sea a crédito
   *
   * @param int $plazoCredito
   * @return self
   */
  public function setPlazoCredito($plazoCredito)
  {
    $this->plazoCredito = $plazoCredito;
    return $this;
  }

  /**
   * Adds as lineaDetalle
   *
   * Detalle del Servicio,
   * Mercancía u otro
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType $lineaDetalle
   */
  public function addToDetalleServicio(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType $lineaDetalle)
  {
    $this->detalleServicio[] = $lineaDetalle;
    return $this;
  }

  /**
   * isset detalleServicio
   *
   * Detalle del Servicio,
   * Mercancía u otro
   *
   * @param int|string $index
   * @return bool
   */
  public function issetDetalleServicio($index)
  {
    return isset($this->detalleServicio[$index]);
  }

  /**
   * unset detalleServicio
   *
   * Detalle del Servicio,
   * Mercancía u otro
   *
   * @param int|string $index
   * @return void
   */
  public function unsetDetalleServicio($index)
  {
    unset($this->detalleServicio[$index]);
  }

  /**
   * Gets as detalleServicio
   *
   * Detalle del Servicio,
   * Mercancía u otro
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType[]
   */
  public function getDetalleServicio()
  {
    return $this->detalleServicio;
  }

  /**
   * Sets a new detalleServicio
   *
   * Detalle del Servicio,
   * Mercancía u otro
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType[] $detalleServicio
   * @return self
   */
  public function setDetalleServicio(array $detalleServicio = [])
  {
    $this->detalleServicio = $detalleServicio;
    return $this;
  }

  /**
   * Adds as otrosCargos
   *
   * Información sobre otros cargos
   *
   * @return self
   * @param \App\Models\Hacienda\OtrosCargosType $otrosCargos
   */
  public function addToOtrosCargos(\App\Models\Hacienda\OtrosCargosType $otrosCargos)
  {
    $this->otrosCargos[] = $otrosCargos;
    return $this;
  }

  /**
   * isset otrosCargos
   *
   * Información sobre otros cargos
   *
   * @param int|string $index
   * @return bool
   */
  public function issetOtrosCargos($index)
  {
    return isset($this->otrosCargos[$index]);
  }

  /**
   * unset otrosCargos
   *
   * Información sobre otros cargos
   *
   * @param int|string $index
   * @return void
   */
  public function unsetOtrosCargos($index)
  {
    unset($this->otrosCargos[$index]);
  }

  /**
   * Gets as otrosCargos
   *
   * Información sobre otros cargos
   *
   * @return \App\Models\Hacienda\OtrosCargosType[]
   */
  public function getOtrosCargos()
  {
    return $this->otrosCargos;
  }

  /**
   * Sets a new otrosCargos
   *
   * Información sobre otros cargos
   *
   * @param \App\Models\Hacienda\OtrosCargosType[] $otrosCargos
   * @return self
   */
  public function setOtrosCargos(array $otrosCargos = null)
  {
    $this->otrosCargos = $otrosCargos;
    return $this;
  }

  /**
   * Gets as resumenComprobante
   *
   * @return \App\Models\Hacienda\ComprobanteElectronicoAType\ResumenComprobanteAType
   */
  public function getResumenFactura()
  {
    return $this->resumenComprobante;
  }

  /**
   * Sets a new resumenComprobante
   *
   * @param \App\Models\Hacienda\ComprobanteElectronicoAType\ResumenComprobanteAType $resumenComprobante
   * @return self
   */
  public function setResumenFactura(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\ResumenComprobanteAType $resumenComprobante)
  {
    $this->resumenComprobante = $resumenComprobante;
    return $this;
  }

  /**
   * Adds as informacionReferencia
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\InformacionReferenciaAType $informacionReferencia
   */
  public function addToInformacionReferencia(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\InformacionReferenciaAType $informacionReferencia)
  {
    $this->informacionReferencia[] = $informacionReferencia;
    return $this;
  }

  /**
   * isset informacionReferencia
   *
   * @param int|string $index
   * @return bool
   */
  public function issetInformacionReferencia($index)
  {
    return isset($this->informacionReferencia[$index]);
  }

  /**
   * unset informacionReferencia
   *
   * @param int|string $index
   * @return void
   */
  public function unsetInformacionReferencia($index)
  {
    unset($this->informacionReferencia[$index]);
  }

  /**
   * Gets as informacionReferencia
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\InformacionReferenciaAType[]
   */
  public function getInformacionReferencia()
  {
    return $this->informacionReferencia;
  }

  /**
   * Sets a new informacionReferencia
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\InformacionReferenciaAType[] $informacionReferencia
   * @return self
   */
  public function setInformacionReferencia(array $informacionReferencia = null)
  {
    $this->informacionReferencia = $informacionReferencia;
    return $this;
  }

  /**
   * Gets as otros
   *
   * @return \App\Models\Hacienda\ComprobanteElectronicoAType\OtrosAType
   */
  public function getOtros()
  {
    return $this->otros;
  }

  /**
   * Sets a new otros
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType $otros
   * @return self
   */
  public function setOtros(?\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\OtrosAType $otros = null)
  {
    $this->otros = $otros;
    return $this;
  }
}
