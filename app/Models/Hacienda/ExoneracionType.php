<?php

namespace App\Models\Hacienda;

use App\Models\TransactionLineTax;
use Carbon\Carbon;

/**
 * Class representing ExoneracionType
 *
 *
 * Hacienda Type: ExoneracionType
 */
class ExoneracionType
{
  /**
   * Tipo de documento de exoneración o autorización.
   *
   * @var string $tipoDocumentoEX1
   */
  private $tipoDocumentoEX1 = null;

  /**
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 10.1. Se debe describir puntualmente el tipo de documento o autorización utilizado
   *
   * @var string $tipoDocumentoOTRO
   */
  private $tipoDocumentoOTRO = null;

  /**
   * Número de documento de exoneración o autorización
   *
   * @var string $numeroDocumento
   */
  private $numeroDocumento = null;

  /**
   * Número de artículo que establece la exoneración o autorización
   *
   * @var int $articulo
   */
  private $articulo = null;

  /**
   * Número de inciso que establece la exoneración o autorización
   *
   * @var int $inciso
   */
  private $inciso = null;

  /**
   * Nombre de la institución o dependencia que emitió la exoneración
   *
   * @var string $nombreInstitucion
   */
  private $nombreInstitucion = null;

  /**
   * Detalle Nombre de institución o dependencia que emitió la exoneración OTRO
   *
   * @var string $nombreInstitucionOtros
   */
  private $nombreInstitucionOtros = null;

  /**
   * Fecha y hora de la emisión del documento de exoneración o autorización.
   *
   * @var \DateTime $fechaEmisionEX
   */
  private $fechaEmisionEX = null;

  /**
   * Tarifa exonerada
   *
   * @var float $tarifaExonerada
   */
  private $tarifaExonerada = null;

  /**
   * Monto del impuesto exonerado
   *
   * @var float $montoExoneracion
   */
  private $montoExoneracion = null;

  /**
   * Constructor para inicializar la exoneración.
   */
  public function __construct(TransactionLineTax $tax, $subtotal)
  {
    if (!is_null($tax->exonerationType) && !empty($tax->exonerationType->code))
      $this->setTipoDocumentoEX1($tax->exonerationType->code);

    if (!is_null($tax->exoneration_doc_other) && !empty($tax->exoneration_doc_other))
      $this->setTipoDocumentoOTRO($tax->exoneration_doc_other);

    if (!is_null($tax->exoneration_doc) && !empty($tax->exoneration_doc))
      $this->setNumeroDocumento($tax->exoneration_doc);

    if (!is_null($tax->exoneration_article) && !empty($tax->exoneration_article))
      $this->setArticulo($tax->exoneration_article);

    if (!is_null($tax->exoneration_inciso) && !empty($tax->exoneration_inciso))
      $this->setInciso($tax->exoneration_inciso);

    if (!is_null($tax->exonerationInstitution) && !empty($tax->exonerationInstitution->code))
      $this->setNombreInstitucion($tax->exonerationInstitution->code);

    if (!is_null($tax->exoneration_institute_other) && !empty($tax->exoneration_institute_other))
      $this->setNombreInstitucionOtros($tax->exoneration_institute_other);

    if (!is_null($tax->exoneration_date) && !empty($tax->exoneration_date)) {

      if ($tax->exoneration_date) {
        // Convertir el string a objeto DateTime
        $fechaEmision = Carbon::parse($tax->exoneration_date, 'America/Costa_Rica');

        $this->setFechaEmisionEX($fechaEmision);
      } else {
        throw new \Exception("La transacción no tiene fecha definida");
      }
    }

    $this->setTarifaExonerada($tax->exoneration_percent);

    $montoExoneracion = $tax->tax_amount * $this->getTarifaExonerada() / 100;
    $this->setMontoExoneracion($montoExoneracion);
  }


  /**
   * Gets as tipoDocumentoEX1
   *
   * Tipo de documento de exoneración o autorización.
   *
   * @return string
   */
  public function getTipoDocumentoEX1()
  {
    return $this->tipoDocumentoEX1;
  }

  /**
   * Sets a new tipoDocumentoEX1
   *
   * Tipo de documento de exoneración o autorización.
   *
   * @param string $tipoDocumentoEX1
   * @return self
   */
  public function setTipoDocumentoEX1($tipoDocumentoEX1)
  {
    $this->tipoDocumentoEX1 = $tipoDocumentoEX1;
    return $this;
  }

  /**
   * Gets as tipoDocumentoOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 10.1. Se debe describir puntualmente el tipo de documento o autorización utilizado
   *
   * @return string
   */
  public function getTipoDocumentoOTRO()
  {
    return $this->tipoDocumentoOTRO;
  }

