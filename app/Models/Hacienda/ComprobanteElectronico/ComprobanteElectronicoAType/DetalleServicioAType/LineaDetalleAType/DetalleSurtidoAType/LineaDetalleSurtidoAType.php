<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType;

/**
 * Class representing LineaDetalleSurtidoAType
 */
class LineaDetalleSurtidoAType
{
  /**
   * Código de Producto
   * /Servicio componente de Surtido
   *
   * @var string $codigoCABYSSurtido
   */
  private $codigoCABYSSurtido = null;

  /**
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\CodigoComercialSurtidoAType[] $codigoComercialSurtido
   */
  private $codigoComercialSurtido = [];

  /**
   * Es un número decimal compuesto por 13 enteros y 3 decimales.
   *
   * @var float $cantidadSurtido
   */
  private $cantidadSurtido = null;

  /**
   * @var string $unidadMedidaSurtido
   */
  private $unidadMedidaSurtido = null;

  /**
   * Nodo utilizado para indicar una unidad de medida que nace del propio giro comercial del establecimiento, no es una cantidad estandarizada de una determinada magnitud física, definida y adoptada por convención o por ley ejemplo: "1
   * Tarima"
   *
   * @var string $unidadMedidaComercialSurtido
   */
  private $unidadMedidaComercialSurtido = null;

  /**
   * Detalle de la mercancía transferida o servicio prestado incluido en el surtido
   *
   * @var string $detalleSurtido
   */
  private $detalleSurtido = null;

  /**
   * @var float $precioUnitarioSurtido
   */
  private $precioUnitarioSurtido = null;

  /**
   * Se obtiene de la multiplicación del campo "Cantidad componente de surtido" por el campo "Precio unitario componente de surtido".
   *
   * @var float $montoTotalSurtido
   */
  private $montoTotalSurtido = null;

  /**
   * Se puede incluir un máximo de 5 repeticiones de
   * descuentos, cada descuento adicional se calcula sobre la base menos el descuento anterior.
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\DescuentoSurtidoAType[] $descuentoSurtido
   */
  private $descuentoSurtido = [];

  /**
   * Se obtiene de la resta del campo "Monto total componente surtido" menos "Monto de descuentos concedidos al componente de surtido"
   *
   * @var float $subTotalSurtido
   */
  private $subTotalSurtido = null;

  /**
   * En este campo se indicará si el Impuesto al Valor Agregado fue cobrado a nivel de fábrica, por lo que deberá ser utilizado únicamente por los obligados tributarios a realizar el pago de esta forma.
   * Se convierte en obligatorio cuando el IVA se cobra o se cobró a nivel de fábrica.
   * Al hacer uso del presente campo el producto se entenderá exento para el código 02, por lo cual no deberá llenar el subnodo de impuestos para el cálculo del IVA.
   *  ▪Para el código 01 el emisor puede separar los impuestos
   * que está cobrando en la fábrica.
   *
   * @var string $iVACobradoFabricaSurtido
   */
  private $iVACobradoFabricaSurtido = null;

  /**
   * Este campo será de condición obligatoria, cuando el producto este gravado con algún impuesto. Se obtiene de la suma entre el campo "Subtotal componente del surtido", más el impuesto selectivo de consumo (02), el Impuesto específico de Bebidas Alcohólicas (04) y el Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador (05), cuando corresponda. Este campo se podrá editar cuando se seleccione en el campo "IVA cobrado a nivel de fábrica" el Código 01 o en el campo de "Código del impuesto" el código 07. Validación: En caso de utilizarse el código 01, en el campo de IVA cobrado a nivel de fábrica, se verificará que este campo se encuentre en el comprobante, caso contrario se rechazará. Además, se deberá incluir un valor mayor a "cero".
   *
   * @var float $baseImponibleSurtido
   */
  private $baseImponibleSurtido = null;

  /**
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType[] $impuestoSurtido
   */
  private $impuestoSurtido = [];

  /**
   * Gets as codigoCABYSSurtido
   *
   * Código de Producto
   * /Servicio componente de Surtido
   *
   * @return string
   */
  public function getCodigoCABYSSurtido()
  {
    return $this->codigoCABYSSurtido;
  }

