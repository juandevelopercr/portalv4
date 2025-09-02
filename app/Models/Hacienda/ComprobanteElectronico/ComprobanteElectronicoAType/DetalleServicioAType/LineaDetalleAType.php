<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType;

use App\Models\Business;
use App\Models\TransactionLine;
use App\Models\Hacienda\CodigoType;
use App\Models\Hacienda\DescuentoType;
use App\Models\Hacienda\ImpuestoType;
use Illuminate\Support\Facades\Session;

/**
 * Class representing LineaDetalleAType
 */
class LineaDetalleAType
{
  /**
   * Número de línea del detalle
   *
   * @var int $numeroLinea
   */
  private $numeroLinea = null;

  /**
   * Código de Producto/servicio
   *
   * @var string $codigoCABYS
   */
  private $codigoCABYS = null;

  /**
   * @var \App\Models\Hacienda\CodigoType[] $codigoComercial
   */
  private $codigoComercial = [];

  /**
   * Cantidad
   *
   * @var float $cantidad
   */
  private $cantidad = null;

  /**
   * Unidad de medida
   *
   * @var string $unidadMedida
   */
  private $unidadMedida = null;

  /**
   * Este campo se utilizará para identificar el tipo de transacción
   * que se realizará.
   *
   * @var string $tipoTransaccion
   */
  private $tipoTransaccion = null;

  /**
   * Unidad de medida comercial
   *
   * @var string $unidadMedidaComercial
   */
  private $unidadMedidaComercial = null;

  /**
   * Detalle de la mercancia transferida o servicio prestado
   *
   * @var string $detalle
   */
  private $detalle = null;

  /**
   * Número de VIN o Serie
   * del medio de transporte
   *
   * @var string[] $numeroVINoSerie
   */
  private $numeroVINoSerie = [];

  /**
   * Se refiere al respectivo número de registro del Ministerio de Salud
   *
   * @var string $registroMedicamento
   */
  private $registroMedicamento = null;

  /**
   * Código de la presentación del medicamento.
   *
   * @var string $formaFarmaceutica
   */
  private $formaFarmaceutica = null;

  /**
   * Tipo complejo que representa cada línea del detalle de los componentes de un surtido, paquete o combinación de productos. Se debe utilizar exclusivamente cuando en la línea de detalle se está facturando un paquete, surtido o combo, entendido como la combinación de más de dos productos con diferentes códigos de producto/servicio.
   *
   * @var \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType[] $detalleSurtido
   */
  private $detalleSurtido = null;

  /**
   * Precio Unitario
   *
   * @var float $precioUnitario
   */
  private $precioUnitario = null;

  /**
   * Se obtiene de multiplicar el campo cantidad por el campo precio unitario
   *
   * @var float $montoTotal
   */
  private $montoTotal = null;

  /**
   * @var \App\Models\Hacienda\DescuentoType[] $descuento
   */
  private $descuento = [];

  /**
   *
   *
   * @var float $montoDescuento
   */
  private $montoDescuento = null;

  /**
   * Se obtiene de la resta del campo monto total menos monto de descuento concedido
   *
   * @var float $subTotal
   */
  private $subTotal = null;

  /**
   * En este campo se indicará si el Impuesto al Valor Agregado fue cobrado a nivel de fábrica, por lo que deberá ser utilizado únicamente por los obligados tributarios a realizar el pago de
   * esta forma. Se convierte en obligatorio cuando el IVA se cobra o se cobró a nivel de fábrica. Al hacer uso del presente campo el producto se entenderá exento para el código 02, por lo cual no deberá llenar el subnodo de impuestos para el cálculo del IVA.
   * Para el código 01 el emisor puede separar los impuestos
   * que está cobrando en la fábrica.
   *
   * @var string $iVACobradoFabrica
   */
  private $iVACobradoFabrica = null;

