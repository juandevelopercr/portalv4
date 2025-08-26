<?php

namespace App\Models\Hacienda;

use App\Models\Transaction;

/**
 * Class representing ReceptorType
 *
 *
 * Hacienda Type: ReceptorType
 */
class ReceptorType
{
  private Transaction $transaction; // ✅ Propiedad corregida

  /**
   * Constructor para inicializar la Factura Electrónica con los datos de la transacción.
   */
  public function __construct(Transaction $transaction)
  {
    $this->transaction = $transaction;
    if ($transaction->document_type == 'FEC') {
      $receptor = $transaction->location;
    } else {
      $receptor = $transaction->contact;
    }

    $this->setNombre($receptor->name);

    $identification = new IdentificacionType();
    $identification->setTipo($receptor->identificationType->code);
    $identification->setNumero($receptor->identification);
    $this->setIdentificacion($identification);

    if (!empty($receptor->commercial_name)) {
      $this->setNombreComercial($receptor->commercial_name);
    }

    // ✅ Configuración de Ubicación
    $ubicacion = new UbicacionType();
    if (!is_null($receptor->province)) {
      $ubicacion->setProvincia($receptor->province->code);
    }
    if (!is_null($receptor->canton)) {
      $ubicacion->setCanton($receptor->canton->code);
    }
    if (!is_null($receptor->distrit)) {
      $ubicacion->setDistrito($receptor->distrit->code);
    }
    if (!empty($receptor->other_signs) && (!is_null($receptor->identificationType->code) && ($receptor->identificationType->code != IdentificacionType::EXTRANJERO))) {
      $ubicacion->setOtrasSenas($receptor->other_signs);
    }
    $this->setUbicacion($ubicacion);

    if (!empty($receptor->other_signs) && (!is_null($receptor->identificationType->code) && ($receptor->identificationType->code == IdentificacionType::EXTRANJERO))) {
      $this->setOtrasSenasExtranjero($receptor->other_signs);
    }

    // ✅ Configuración de Teléfono
    if (!is_null($receptor->country) && !is_null($receptor->phone)) {
      $telefono = new TelefonoType();
      $telefono->setCodigoPais($receptor->country->phonecode);
      $telefono->setNumTelefono($receptor->phone);
      $this->setTelefono($telefono);
    }

    // ✅ Configuración de Email
    if (!empty($receptor->email)) {
      $this->setCorreoElectronico($receptor->email);
    }
  }

  /**
   * Nombre o razon social
   *
   * @var string $nombre
   */
  private $nombre = null;

  /**
   * @var \App\Models\Hacienda\IdentificacionType $identificacion
   */
  private $identificacion = null;

  /**
   * En caso de que se cuente con nombre comercial debe indicarse
   *
   * @var string $nombreComercial
   */
  private $nombreComercial = null;

  /**
   * @var \App\Models\Hacienda\UbicacionType $ubicacion
   */
  private $ubicacion = null;

  /**
   * Campo para incluir la direccion del extranjero, en caso de requerirse.
   *
   * @var string $otrasSenasExtranjero
   */
  private $otrasSenasExtranjero = null;

  /**
   * @var \App\Models\Hacienda\TelefonoType $telefono
   */
  private $telefono = null;

  /**
   * Este campo será de condición obligatoria, cuando el cliente lo requiera. Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @var string $correoElectronico
   */
  private $correoElectronico = null;

  /**
   * Gets as nombre
   *
   * Nombre o razon social
   *
   * @return string
   */
  public function getNombre()
  {
    return $this->nombre;
  }

  /**
   * Sets a new nombre
   *
   * Nombre o razon social
   *
   * @param string $nombre
   * @return self
   */
  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
    return $this;
  }

  /**
   * Gets as identificacion
   *
   * @return \App\Models\Hacienda\IdentificacionType
   */
  public function getIdentificacion()
  {
    return $this->identificacion;
  }

  /**
   * Sets a new identificacion
   *
   * @param \App\Models\Hacienda\IdentificacionType $identificacion
   * @return self
   */
  public function setIdentificacion(\App\Models\Hacienda\IdentificacionType $identificacion)
  {
    $this->identificacion = $identificacion;
    return $this;
  }

  /**
   * Gets as nombreComercial
   *
   * En caso de que se cuente con nombre comercial debe indicarse
   *
   * @return string
   */
  public function getNombreComercial()
  {
    return $this->nombreComercial;
  }

  /**
   * Sets a new nombreComercial
   *
   * En caso de que se cuente con nombre comercial debe indicarse
   *
   * @param string $nombreComercial
   * @return self
   */
  public function setNombreComercial($nombreComercial)
  {
    $this->nombreComercial = $nombreComercial;
    return $this;
  }

  /**
   * Gets as ubicacion
   *
   * @return \App\Models\Hacienda\UbicacionType
   */
  public function getUbicacion()
  {
    return $this->ubicacion;
  }

  /**
   * Sets a new ubicacion
   *
   * @param \App\Models\Hacienda\UbicacionType $ubicacion
   * @return self
   */
  public function setUbicacion(?\App\Models\Hacienda\UbicacionType $ubicacion = null)
  {
    $this->ubicacion = $ubicacion;
    return $this;
  }

  /**
   * Gets as otrasSenasExtranjero
   *
   * Campo para incluir la direccion del extranjero, en caso de requerirse.
   *
   * @return string
   */
  public function getOtrasSenasExtranjero()
  {
    return $this->otrasSenasExtranjero;
  }

  /**
   * Sets a new otrasSenasExtranjero
   *
   * Campo para incluir la direccion del extranjero, en caso de requerirse.
   *
   * @param string $otrasSenasExtranjero
   * @return self
   */
  public function setOtrasSenasExtranjero($otrasSenasExtranjero)
  {
    $this->otrasSenasExtranjero = $otrasSenasExtranjero;
    return $this;
  }

  /**
   * Gets as telefono
   *
   * @return \App\Models\Hacienda\TelefonoType
   */
  public function getTelefono()
  {
    return $this->telefono;
  }

  /**
   * Sets a new telefono
   *
   * @param \App\Models\Hacienda\TelefonoType $telefono
   * @return self
   */
  public function setTelefono(?\App\Models\Hacienda\TelefonoType $telefono = null)
  {
    $this->telefono = $telefono;
    return $this;
  }

  /**
   * Gets as correoElectronico
   *
   * Este campo será de condición obligatoria, cuando el cliente lo requiera. Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @return string
   */
  public function getCorreoElectronico()
  {
    return $this->correoElectronico;
  }

  /**
   * Sets a new correoElectronico
   *
   * Este campo será de condición obligatoria, cuando el cliente lo requiera. Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @param string $correoElectronico
   * @return self
   */
  public function setCorreoElectronico($correoElectronico)
  {
    $this->correoElectronico = $correoElectronico;
    return $this;
  }
}
