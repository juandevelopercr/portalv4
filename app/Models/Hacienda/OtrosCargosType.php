<?php

namespace App\Models\Hacienda;

use App\Models\TransactionOtherCharge;

/**
 * Class representing OtrosCargosType
 *
 *
 * Hacienda Type: OtrosCargosType
 */
class OtrosCargosType
{
  /**
   * Se verificará el cumplimiento de la nota 16.
   * Además, cuando se seleccione el código 04, 08, 09 y 10 de la nota 16 en “Tipo de documento otros cargos” y no se cuente con una línea de servicio o producto, no es obligatorio usar el nodo “Detalle de la mercancía o servicio prestado”.
   *
   * @var string $tipoDocumentoOC
   */
  private $tipoDocumentoOC = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 16. Se debe describir puntualmente el tipo de documento utilizado
   *
   * @var string $tipoDocumentoOTROS
   */
  private $tipoDocumentoOTROS = null;

  /**
   * @var \App\Models\Hacienda\IdentificacionType $identificacionTercero
   */
  private $identificacionTercero = null;

  /**
   * Nombre o razón social del receptor
   *
   * @var string $nombreTercero
   */
  private $nombreTercero = null;

  /**
   * Detalle de otros cargos
   *
   * @var string $detalle
   */
  private $detalle = null;

  /**
   * En el caso que el cargo posea un porcentaje o monto para su cálculo se debe de indicar el mismo
   *
   * @var float $porcentajeOC
   */
  private $porcentajeOC = null;

  /**
   * Monto del cargo
   *
   * @var float $montoCargo
   */
  private $montoCargo = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(TransactionOtherCharge $otrosCargo)
  {
    if (!is_null($otrosCargo->additionalChargeType)) {
      $this->setTipoDocumentoOC($otrosCargo->additionalChargeType->code);

      if (!is_null($otrosCargo->additional_charge_other) && $otrosCargo->additionalChargeType->code == '99')
        $this->setTipoDocumentoOTROS($otrosCargo->additional_charge_other);
    }

    if ($otrosCargo->additionalChargeType->code == '04') {
      $identification = new IdentificacionType();
      $identification->setTipo($otrosCargo->third_party_identification_type);
      $identification->setNumero($otrosCargo->third_party_identification);
      $this->setIdentificacionTercero($identification);

      $this->setNombreTercero($otrosCargo->third_party_name);
    }

    $this->setDetalle($otrosCargo->detail);

    $this->setPorcentajeOC($otrosCargo->percent);

    $this->setMontoCargo($otrosCargo->amount * $otrosCargo->quantity);
  }


  /**
   * Gets as tipoDocumentoOC
   *
   * Se verificará el cumplimiento de la nota 16.
   * Además, cuando se seleccione el código 04, 08, 09 y 10 de la nota 16 en “Tipo de documento otros cargos” y no se cuente con una línea de servicio o producto, no es obligatorio usar el nodo “Detalle de la mercancía o servicio prestado”.
   *
   * @return string
   */
  public function getTipoDocumentoOC()
  {
    return $this->tipoDocumentoOC;
  }

  /**
   * Sets a new tipoDocumentoOC
   *
   * Se verificará el cumplimiento de la nota 16.
   * Además, cuando se seleccione el código 04, 08, 09 y 10 de la nota 16 en “Tipo de documento otros cargos” y no se cuente con una línea de servicio o producto, no es obligatorio usar el nodo “Detalle de la mercancía o servicio prestado”.
   *
   * @param string $tipoDocumentoOC
   * @return self
   */
  public function setTipoDocumentoOC($tipoDocumentoOC)
  {
    $this->tipoDocumentoOC = $tipoDocumentoOC;
    return $this;
  }

  /**
   * Gets as tipoDocumentoOTROS
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 16. Se debe describir puntualmente el tipo de documento utilizado
   *
   * @return string
   */
  public function getTipoDocumentoOTROS()
  {
    return $this->tipoDocumentoOTROS;
  }

  /**
   * Sets a new tipoDocumentoOTROS
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 16. Se debe describir puntualmente el tipo de documento utilizado
   *
   * @param string $tipoDocumentoOTROS
   * @return self
   */
  public function setTipoDocumentoOTROS($tipoDocumentoOTROS)
  {
    $this->tipoDocumentoOTROS = $tipoDocumentoOTROS;
    return $this;
  }

  /**
   * Gets as identificacionTercero
   *
   * @return \App\Models\Hacienda\IdentificacionType
   */
  public function getIdentificacionTercero()
  {
    return $this->identificacionTercero;
  }

  /**
   * Sets a new identificacionTercero
   *
   * @param \App\Models\Hacienda\IdentificacionType $identificacionTercero
   * @return self
   */
  public function setIdentificacionTercero(?\App\Models\Hacienda\IdentificacionType $identificacionTercero = null)
  {
    $this->identificacionTercero = $identificacionTercero;
    return $this;
  }

  /**
   * Gets as nombreTercero
   *
   * Nombre o razón social del receptor
   *
   * @return string
   */
  public function getNombreTercero()
  {
    return $this->nombreTercero;
  }

  /**
   * Sets a new nombreTercero
   *
   * Nombre o razón social del receptor
   *
   * @param string $nombreTercero
   * @return self
   */
  public function setNombreTercero($nombreTercero)
  {
    $this->nombreTercero = $nombreTercero;
    return $this;
  }

  /**
   * Gets as detalle
   *
   * Detalle de otros cargos
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
   * Detalle de otros cargos
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
   * Gets as porcentajeOC
   *
   * En el caso que el cargo posea un porcentaje o monto para su cálculo se debe de indicar el mismo
   *
   * @return float
   */
  public function getPorcentajeOC()
  {
    return $this->porcentajeOC;
  }

  /**
   * Sets a new porcentajeOC
   *
   * En el caso que el cargo posea un porcentaje o monto para su cálculo se debe de indicar el mismo
   *
   * @param float $porcentajeOC
   * @return self
   */
  public function setPorcentajeOC($porcentajeOC)
  {
    $this->porcentajeOC = $porcentajeOC;
    return $this;
  }

  /**
   * Gets as montoCargo
   *
   * Monto del cargo
   *
   * @return float
   */
  public function getMontoCargo()
  {
    return $this->montoCargo;
  }

  /**
   * Sets a new montoCargo
   *
   * Monto del cargo
   *
   * @param float $montoCargo
   * @return self
   */
  public function setMontoCargo($montoCargo)
  {
    $this->montoCargo = number_format($montoCargo, 5, '.', '');
    return $this;
  }
}