  /**
   * Este campo será de condición obligatoria, cuando el
   * producto/ servicio este gravado con algún impuesto. Se obtiene de la suma entre el campo "Subtotal", más el impuesto selectivo de consumo (02), el Impuesto específico de Bebidas Alcohólicas (04), el Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de
   * tocador (05) y el impuesto al cemento (12), cuando corresponda.
   * Este campo se podrá editar cuando se seleccione en el campo "IVA cobrado a nivel de fábrica" el Código 01 o en el campo de "Código del impuesto" el código 07.
   *
   * @var float $baseImponible
   */
  private $baseImponible = null;

  /**
   * Cuando el producto o servicio este gravado con algún impuesto se debe indicar cada uno de ellos.
   *
   * @var \App\Models\Hacienda\ImpuestoType[] $impuesto
   */
  private $impuesto = [];

  /**
   * Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica
   *
   * @var float $impuestoAsumidoEmisorFabrica
   */
  private $impuestoAsumidoEmisorFabrica = null;

  /**
   * Este monto se obtiene al restar el campo “Monto del Impuesto” menos “Monto del Impuesto Exonerado” o el
   * campo “Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica” cuando corresponda
   *
   * @var float $impuestoNeto
   */
  private $impuestoNeto = null;

  /**
   * Se calcula de la siguiente manera:
   * se obtiene de la sumatoria de los campos “Subtotal”, “Impuesto Neto”.
   *
   * @var float $montoTotalLinea
   */
  private $montoTotalLinea = null;

  /**
   * Constructor para inicializar el detalle.
   */
  public function __construct(TransactionLine $line, int $numeroLinea)
  {
    $this->setNumeroLinea($numeroLinea);
    $this->setCodigoCABYS($line->codigocabys);

    //Adicionar el código comercial
    $codigoComercial = new CodigoType($line);
    $this->addToCodigoComercial($codigoComercial);

    $this->setCantidad($line->quantity);

    $this->setUnidadMedida($line->product->unitType->code);

    //01 ver Nota 22 Venta Normal de Bienes y Servicios (Transacción General)
    $this->setTipoTransaccion('01');

    $this->setDetalle($line->detail);

    $business = Session::get('user.business');
    if (!$business) {
      $business = Business::find(1);
    }
    if ($business->registro_medicamento && $business->forma_farmaceutica && $this->itemIsMedicine($this->getCodigoCABYS())) {
      $this->setRegistroMedicamento($business->registro_medicamento);
      $this->setFormaFarmaceutica($business->forma_farmaceutica);
    }

    $this->setPrecioUnitario($line->price);

    // Adicionar los descuentos
    if (!empty($line->discounts)) {
      foreach ($line->discounts as $discount) {
        $descuentoType = new DescuentoType($discount);
        $this->addToDescuento($descuentoType);
      }
    }

    $this->setSubTotal($line->subtotal);

    $this->setMontoTotal($line->getMontoTotal());

    //Este campo será de condición obligatoria, cuando el producto/ servicio este gravado con algún impuesto. Se
    //obtiene de la suma entre el campo “Subtotal”, más el impuesto selectivo de consumo (02), el Impuesto específico
    //de Bebidas Alcohólicas (04), el Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y
    //jabones de tocador (05) y el impuesto al cemento (12), cuando corresponda.
    //Este campo se podrá editar cuando se seleccione en el campo “IVA cobrado a nivel de fábrica” el Código 01 o en el
    //campo de “Código del impuesto” el código 07. Validación: En caso de utilizarse el código 01, en el campo
    //de IVA cobrado a nivel de fábrica, se verificará que este campo se encuentre en el comprobante, y deberá
    //incluirse un valor mayor a “cero”. Caso contrario se rechazará
    $this->setBaseImponible($line->baseImponible);

    // Adicionar los impuestos
    if (!empty($line->taxes)) {
      foreach ($line->taxes as $tax) {
        $impuestoType = new ImpuestoType($tax, $this->getSubTotal());
        $this->addToImpuesto($impuestoType);
      }
    }

    $this->setImpuestoAsumidoEmisorFabrica($line->impuestoAsumidoEmisorFabrica);

    $this->setImpuestoNeto($line->impuestoNeto);


    $this->setMontoTotalLinea($line->total);
  }

