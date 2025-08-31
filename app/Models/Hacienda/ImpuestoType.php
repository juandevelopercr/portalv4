<?php

namespace App\Models\Hacienda;

use App\Models\TransactionLineTax;
use App\Models\Hacienda\ImpuestoType\DatosImpuestoEspecificoAType;

/**
 * Class representing ImpuestoType
 *
 *
 * Hacienda Type: ImpuestoType
 */
class ImpuestoType
{
  /**
   * Código del impuesto: 01 Impuesto al valor agregado, 02 Impuesto Selectivo de Consumo, 03 Impuesto único a los combustivos, 04 Impuesto específico de bebidas alcohólicas, 05 Impuesto específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador, 06 Impuesto a los productos de tabaco, 07 IVA (cálculo especial), 08 IVA Regimen de Bienes Usados (Factor), 12 Impuesto Especifico al cemento, 99 Otros
   *
   * @var string $codigo
   */
  private $codigo = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 8. Se debe describir puntualmente el impuesto
   * utilizado
   *
   * @var string $codigoImpuestoOTRO
   */
  private $codigoImpuestoOTRO = null;

  /**
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este
   * campo, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de los componentes del surtido que se deben incluir en estos
   * casos. La eventual validación de la consistencia de los impuestos calculados y aplicación de tarifas se hará sobre las líneas individuales de detalle.
   *
   * @var string $codigoTarifaIVA
   */
  private $codigoTarifaIVA = null;

  /**
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este campo, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de componentes del surtido que se deben incluir en estos casos. La eventual validación de la consistencia de los impuestos calculados y aplicación de tarifas se hará sobre las líneas individuales de detalle.
   *
   * @var float $tarifa
   */
  private $tarifa = null;

  /**
   * Este campo es de condición obligatoria, cuando el producto/servicio posea un factor para su cálculo.
   * Cuando en el código de impuesto se defina IVA Bienes Usados se deberá utilizar este campo con el factor establecido por el Ministerio de Hacienda
   *
   * @var float $factorCalculoIVA
   */
  private $factorCalculoIVA = null;

  /**
   * Tipo complejo con el detalle para calcular impuestos específicos no tarifarios. Este campo es de condición obligatoria, cuando se utilicen
   * los códigos de impuesto 03, 04, 05, 06 de la nota 8 y agrupará los campos requeridos para el cálculo de estos impuestos
   *
   * @var \App\Models\Hacienda\ImpuestoType\DatosImpuestoEspecificoAType $datosImpuestoEspecifico
   */
  private $datosImpuestoEspecifico = null;

  /**
   * Monto del impuesto
   *
   * @var float $monto
   */
  private $monto = null;

  /**
   * @var \App\Models\Hacienda\ExoneracionType $exoneracion
   */
  private $exoneracion = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(TransactionLineTax $tax, $subtotal)
  {
    if (!is_null($tax->taxType) && !empty($tax->taxType->code))
      $this->setCodigo($tax->taxType->code);

    if (!is_null($tax->tax_type_other) && !empty($tax->tax_type_other))
      $this->setCodigoImpuestoOTRO($tax->tax_type_other);

    if (!is_null($tax->taxRate) && !empty($tax->taxRate->code))
      $this->setCodigoTarifaIVA($tax->taxRate->code);

    if (!is_null($tax->taxRate) && !empty($tax->taxRate->code))
      $this->setTarifa($tax->tax);

    if (!is_null($tax->factor_calculo_tax) && !empty($tax->factor_calculo_tax))
      $this->setFactorCalculoIVA($tax->factor_calculo_tax);

    if (!is_null($tax->taxType) && !empty($tax->taxType->code) && in_array($tax->taxType->code, ['03', '04', '05', '06'])) {
      $datosImpuestoEspecifico = new DatosImpuestoEspecificoAType($tax);
      $this->setDatosImpuestoEspecifico($datosImpuestoEspecifico);
    }

    $this->setMonto($tax->tax_amount);

    if (!is_null($tax->exoneration_type_id) && !is_null($tax->exoneration_doc) && !is_null($tax->exoneration_institution_id)) {
      $exoneracionType = new ExoneracionType($tax, $subtotal);
      $this->setExoneracion($exoneracionType);
    }
  }

  /**
   * Gets as codigo
   *
   * Código del impuesto: 01 Impuesto al valor agregado, 02 Impuesto Selectivo de Consumo, 03 Impuesto único a los combustivos, 04 Impuesto específico de bebidas alcohólicas, 05 Impuesto específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador, 06 Impuesto a los productos de tabaco, 07 IVA (cálculo especial), 08 IVA Regimen de Bienes Usados (Factor), 12 Impuesto Especifico al cemento, 99 Otros
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
   * Código del impuesto: 01 Impuesto al valor agregado, 02 Impuesto Selectivo de Consumo, 03 Impuesto único a los combustivos, 04 Impuesto específico de bebidas alcohólicas, 05 Impuesto específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador, 06 Impuesto a los productos de tabaco, 07 IVA (cálculo especial), 08 IVA Regimen de Bienes Usados (Factor), 12 Impuesto Especifico al cemento, 99 Otros
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
   * Gets as codigoImpuestoOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 8. Se debe describir puntualmente el impuesto
   * utilizado
   *
   * @return string
   */
  public function getCodigoImpuestoOTRO()
  {
    return $this->codigoImpuestoOTRO;
  }