  /**
   * Sets a new tipoDocumentoOTRO
   *
   * Será obligatorio en caso de utilizar el código 99 de “Otros” de la nota 10.1. Se debe describir puntualmente el tipo de documento o autorización utilizado
   *
   * @param string $tipoDocumentoOTRO
   * @return self
   */
  public function setTipoDocumentoOTRO($tipoDocumentoOTRO)
  {
    $this->tipoDocumentoOTRO = $tipoDocumentoOTRO;
    return $this;
  }

  /**
   * Gets as numeroDocumento
   *
   * Número de documento de exoneración o autorización
   *
   * @return string
   */
  public function getNumeroDocumento()
  {
    return $this->numeroDocumento;
  }

  /**
   * Sets a new numeroDocumento
   *
   * Número de documento de exoneración o autorización
   *
   * @param string $numeroDocumento
   * @return self
   */
  public function setNumeroDocumento($numeroDocumento)
  {
    $this->numeroDocumento = $numeroDocumento;
    return $this;
  }

  /**
   * Gets as articulo
   *
   * Número de artículo que establece la exoneración o autorización
   *
   * @return int
   */
  public function getArticulo()
  {
    return $this->articulo;
  }

  /**
   * Sets a new articulo
   *
   * Número de artículo que establece la exoneración o autorización
   *
   * @param int $articulo
   * @return self
   */
  public function setArticulo($articulo)
  {
    $this->articulo = $articulo;
    return $this;
  }

  /**
   * Gets as inciso
   *
   * Número de inciso que establece la exoneración o autorización
   *
   * @return int
   */
  public function getInciso()
  {
    return $this->inciso;
  }

  /**
   * Sets a new inciso
   *
   * Número de inciso que establece la exoneración o autorización
   *
   * @param int $inciso
   * @return self
   */
  public function setInciso($inciso)
  {
    $this->inciso = $inciso;
    return $this;
  }

  /**
   * Gets as nombreInstitucion
   *
   * Nombre de la institución o dependencia que emitió la exoneración
   *
   * @return string
   */
  public function getNombreInstitucion()
  {
    return $this->nombreInstitucion;
  }

  /**
   * Sets a new nombreInstitucion
   *
   * Nombre de la institución o dependencia que emitió la exoneración
   *
   * @param string $nombreInstitucion
   * @return self
   */
  public function setNombreInstitucion($nombreInstitucion)
  {
    $this->nombreInstitucion = $nombreInstitucion;
    return $this;
  }

  /**
   * Gets as nombreInstitucionOtros
   *
   * Detalle Nombre de institución o dependencia que emitió la exoneración OTRO
   *
   * @return string
   */
  public function getNombreInstitucionOtros()
  {
    return $this->nombreInstitucionOtros;
  }

  /**
   * Sets a new nombreInstitucionOtros
   *
   * Detalle Nombre de institución o dependencia que emitió la exoneración OTRO
   *
   * @param string $nombreInstitucionOtros
   * @return self
   */
  public function setNombreInstitucionOtros($nombreInstitucionOtros)
  {
    $this->nombreInstitucionOtros = $nombreInstitucionOtros;
    return $this;
  }

  /**
   * Gets as fechaEmisionEX
   *
   * Fecha y hora de la emisión del documento de exoneración o autorización.
   *
   * @return \DateTime
   */
  public function getFechaEmisionEX()
  {
    return $this->fechaEmisionEX;
  }

  /**
   * Sets a new fechaEmisionEX
   *
   * Fecha y hora de la emisión del documento de exoneración o autorización.
   *
   * @param \DateTime $fechaEmisionEX
   * @return self
   */
  public function setFechaEmisionEX(\DateTime $fechaEmisionEX)
  {
    $this->fechaEmisionEX = $fechaEmisionEX;
    return $this;
  }

  /**
   * Gets as tarifaExonerada
   *
   * Tarifa exonerada
   *
   * @return float
   */
  public function getTarifaExonerada()
  {
    return $this->tarifaExonerada;
  }

  /**
   * Sets a new tarifaExonerada
   *
   * Tarifa exonerada
   *
   * @param float $tarifaExonerada
   * @return self
   */
  public function setTarifaExonerada($tarifaExonerada)
  {
    $this->tarifaExonerada = $tarifaExonerada;
    return $this;
  }

  /**
   * Gets as montoExoneracion
   *
   * Monto del impuesto exonerado
   *
   * @return float
   */
  public function getMontoExoneracion()
  {
    return $this->montoExoneracion;
  }

  /**
   * Sets a new montoExoneracion
   *
   * Monto del impuesto exonerado
   *
   * @param float $montoExoneracion
   * @return self
   */
  public function setMontoExoneracion($montoExoneracion)
  {
    $this->montoExoneracion = $montoExoneracion;
    return $this;
  }
}