  /**
   * Gets as numeroLinea
   *
   * Número de línea del detalle
   *
   * @return int
   */
  public function getNumeroLinea()
  {
    return $this->numeroLinea;
  }

  /**
   * Sets a new numeroLinea
   *
   * Número de línea del detalle
   *
   * @param int $numeroLinea
   * @return self
   */
  public function setNumeroLinea($numeroLinea)
  {
    $this->numeroLinea = $numeroLinea;
    return $this;
  }

  /**
   * Gets as codigoCABYS
   *
   * Código de Producto/servicio
   *
   * @return string
   */
  public function getCodigoCABYS()
  {
    return $this->codigoCABYS;
  }

  /**
   * Sets a new codigoCABYS
   *
   * Código de Producto/servicio
   *
   * @param string $codigoCABYS
   * @return self
   */
  public function setCodigoCABYS($codigoCABYS)
  {
    $this->codigoCABYS = $codigoCABYS;
    return $this;
  }

  /**
   * Adds as codigoComercial
   *
   * @return self
   * @param \App\Models\Hacienda\CodigoType $codigoComercial
   */
  public function addToCodigoComercial(\App\Models\Hacienda\CodigoType $codigoComercial)
  {
    $this->codigoComercial[] = $codigoComercial;
    return $this;
  }

  /**
   * isset codigoComercial
   *
   * @param int|string $index
   * @return bool
   */
  public function issetCodigoComercial($index)
  {
    return isset($this->codigoComercial[$index]);
  }

  /**
   * unset codigoComercial
   *
   * @param int|string $index
   * @return void
   */
  public function unsetCodigoComercial($index)
  {
    unset($this->codigoComercial[$index]);
  }

  /**
   * Gets as codigoComercial
   *
   * @return \App\Models\Hacienda\CodigoType[]
   */
  public function getCodigoComercial()
  {
    return $this->codigoComercial;
  }

  /**
   * Sets a new codigoComercial
   *
   * @param \App\Models\Hacienda\CodigoType[] $codigoComercial
   * @return self
   */
  public function setCodigoComercial(array $codigoComercial = null)
  {
    $this->codigoComercial = $codigoComercial;
    return $this;
  }

  /**
   * Gets as cantidad
   *
   * Cantidad
   *
   * @return float
   */
  public function getCantidad()
  {
    return $this->cantidad;
  }

  /**
   * Sets a new cantidad
   *
   * Cantidad
   *
   * @param float $cantidad
   * @return self
   */
  public function setCantidad($cantidad)
  {
    $this->cantidad = $cantidad;
    return $this;
  }

  /**
   * Gets as unidadMedida
   *
   * Unidad de medida
   *
   * @return string
   */
  public function getUnidadMedida()
  {
    return $this->unidadMedida;
  }

  /**
   * Sets a new unidadMedida
   *
   * Unidad de medida
   *
   * @param string $unidadMedida
   * @return self
   */
  public function setUnidadMedida($unidadMedida)
  {
    $this->unidadMedida = $unidadMedida;
    return $this;
  }

  /**
   * Gets as tipoTransaccion
   *
   * Este campo se utilizará para identificar el tipo de transacción
   * que se realizará.
   *
   * @return string
   */
  public function getTipoTransaccion()
  {
    return $this->tipoTransaccion;
  }

  /**
   * Sets a new tipoTransaccion
   *
   * Este campo se utilizará para identificar el tipo de transacción
   * que se realizará.
   *
   * @param string $tipoTransaccion
   * @return self
   */
  public function setTipoTransaccion($tipoTransaccion)
  {
    $this->tipoTransaccion = $tipoTransaccion;
    return $this;
  }

  /**
   * Gets as unidadMedidaComercial
   *
   * Unidad de medida comercial
   *
   * @return string
   */
  public function getUnidadMedidaComercial()
  {
    return $this->unidadMedidaComercial;
  }