  /**
   * Sets a new codigoCABYSSurtido
   *
   * Código de Producto
   * /Servicio componente de Surtido
   *
   * @param string $codigoCABYSSurtido
   * @return self
   */
  public function setCodigoCABYSSurtido($codigoCABYSSurtido)
  {
    $this->codigoCABYSSurtido = $codigoCABYSSurtido;
    return $this;
  }

  /**
   * Adds as codigoComercialSurtido
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\CodigoComercialSurtidoAType $codigoComercialSurtido
   */
  public function addToCodigoComercialSurtido(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\CodigoComercialSurtidoAType $codigoComercialSurtido)
  {
    $this->codigoComercialSurtido[] = $codigoComercialSurtido;
    return $this;
  }

  /**
   * isset codigoComercialSurtido
   *
   * @param int|string $index
   * @return bool
   */
  public function issetCodigoComercialSurtido($index)
  {
    return isset($this->codigoComercialSurtido[$index]);
  }

  /**
   * unset codigoComercialSurtido
   *
   * @param int|string $index
   * @return void
   */
  public function unsetCodigoComercialSurtido($index)
  {
    unset($this->codigoComercialSurtido[$index]);
  }

  /**
   * Gets as codigoComercialSurtido
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\CodigoComercialSurtidoAType[]
   */
  public function getCodigoComercialSurtido()
  {
    return $this->codigoComercialSurtido;
  }

  /**
   * Sets a new codigoComercialSurtido
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\CodigoComercialSurtidoAType[] $codigoComercialSurtido
   * @return self
   */
  public function setCodigoComercialSurtido(array $codigoComercialSurtido = null)
  {
    $this->codigoComercialSurtido = $codigoComercialSurtido;
    return $this;
  }

  /**
   * Gets as cantidadSurtido
   *
   * Es un número decimal compuesto por 13 enteros y 3 decimales.
   *
   * @return float
   */
  public function getCantidadSurtido()
  {
    return $this->cantidadSurtido;
  }

  /**
   * Sets a new cantidadSurtido
   *
   * Es un número decimal compuesto por 13 enteros y 3 decimales.
   *
   * @param float $cantidadSurtido
   * @return self
   */
  public function setCantidadSurtido($cantidadSurtido)
  {
    $this->cantidadSurtido = $cantidadSurtido;
    return $this;
  }

  /**
   * Gets as unidadMedidaSurtido
   *
   * @return string
   */
  public function getUnidadMedidaSurtido()
  {
    return $this->unidadMedidaSurtido;
  }

  /**
   * Sets a new unidadMedidaSurtido
   *
   * @param string $unidadMedidaSurtido
   * @return self
   */
  public function setUnidadMedidaSurtido($unidadMedidaSurtido)
  {
    $this->unidadMedidaSurtido = $unidadMedidaSurtido;
    return $this;
  }

  /**
   * Gets as unidadMedidaComercialSurtido
   *
   * Nodo utilizado para indicar una unidad de medida que nace del propio giro comercial del establecimiento, no es una cantidad estandarizada de una determinada magnitud física, definida y adoptada por convención o por ley ejemplo: "1
   * Tarima"
   *
   * @return string
   */
  public function getUnidadMedidaComercialSurtido()
  {
    return $this->unidadMedidaComercialSurtido;
  }

  /**
   * Sets a new unidadMedidaComercialSurtido
   *
   * Nodo utilizado para indicar una unidad de medida que nace del propio giro comercial del establecimiento, no es una cantidad estandarizada de una determinada magnitud física, definida y adoptada por convención o por ley ejemplo: "1
   * Tarima"
   *
   * @param string $unidadMedidaComercialSurtido
   * @return self
   */
  public function setUnidadMedidaComercialSurtido($unidadMedidaComercialSurtido)
  {
    $this->unidadMedidaComercialSurtido = $unidadMedidaComercialSurtido;
    return $this;
  }

  /**
   * Gets as detalleSurtido
   *
   * Detalle de la mercancía transferida o servicio prestado incluido en el surtido
   *
   * @return string
   */
  public function getDetalleSurtido()
  {
    return $this->detalleSurtido;
  }

  /**
   * Sets a new detalleSurtido
   *
   * Detalle de la mercancía transferida o servicio prestado incluido en el surtido
   *
   * @param string $detalleSurtido
   * @return self
   */
  public function setDetalleSurtido($detalleSurtido)
  {
    $this->detalleSurtido = $detalleSurtido;
    return $this;
  }

