<?php

namespace App\Models\Hacienda;

use App\Models\TransactionLine;

/**
 * Class representing CodigoType
 *
 *
 * Hacienda Type: CodigoType
 */
class CodigoType
{
  /**
   * Será obligatorio para las líneas de detalle que utilicen uno de
   * los códigos de producto/servicio de "surtidos" que estén
   * habilitados en el CAByS.
   * En el caso de la inclusión de paquetes, surtidos o combos,
   * entendidos como la combinación de más de dos productos
   * con diferentes códigos de producto/servicio, se debe
   * seleccionar el código 03 "Código del producto asignado por la
   * industria" de la nota 12 e incluir en el campo "código" el
   * respectivo código "SKU", GTIN o equivalentes, con el que el
   * paquete este identificado en la industria. Estos códigos deben
   * ser verificables en los catálogos disponibles en la industria.
   *
   * @var string $tipo
   */
  private $tipo = null;

  /**
   * Será obligatorio para las líneas de detalle que utilicen uno de
   * los códigos de producto/servicio de "surtidos" que estén
   * habilitados en el CAByS.
   *
   * @var string $codigo
   */
  private $codigo = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(TransactionLine $line)
  {
    //Ver nota 12  04 Código uso interno
    $this->setTipo('04');
    $this->setCodigo($line->codigo);
  }

  /**
   * Gets as tipo
   *
   * Será obligatorio para las líneas de detalle que utilicen uno de
   * los códigos de producto/servicio de "surtidos" que estén
   * habilitados en el CAByS.
   * En el caso de la inclusión de paquetes, surtidos o combos,
   * entendidos como la combinación de más de dos productos
   * con diferentes códigos de producto/servicio, se debe
   * seleccionar el código 03 "Código del producto asignado por la
   * industria" de la nota 12 e incluir en el campo "código" el
   * respectivo código "SKU", GTIN o equivalentes, con el que el
   * paquete este identificado en la industria. Estos códigos deben
   * ser verificables en los catálogos disponibles en la industria.
   *
   * @return string
   */
  public function getTipo()
  {
    return $this->tipo;
  }

  /**
   * Sets a new tipo
   *
   * Será obligatorio para las líneas de detalle que utilicen uno de
   * los códigos de producto/servicio de "surtidos" que estén
   * habilitados en el CAByS.
   * En el caso de la inclusión de paquetes, surtidos o combos,
   * entendidos como la combinación de más de dos productos
   * con diferentes códigos de producto/servicio, se debe
   * seleccionar el código 03 "Código del producto asignado por la
   * industria" de la nota 12 e incluir en el campo "código" el
   * respectivo código "SKU", GTIN o equivalentes, con el que el
   * paquete este identificado en la industria. Estos códigos deben
   * ser verificables en los catálogos disponibles en la industria.
   *
   * @param string $tipo
   * @return self
   */
  public function setTipo($tipo)
  {
    $this->tipo = $tipo;
    return $this;
  }

  /**
   * Gets as codigo
   *
   * Será obligatorio para las líneas de detalle que utilicen uno de
   * los códigos de producto/servicio de "surtidos" que estén
   * habilitados en el CAByS.
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
   * Será obligatorio para las líneas de detalle que utilicen uno de
   * los códigos de producto/servicio de "surtidos" que estén
   * habilitados en el CAByS.
   *
   * @param string $codigo
   * @return self
   */
  public function setCodigo($codigo)
  {
    $this->codigo = $codigo;
    return $this;
  }
}