  /**
   * Sets a new unidadMedidaComercial
   *
   * Unidad de medida comercial
   *
   * @param string $unidadMedidaComercial
   * @return self
   */
  public function setUnidadMedidaComercial($unidadMedidaComercial)
  {
    $this->unidadMedidaComercial = $unidadMedidaComercial;
    return $this;
  }

  /**
   * Gets as detalle
   *
   * Detalle de la mercancia transferida o servicio prestado
   *
   * @return string
   */
  public function getDetalle()
  {
    return $this->detalle;
  }

  /**
   * Sets a new detalle
   *
   * Detalle de la mercancia transferida o servicio prestado
   *
   * @param string $detalle
   * @return self
   */
  public function setDetalle($detalle)
  {
    $this->detalle = $detalle;
    return $this;
  }

  /**
   * Adds as numeroVINoSerie
   *
   * Número de VIN o Serie
   * del medio de transporte
   *
   * @return self
   * @param string $numeroVINoSerie
   */
  public function addToNumeroVINoSerie($numeroVINoSerie)
  {
    $this->numeroVINoSerie[] = $numeroVINoSerie;
    return $this;
  }

  /**
   * isset numeroVINoSerie
   *
   * Número de VIN o Serie
   * del medio de transporte
   *
   * @param int|string $index
   * @return bool
   */
  public function issetNumeroVINoSerie($index)
  {
    return isset($this->numeroVINoSerie[$index]);
  }

  /**
   * unset numeroVINoSerie
   *
   * Número de VIN o Serie
   * del medio de transporte
   *
   * @param int|string $index
   * @return void
   */
  public function unsetNumeroVINoSerie($index)
  {
    unset($this->numeroVINoSerie[$index]);
  }

  /**
   * Gets as numeroVINoSerie
   *
   * Número de VIN o Serie
   * del medio de transporte
   *
   * @return string[]
   */
  public function getNumeroVINoSerie()
  {
    return $this->numeroVINoSerie;
  }

  /**
   * Sets a new numeroVINoSerie
   *
   * Número de VIN o Serie
   * del medio de transporte
   *
   * @param string $numeroVINoSerie
   * @return self
   */
  public function setNumeroVINoSerie(array $numeroVINoSerie = null)
  {
    $this->numeroVINoSerie = $numeroVINoSerie;
    return $this;
  }

  /**
   * Gets as registroMedicamento
   *
   * Se refiere al respectivo número de registro del Ministerio de Salud
   *
   * @return string
   */
  public function getRegistroMedicamento()
  {
    return $this->registroMedicamento;
  }

  /**
   * Sets a new registroMedicamento
   *
   * Se refiere al respectivo número de registro del Ministerio de Salud
   *
   * @param string $registroMedicamento
   * @return self
   */
  public function setRegistroMedicamento($registroMedicamento)
  {
    $this->registroMedicamento = $registroMedicamento;
    return $this;
  }

  /**
   * Gets as formaFarmaceutica
   *
   * Código de la presentación del medicamento.
   *
   * @return string
   */
  public function getFormaFarmaceutica()
  {
    return $this->formaFarmaceutica;
  }

  /**
   * Sets a new formaFarmaceutica
   *
   * Código de la presentación del medicamento.
   *
   * @param string $formaFarmaceutica
   * @return self
   */
  public function setFormaFarmaceutica($formaFarmaceutica)
  {
    $this->formaFarmaceutica = $formaFarmaceutica;
    return $this;
  }

  /**
   * Adds as lineaDetalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle de los componentes de un surtido, paquete o combinación de productos. Se debe utilizar exclusivamente cuando en la línea de detalle se está facturando un paquete, surtido o combo, entendido como la combinación de más de dos productos con diferentes códigos de producto/servicio.
   *
   * @return self
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType $lineaDetalleSurtido
   */
  public function addToDetalleSurtido(\App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType $lineaDetalleSurtido)
  {
    $this->detalleSurtido[] = $lineaDetalleSurtido;
    return $this;
  }