  /**
   * Sets a new codigoImpuestoOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 8. Se debe describir puntualmente el impuesto
   * utilizado
   *
   * @param string $codigoImpuestoOTRO
   * @return self
   */
  public function setCodigoImpuestoOTRO($codigoImpuestoOTRO)
  {
    $this->codigoImpuestoOTRO = $codigoImpuestoOTRO;
    return $this;
  }

  /**
   * Gets as codigoTarifaIVA
   *
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este
   * campo, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de los componentes del surtido que se deben incluir en estos
   * casos. La eventual validación de la consistencia de los impuestos calculados y aplicación de tarifas se hará sobre las líneas individuales de detalle.
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
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este
   * campo, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de los componentes del surtido que se deben incluir en estos
   * casos. La eventual validación de la consistencia de los impuestos calculados y aplicación de tarifas se hará sobre las líneas individuales de detalle.
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
   * Gets as tarifa
   *
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este campo, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de componentes del surtido que se deben incluir en estos casos. La eventual validación de la consistencia de los impuestos calculados y aplicación de tarifas se hará sobre las líneas individuales de detalle.
   *
   * @return float
   */
  public function getTarifa()
  {
    return $this->tarifa;
  }

  /**
   * Sets a new tarifa
   *
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este campo, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de componentes del surtido que se deben incluir en estos casos. La eventual validación de la consistencia de los impuestos calculados y aplicación de tarifas se hará sobre las líneas individuales de detalle.
   *
   * @param float $tarifa
   * @return self
   */
  public function setTarifa($tarifa)
  {
    $this->tarifa = $tarifa;
    return $this;
  }

  /**
   * Gets as factorCalculoIVA
   *
   * Este campo es de condición obligatoria, cuando el producto/servicio posea un factor para su cálculo.
   * Cuando en el código de impuesto se defina IVA Bienes Usados se deberá utilizar este campo con el factor establecido por el Ministerio de Hacienda
   *
   * @return float
   */
  public function getFactorCalculoIVA()
  {
    return $this->factorCalculoIVA;
  }

  /**
   * Sets a new factorCalculoIVA
   *
   * Este campo es de condición obligatoria, cuando el producto/servicio posea un factor para su cálculo.
   * Cuando en el código de impuesto se defina IVA Bienes Usados se deberá utilizar este campo con el factor establecido por el Ministerio de Hacienda
   *
   * @param float $factorCalculoIVA
   * @return self
   */
  public function setFactorCalculoIVA($factorCalculoIVA)
  {
    $this->factorCalculoIVA = $factorCalculoIVA;
    return $this;
  }

  /**
   * Gets as datosImpuestoEspecifico
   *
   * Tipo complejo con el detalle para calcular impuestos específicos no tarifarios. Este campo es de condición obligatoria, cuando se utilicen
   * los códigos de impuesto 03, 04, 05, 06 de la nota 8 y agrupará los campos requeridos para el cálculo de estos impuestos
   *
   * @return \App\Models\Hacienda\ImpuestoType\DatosImpuestoEspecificoAType
   */
  public function getDatosImpuestoEspecifico()
  {
    return $this->datosImpuestoEspecifico;
  }

  /**
   * Sets a new datosImpuestoEspecifico
   *
   * Tipo complejo con el detalle para calcular impuestos específicos no tarifarios. Este campo es de condición obligatoria, cuando se utilicen
   * los códigos de impuesto 03, 04, 05, 06 de la nota 8 y agrupará los campos requeridos para el cálculo de estos impuestos
   *
   * @param \App\Models\Hacienda\ImpuestoType\DatosImpuestoEspecificoAType $datosImpuestoEspecifico
   * @return self
   */
  public function setDatosImpuestoEspecifico(?\App\Models\Hacienda\ImpuestoType\DatosImpuestoEspecificoAType $datosImpuestoEspecifico = null)
  {
    $this->datosImpuestoEspecifico = $datosImpuestoEspecifico;
    return $this;
  }

  /**
   * Gets as monto
   *
   * Monto del impuesto
   *
   * @return float
   */
  public function getMonto()
  {
    return $this->monto;
  }

  /**
   * Sets a new monto
   *
   * Monto del impuesto
   *
   * @param float $monto
   * @return self
   */
  public function setMonto($monto)
  {
    $this->monto = $monto;
    return $this;
  }

  /**
   * Gets as exoneracion
   *
   * @return \App\Models\Hacienda\ExoneracionType
   */
  public function getExoneracion()
  {
    return $this->exoneracion;
  }

  /**
   * Sets a new exoneracion
   *
   * @param \App\Models\Hacienda\ExoneracionType $exoneracion
   * @return self
   */
  public function setExoneracion(?\App\Models\Hacienda\ExoneracionType $exoneracion = null)
  {
    $this->exoneracion = $exoneracion;
    return $this;
  }
}