  /**
   * Gets as precioUnitarioSurtido
   *
   * @return float
   */
  public function getPrecioUnitarioSurtido()
  {
    return $this->precioUnitarioSurtido;
  }

  /**
   * Sets a new precioUnitarioSurtido
   *
   * @param float $precioUnitarioSurtido
   * @return self
   */
  public function setPrecioUnitarioSurtido($precioUnitarioSurtido)
  {
    $this->precioUnitarioSurtido = $precioUnitarioSurtido;
    return $this;
  }

  /**
   * Gets as montoTotalSurtido
   *
   * Se obtiene de la multiplicación del campo "Cantidad componente de surtido" por el campo "Precio unitario componente de surtido".
   *
   * @return float
   */
  public function getMontoTotalSurtido()
  {
    return $this->montoTotalSurtido;
  }

  /**
   * Sets a new montoTotalSurtido
   *
   * Se obtiene de la multiplicación del campo "Cantidad componente de surtido" por el campo "Precio unitario componente de surtido".
   *
   * @param float $montoTotalSurtido
   * @return self
   */
  public function setMontoTotalSurtido($montoTotalSurtido)
  {
    $this->montoTotalSurtido = $montoTotalSurtido;
    return $this;
  }

  /**
   * Adds as descuentoSurtido
   *
   * Se puede incluir un máximo de 5 repeticiones de
   * descuentos, cada descuento adicional se calcula sobre la base menos el descuento anterior.
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\DescuentoSurtidoAType $descuentoSurtido
   */
  public function addToDescuentoSurtido(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\DescuentoSurtidoAType $descuentoSurtido)
  {
    $this->descuentoSurtido[] = $descuentoSurtido;
    return $this;
  }

  /**
   * isset descuentoSurtido
   *
   * Se puede incluir un máximo de 5 repeticiones de
   * descuentos, cada descuento adicional se calcula sobre la base menos el descuento anterior.
   *
   * @param int|string $index
   * @return bool
   */
  public function issetDescuentoSurtido($index)
  {
    return isset($this->descuentoSurtido[$index]);
  }

  /**
   * unset descuentoSurtido
   *
   * Se puede incluir un máximo de 5 repeticiones de
   * descuentos, cada descuento adicional se calcula sobre la base menos el descuento anterior.
   *
   * @param int|string $index
   * @return void
   */
  public function unsetDescuentoSurtido($index)
  {
    unset($this->descuentoSurtido[$index]);
  }

  /**
   * Gets as descuentoSurtido
   *
   * Se puede incluir un máximo de 5 repeticiones de
   * descuentos, cada descuento adicional se calcula sobre la base menos el descuento anterior.
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\DescuentoSurtidoAType[]
   */
  public function getDescuentoSurtido()
  {
    return $this->descuentoSurtido;
  }

  /**
   * Sets a new descuentoSurtido
   *
   * Se puede incluir un máximo de 5 repeticiones de
   * descuentos, cada descuento adicional se calcula sobre la base menos el descuento anterior.
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\DescuentoSurtidoAType[] $descuentoSurtido
   * @return self
   */
  public function setDescuentoSurtido(array $descuentoSurtido = null)
  {
    $this->descuentoSurtido = $descuentoSurtido;
    return $this;
  }

  /**
   * Gets as subTotalSurtido
   *
   * Se obtiene de la resta del campo "Monto total componente surtido" menos "Monto de descuentos concedidos al componente de surtido"
   *
   * @return float
   */
  public function getSubTotalSurtido()
  {
    return $this->subTotalSurtido;
  }

  /**
   * Sets a new subTotalSurtido
   *
   * Se obtiene de la resta del campo "Monto total componente surtido" menos "Monto de descuentos concedidos al componente de surtido"
   *
   * @param float $subTotalSurtido
   * @return self
   */
  public function setSubTotalSurtido($subTotalSurtido)
  {
    $this->subTotalSurtido = $subTotalSurtido;
    return $this;
  }

  /**
   * Gets as iVACobradoFabricaSurtido
   *
   * En este campo se indicará si el Impuesto al Valor Agregado fue cobrado a nivel de fábrica, por lo que deberá ser utilizado únicamente por los obligados tributarios a realizar el pago de esta forma.
   * Se convierte en obligatorio cuando el IVA se cobra o se cobró a nivel de fábrica.
   * Al hacer uso del presente campo el producto se entenderá exento para el código 02, por lo cual no deberá llenar el subnodo de impuestos para el cálculo del IVA.
   *  ▪Para el código 01 el emisor puede separar los impuestos
   * que está cobrando en la fábrica.
   *
   * @return string
   */
  public function getIVACobradoFabricaSurtido()
  {
    return $this->iVACobradoFabricaSurtido;
  }