  /**
   * isset detalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle de los componentes de un surtido, paquete o combinación de productos. Se debe utilizar exclusivamente cuando en la línea de detalle se está facturando un paquete, surtido o combo, entendido como la combinación de más de dos productos con diferentes códigos de producto/servicio.
   *
   * @param int|string $index
   * @return bool
   */
  public function issetDetalleSurtido($index)
  {
    return isset($this->detalleSurtido[$index]);
  }

  /**
   * unset detalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle de los componentes de un surtido, paquete o combinación de productos. Se debe utilizar exclusivamente cuando en la línea de detalle se está facturando un paquete, surtido o combo, entendido como la combinación de más de dos productos con diferentes códigos de producto/servicio.
   *
   * @param int|string $index
   * @return void
   */
  public function unsetDetalleSurtido($index)
  {
    unset($this->detalleSurtido[$index]);
  }

  /**
   * Gets as detalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle de los componentes de un surtido, paquete o combinación de productos. Se debe utilizar exclusivamente cuando en la línea de detalle se está facturando un paquete, surtido o combo, entendido como la combinación de más de dos productos con diferentes códigos de producto/servicio.
   *
   * @return \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType[]
   */
  public function getDetalleSurtido()
  {
    return $this->detalleSurtido;
  }

  /**
   * Sets a new detalleSurtido
   *
   * Tipo complejo que representa cada línea del detalle de los componentes de un surtido, paquete o combinación de productos. Se debe utilizar exclusivamente cuando en la línea de detalle se está facturando un paquete, surtido o combo, entendido como la combinación de más de dos productos con diferentes códigos de producto/servicio.
   *
   * @param \App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType\DetalleServicioAType\LineaDetalleAType\DetalleSurtidoAType\LineaDetalleSurtidoAType[] $detalleSurtido
   * @return self
   */
  public function setDetalleSurtido(array $detalleSurtido = null)
  {
    $this->detalleSurtido = $detalleSurtido;
    return $this;
  }

  /**
   * Gets as precioUnitario
   *
   * Precio Unitario
   *
   * @return float
   */
  public function getPrecioUnitario()
  {
    return $this->precioUnitario;
  }

  /**
   * Sets a new precioUnitario
   *
   * Precio Unitario
   *
   * @param float $precioUnitario
   * @return self
   */
  public function setPrecioUnitario($precioUnitario)
  {
    $this->precioUnitario = $precioUnitario;
    return $this;
  }

  /**
   * Gets as montoTotal
   *
   * Se obtiene de multiplicar el campo cantidad por el campo precio unitario
   *
   * @return float
   */
  public function getMontoTotal()
  {
    return $this->montoTotal;
  }

  /**
   * Sets a new montoTotal
   *
   * Se obtiene de multiplicar el campo cantidad por el campo precio unitario
   *
   * @param float $montoTotal
   * @return self
   */
  public function setMontoTotal($montoTotal)
  {
    $this->montoTotal = $montoTotal;
    return $this;
  }

  /**
   * Adds as descuento
   *
   * @return self
   * @param \App\Models\Hacienda\DescuentoType $descuento
   */
  public function addToDescuento(\App\Models\Hacienda\DescuentoType $descuento)
  {
    $this->descuento[] = $descuento;
    return $this;
  }

  /**
   * isset descuento
   *
   * @param int|string $index
   * @return bool
   */
  public function issetDescuento($index)
  {
    return isset($this->descuento[$index]);
  }

  /**
   * unset descuento
   *
   * @param int|string $index
   * @return void
   */
  public function unsetDescuento($index)
  {
    unset($this->descuento[$index]);
  }

  /**
   * Gets as descuento
   *
   * @return \App\Models\Hacienda\DescuentoType[]
   */
  public function getDescuento()
  {
    return $this->descuento;
  }

