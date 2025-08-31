<?php

namespace App\Models\Hacienda\ImpuestoType;

use App\Models\TransactionLineTax;

/**
 * Class representing DatosImpuestoEspecificoAType
 */
class DatosImpuestoEspecificoAType
{
  /**
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este
   * campo, para los códigos de impuesto 04, 05, 06 de la nota 8, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de
   * componentes del surtido que se deben incluir en estos casos
   *
   * @var float $cantidadUnidadMedida
   */
  private $cantidadUnidadMedida = null;

  /**
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8
   *
   * @var float $porcentaje
   */
  private $porcentaje = null;

  /**
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8 Este campo se obtiene de multiplicar la “Cantidad de la unidad de medida a utilizar” por el “Porcentaje”
   *
   * @var float $proporcion
   */
  private $proporcion = null;

  /**
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 05 de la nota 8
   *
   * @var float $volumenUnidadConsumo
   */
  private $volumenUnidadConsumo = null;

  /**
   * Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 03, 04, 05 y 06 de la nota 8
   *
   * @var float $impuestoUnidad
   */
  private $impuestoUnidad = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(TransactionLineTax $tax)
  {
    if (!is_null($tax->count_unit_type) && !empty($tax->count_unit_type))
      $this->setCantidadUnidadMedida($tax->taxType->code);

    if (!is_null($tax->percent) && !empty($tax->percent))
      $this->setPorcentaje($tax->percent);

    if (!is_null($tax->proporcion) && !empty($tax->proporcion))
      $this->setProporcion($tax->proporcion);

    if (!is_null($tax->volumen_unidad_consumo) && !empty($tax->volumen_unidad_consumo))
      $this->setVolumenUnidadConsumo($tax->volumen_unidad_consumo);

    if (!is_null($tax->impuesto_unidad) && !empty($tax->impuesto_unidad))
      $this->setImpuestoUnidad($tax->impuesto_unidad);
  }

  /**
   * Gets as cantidadUnidadMedida
   *
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este
   * campo, para los códigos de impuesto 04, 05, 06 de la nota 8, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de
   * componentes del surtido que se deben incluir en estos casos
   *
   * @return float
   */
  public function getCantidadUnidadMedida()
  {
    return $this->cantidadUnidadMedida;
  }

  /**
   * Sets a new cantidadUnidadMedida
   *
   * En el caso que se utilice el nodo “Detalle de productos del surtido, paquetes o combos”, no se deberá utilizar este
   * campo, para los códigos de impuesto 04, 05, 06 de la nota 8, ya que el impuesto se calcula como la suma de los montos de impuestos individuales de las líneas de detalle de
   * componentes del surtido que se deben incluir en estos casos
   *
   * @param float $cantidadUnidadMedida
   * @return self
   */
  public function setCantidadUnidadMedida($cantidadUnidadMedida)
  {
    $this->cantidadUnidadMedida = $cantidadUnidadMedida;
    return $this;
  }

  /**
   * Gets as porcentaje
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8
   *
   * @return float
   */
  public function getPorcentaje()
  {
    return $this->porcentaje;
  }

  /**
   * Sets a new porcentaje
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8
   *
   * @param float $porcentaje
   * @return self
   */
  public function setPorcentaje($porcentaje)
  {
    $this->porcentaje = $porcentaje;
    return $this;
  }

  /**
   * Gets as proporcion
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8 Este campo se obtiene de multiplicar la “Cantidad de la unidad de medida a utilizar” por el “Porcentaje”
   *
   * @return float
   */
  public function getProporcion()
  {
    return $this->proporcion;
  }

  /**
   * Sets a new proporcion
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 04 de la nota 8 Este campo se obtiene de multiplicar la “Cantidad de la unidad de medida a utilizar” por el “Porcentaje”
   *
   * @param float $proporcion
   * @return self
   */
  public function setProporcion($proporcion)
  {
    $this->proporcion = $proporcion;
    return $this;
  }

  /**
   * Gets as volumenUnidadConsumo
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 05 de la nota 8
   *
   * @return float
   */
  public function getVolumenUnidadConsumo()
  {
    return $this->volumenUnidadConsumo;
  }

  /**
   * Sets a new volumenUnidadConsumo
   *
   * Este campo es de condición obligatoria, cuando se utilice el código de impuesto 05 de la nota 8
   *
   * @param float $volumenUnidadConsumo
   * @return self
   */
  public function setVolumenUnidadConsumo($volumenUnidadConsumo)
  {
    $this->volumenUnidadConsumo = $volumenUnidadConsumo;
    return $this;
  }

  /**
   * Gets as impuestoUnidad
   *
   * Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 03, 04, 05 y 06 de la nota 8
   *
   * @return float
   */
  public function getImpuestoUnidad()
  {
    return $this->impuestoUnidad;
  }

  /**
   * Sets a new impuestoUnidad
   *
   * Este campo es de condición obligatoria, cuando se utilicen los códigos de impuesto 03, 04, 05 y 06 de la nota 8
   *
   * @param float $impuestoUnidad
   * @return self
   */
  public function setImpuestoUnidad($impuestoUnidad)
  {
    $this->impuestoUnidad = $impuestoUnidad;
    return $this;
  }
}