  /**
   * Sets a new iVACobradoFabricaSurtido
   *
   * En este campo se indicará si el Impuesto al Valor Agregado fue cobrado a nivel de fábrica, por lo que deberá ser utilizado únicamente por los obligados tributarios a realizar el pago de esta forma.
   * Se convierte en obligatorio cuando el IVA se cobra o se cobró a nivel de fábrica.
   * Al hacer uso del presente campo el producto se entenderá exento para el código 02, por lo cual no deberá llenar el subnodo de impuestos para el cálculo del IVA.
   *  ▪Para el código 01 el emisor puede separar los impuestos
   * que está cobrando en la fábrica.
   *
   * @param string $iVACobradoFabricaSurtido
   * @return self
   */
  public function setIVACobradoFabricaSurtido($iVACobradoFabricaSurtido)
  {
    $this->iVACobradoFabricaSurtido = $iVACobradoFabricaSurtido;
    return $this;
  }

  /**
   * Gets as baseImponibleSurtido
   *
   * Este campo será de condición obligatoria, cuando el producto este gravado con algún impuesto. Se obtiene de la suma entre el campo "Subtotal componente del surtido", más el impuesto selectivo de consumo (02), el Impuesto específico de Bebidas Alcohólicas (04) y el Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador (05), cuando corresponda. Este campo se podrá editar cuando se seleccione en el campo "IVA cobrado a nivel de fábrica" el Código 01 o en el campo de "Código del impuesto" el código 07. Validación: En caso de utilizarse el código 01, en el campo de IVA cobrado a nivel de fábrica, se verificará que este campo se encuentre en el comprobante, caso contrario se rechazará. Además, se deberá incluir un valor mayor a "cero".
   *
   * @return float
   */
  public function getBaseImponibleSurtido()
  {
    return $this->baseImponibleSurtido;
  }

  /**
   * Sets a new baseImponibleSurtido
   *
   * Este campo será de condición obligatoria, cuando el producto este gravado con algún impuesto. Se obtiene de la suma entre el campo "Subtotal componente del surtido", más el impuesto selectivo de consumo (02), el Impuesto específico de Bebidas Alcohólicas (04) y el Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador (05), cuando corresponda. Este campo se podrá editar cuando se seleccione en el campo "IVA cobrado a nivel de fábrica" el Código 01 o en el campo de "Código del impuesto" el código 07. Validación: En caso de utilizarse el código 01, en el campo de IVA cobrado a nivel de fábrica, se verificará que este campo se encuentre en el comprobante, caso contrario se rechazará. Además, se deberá incluir un valor mayor a "cero".
   *
   * @param float $baseImponibleSurtido
   * @return self
   */
  public function setBaseImponibleSurtido($baseImponibleSurtido)
  {
    $this->baseImponibleSurtido = $baseImponibleSurtido;
    return $this;
  }

  /**
   * Adds as impuestoSurtido
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType $impuestoSurtido
   */
  public function addToImpuestoSurtido(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType $impuestoSurtido)
  {
    $this->impuestoSurtido[] = $impuestoSurtido;
    return $this;
  }

  /**
   * isset impuestoSurtido
   *
   * @param int|string $index
   * @return bool
   */
  public function issetImpuestoSurtido($index)
  {
    return isset($this->impuestoSurtido[$index]);
  }

  /**
   * unset impuestoSurtido
   *
   * @param int|string $index
   * @return void
   */
  public function unsetImpuestoSurtido($index)
  {
    unset($this->impuestoSurtido[$index]);
  }

  /**
   * Gets as impuestoSurtido
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType[]
   */
  public function getImpuestoSurtido()
  {
    return $this->impuestoSurtido;
  }

  /**
   * Sets a new impuestoSurtido
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType\ImpuestoSurtidoAType[] $impuestoSurtido
   * @return self
   */
  public function setImpuestoSurtido(array $impuestoSurtido)
  {
    $this->impuestoSurtido = $impuestoSurtido;
    return $this;
  }
}