  /**
   * Sets a new descuento
   *
   * @param \App\Models\Hacienda\DescuentoType[] $descuento
   * @return self
   */
  public function setDescuento(array $descuento = null)
  {
    $this->descuento = $descuento;
    return $this;
  }

  /**
   * Gets as subTotal
   *
   * Se obtiene de la resta del campo monto total menos monto de descuento concedido
   *
   * @return float
   */
  public function getSubTotal()
  {
    return $this->subTotal;
  }

  /**
   * Sets a new subTotal
   *
   * Se obtiene de la resta del campo monto total menos monto de descuento concedido
   *
   * @param float $subTotal
   * @return self
   */
  public function setSubTotal($subTotal)
  {
    $this->subTotal = $subTotal;
    return $this;
  }

  /**
   * Gets as iVACobradoFabrica
   *
   * En este campo se indicará si el Impuesto al Valor Agregado fue cobrado a nivel de fábrica, por lo que deberá ser utilizado únicamente por los obligados tributarios a realizar el pago de
   * esta forma. Se convierte en obligatorio cuando el IVA se cobra o se cobró a nivel de fábrica. Al hacer uso del presente campo el producto se entenderá exento para el código 02, por lo cual no deberá llenar el subnodo de impuestos para el cálculo del IVA. Para el código 01 el emisor puede separar los impuestos
   * que está cobrando en la fábrica.
   *
   * @return string
   */
  public function getIVACobradoFabrica()
  {
    return $this->iVACobradoFabrica;
  }

  /**
   * Sets a new iVACobradoFabrica
   *
   * En este campo se indicará si el Impuesto al Valor Agregado fue cobrado a nivel de fábrica, por lo que deberá ser utilizado únicamente por los obligados tributarios a realizar el pago de
   * esta forma. Se convierte en obligatorio cuando el IVA se cobra o se cobró a nivel de fábrica. Al hacer uso del presente campo el producto se entenderá exento para el código 02, por lo cual no deberá llenar el subnodo de impuestos para el cálculo del IVA. Para el código 01 el emisor puede separar los impuestos
   * que está cobrando en la fábrica.
   *
   * @param string $iVACobradoFabrica
   * @return self
   */
  public function setIVACobradoFabrica($iVACobradoFabrica)
  {
    $this->iVACobradoFabrica = $iVACobradoFabrica;
    return $this;
  }

  /**
   * Gets as baseImponible
   *
   * Este campo será de condición obligatoria, cuando el
   * producto/ servicio este gravado con algún impuesto. Se obtiene de la suma entre el campo "Subtotal", más el impuesto selectivo de consumo (02), el Impuesto específico de Bebidas Alcohólicas (04), el Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de
   * tocador (05) y el impuesto al cemento (12), cuando corresponda.
   * Este campo se podrá editar cuando se seleccione en el campo "IVA cobrado a nivel de fábrica" el Código 01 o en el campo de "Código del impuesto" el código 07.
   *
   * @return float
   */
  public function getBaseImponible()
  {
    return $this->baseImponible;
  }

  /**
   * Sets a new baseImponible
   *
   * Este campo será de condición obligatoria, cuando el
   * producto/ servicio este gravado con algún impuesto. Se obtiene de la suma entre el campo "Subtotal", más el impuesto selectivo de consumo (02), el Impuesto específico de Bebidas Alcohólicas (04), el Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de
   * tocador (05) y el impuesto al cemento (12), cuando corresponda.
   * Este campo se podrá editar cuando se seleccione en el campo "IVA cobrado a nivel de fábrica" el Código 01 o en el campo de "Código del impuesto" el código 07.
   *
   * @param float $baseImponible
   * @return self
   */
  public function setBaseImponible($baseImponible)
  {
    $this->baseImponible = $baseImponible;
    return $this;
  }

  /**
   * Adds as impuesto
   *
   * Cuando el producto o servicio este gravado con algún impuesto se debe indicar cada uno de ellos.
   *
   * @return self
   * @param \App\Models\Hacienda\ImpuestoType $impuesto
   */
  public function addToImpuesto(\App\Models\Hacienda\ImpuestoType $impuesto)
  {
    $this->impuesto[] = $impuesto;
    return $this;
  }

