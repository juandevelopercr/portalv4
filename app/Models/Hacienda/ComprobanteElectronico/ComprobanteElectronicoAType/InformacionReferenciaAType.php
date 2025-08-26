<?php

namespace App\Models\Hacienda\ComprobanteElectronico\ComprobanteElectronicoAType;

use App\Models\Transaction;
use Carbon\Carbon;

/**
 * Class representing InformacionReferenciaAType
 */
class InformacionReferenciaAType
{
  /**
   * Tipo de documento de referencia
   *
   * @var string $tipoDocIR
   */
  private $tipoDocIR = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 10. Se debe describir puntualmente el tipo de documento utilizado
   *
   * @var string $tipoDocRefOTRO
   */
  private $tipoDocRefOTRO = null;

  /**
   * Clave numérica del comprobante electrónico o consecutivo del documento de referencia
   *
   * @var string $numero
   */
  private $numero = null;

  /**
   * Fecha de emisión del documento de referencia
   *
   * @var \DateTime $fechaEmisionIR
   */
  private $fechaEmisionIR = null;

  /**
   * Código de referencia. 01 Anula documento de referencia, 02 Corrige texto de documento de referencia, 04 Referencia a otro documento, 05 Sustituye comprobante provisional por contigencia, 99 Otros
   *
   * @var string $codigo
   */
  private $codigo = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 9. Se debe describir puntualmente el código de referencia utilizado
   *
   * @var string $codigoReferenciaOTRO
   */
  private $codigoReferenciaOTRO = null;

  /**
   * Razón de referencia
   *
   * @var string $razon
   */
  private $razon = null;

  /**
   * Constructor para inicializar
   */
  public function __construct(Transaction $transaction)
  {
    //if ($transaction->document_type == 'NCE' || $transaction->document_type == 'NDE'){
    if (in_array($transaction->document_type, ['NCE', 'NDE', 'PRC', 'FEC'])) {
      $this->setTipoDocIR($transaction->RefTipoDoc);
      $this->setTipoDocRefOTRO($transaction->RefTipoDocOtro);
      $this->setNumero($transaction->RefNumero);
      $fechaEmision = Carbon::parse($transaction->RefFechaEmision, 'America/Costa_Rica');
      $this->setFechaEmisionIR($fechaEmision);
      $this->setCodigo($transaction->RefCodigo);
      $this->setCodigoReferenciaOTRO($transaction->RefCodigoOtro);
      $this->setRazon($transaction->RefRazon);
    }
  }

  /**
   * Gets as tipoDocIR
   *
   * Tipo de documento de referencia
   *
   * @return string
   */
  public function getTipoDocIR()
  {
    return $this->tipoDocIR;
  }

  /**
   * Sets a new tipoDocIR
   *
   * Tipo de documento de referencia
   *
   * @param string $tipoDocIR
   * @return self
   */
  public function setTipoDocIR($tipoDocIR)
  {
    $this->tipoDocIR = $tipoDocIR;
    return $this;
  }

  /**
   * Gets as tipoDocRefOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 10. Se debe describir puntualmente el tipo de documento utilizado
   *
   * @return string
   */
  public function getTipoDocRefOTRO()
  {
    return $this->tipoDocRefOTRO;
  }

  /**
   * Sets a new tipoDocRefOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 10. Se debe describir puntualmente el tipo de documento utilizado
   *
   * @param string $tipoDocRefOTRO
   * @return self
   */
  public function setTipoDocRefOTRO($tipoDocRefOTRO)
  {
    $this->tipoDocRefOTRO = $tipoDocRefOTRO;
    return $this;
  }

  /**
   * Gets as numero
   *
   * Clave numérica del comprobante electrónico o consecutivo del documento de referencia
   *
   * @return string
   */
  public function getNumero()
  {
    return $this->numero;
  }

  /**
   * Sets a new numero
   *
   * Clave numérica del comprobante electrónico o consecutivo del documento de referencia
   *
   * @param string $numero
   * @return self
   */
  public function setNumero($numero)
  {
    $this->numero = $numero;
    return $this;
  }

  /**
   * Gets as fechaEmisionIR
   *
   * Fecha de emisión del documento de referencia
   *
   * @return \DateTime
   */
  public function getFechaEmisionIR()
  {
    return $this->fechaEmisionIR;
  }

  /**
   * Sets a new fechaEmisionIR
   *
   * Fecha de emisión del documento de referencia
   *
   * @param \DateTime $fechaEmisionIR
   * @return self
   */
  public function setFechaEmisionIR(\DateTime $fechaEmisionIR)
  {
    $this->fechaEmisionIR = $fechaEmisionIR;
    return $this;
  }

  /**
   * Gets as codigo
   *
   * Código de referencia. 01 Anula documento de referencia, 02 Corrige texto de documento de referencia, 04 Referencia a otro documento, 05 Sustituye comprobante provisional por contigencia, 99 Otros
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
   * Código de referencia. 01 Anula documento de referencia, 02 Corrige texto de documento de referencia, 04 Referencia a otro documento, 05 Sustituye comprobante provisional por contigencia, 99 Otros
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
   * Gets as codigoReferenciaOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 9. Se debe describir puntualmente el código de referencia utilizado
   *
   * @return string
   */
  public function getCodigoReferenciaOTRO()
  {
    return $this->codigoReferenciaOTRO;
  }

  /**
   * Sets a new codigoReferenciaOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 9. Se debe describir puntualmente el código de referencia utilizado
   *
   * @param string $codigoReferenciaOTRO
   * @return self
   */
  public function setCodigoReferenciaOTRO($codigoReferenciaOTRO)
  {
    $this->codigoReferenciaOTRO = $codigoReferenciaOTRO;
    return $this;
  }

  /**
   * Gets as razon
   *
   * Razón de referencia
   *
   * @return string
   */
  public function getRazon()
  {
    return $this->razon;
  }

  /**
   * Sets a new razon
   *
   * Razón de referencia
   *
   * @param string $razon
   * @return self
   */
  public function setRazon($razon)
  {
    $this->razon = $razon;
    return $this;
  }
}