  /**
   * isset impuesto
   *
   * Cuando el producto o servicio este gravado con algún impuesto se debe indicar cada uno de ellos.
   *
   * @param int|string $index
   * @return bool
   */
  public function issetImpuesto($index)
  {
    return isset($this->impuesto[$index]);
  }

  /**
   * unset impuesto
   *
   * Cuando el producto o servicio este gravado con algún impuesto se debe indicar cada uno de ellos.
   *
   * @param int|string $index
   * @return void
   */
  public function unsetImpuesto($index)
  {
    unset($this->impuesto[$index]);
  }

  /**
   * Gets as impuesto
   *
   * Cuando el producto o servicio este gravado con algún impuesto se debe indicar cada uno de ellos.
   *
   * @return \App\Models\Hacienda\ImpuestoType[]
   */
  public function getImpuesto()
  {
    return $this->impuesto;
  }

  /**
   * Sets a new impuesto
   *
   * Cuando el producto o servicio este gravado con algún impuesto se debe indicar cada uno de ellos.
   *
   * @param \App\Models\Hacienda\ImpuestoType[] $impuesto
   * @return self
   */
  public function setImpuesto(array $impuesto)
  {
    $this->impuesto = $impuesto;
    return $this;
  }

  /**
   * Gets as impuestoAsumidoEmisorFabrica
   *
   * Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica
   *
   * @return float
   */
  public function getImpuestoAsumidoEmisorFabrica()
  {
    return $this->impuestoAsumidoEmisorFabrica;
  }

  /**
   * Sets a new impuestoAsumidoEmisorFabrica
   *
   * Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica
   *
   * @param float $impuestoAsumidoEmisorFabrica
   * @return self
   */
  public function setImpuestoAsumidoEmisorFabrica($impuestoAsumidoEmisorFabrica)
  {
    $this->impuestoAsumidoEmisorFabrica = $impuestoAsumidoEmisorFabrica;
    return $this;
  }

  /**
   * Gets as impuestoNeto
   *
   * Este monto se obtiene al restar el campo “Monto del Impuesto” menos “Monto del Impuesto Exonerado” o el
   * campo “Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica” cuando corresponda
   *
   * @return float
   */
  public function getImpuestoNeto()
  {
    return $this->impuestoNeto;
  }

  /**
   * Sets a new impuestoNeto
   *
   * Este monto se obtiene al restar el campo “Monto del Impuesto” menos “Monto del Impuesto Exonerado” o el
   * campo “Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica” cuando corresponda
   *
   * @param float $impuestoNeto
   * @return self
   */
  public function setImpuestoNeto($impuestoNeto)
  {
    $this->impuestoNeto = $impuestoNeto;
    return $this;
  }

  /**
   * Gets as montoTotalLinea
   *
   * Se calcula de la siguiente manera:
   * se obtiene de la sumatoria de los campos “Subtotal”, “Impuesto Neto”.
   *
   * @return float
   */
  public function getMontoTotalLinea()
  {
    return $this->montoTotalLinea;
  }

  /**
   * Sets a new montoTotalLinea
   *
   * Se calcula de la siguiente manera:
   * se obtiene de la sumatoria de los campos “Subtotal”, “Impuesto Neto”.
   *
   * @param float $montoTotalLinea
   * @return self
   */
  public function setMontoTotalLinea($montoTotalLinea)
  {
    $this->montoTotalLinea = $montoTotalLinea;
    return $this;
  }

  private function itemIsMedicine($codigocabys)
  {
    // Obtener los primeros 3 caracteres
    $primerosTres = substr($codigocabys, 0, 3);

    // Lista de valores permitidos
    $valoresPermitidos = ['356'];

    // Retorna true si está en la lista
    return in_array($primerosTres, $valoresPermitidos);
  }
}
