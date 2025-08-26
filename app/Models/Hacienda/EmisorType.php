<?php

namespace App\Models\Hacienda;

use App\Models\Transaction;

/**
 * Class representing EmisorType
 *
 *
 * Hacienda Type: EmisorType
 */
class EmisorType
{
  private Transaction $transaction; // ✅ Propiedad corregida

  /**
   * Constructor para inicializar la Factura Electrónica con los datos de la transacción.
   */
  public function __construct(Transaction $transaction)
  {
    $this->transaction = $transaction;
    if ($transaction->document_type == 'FEC') {
      $emisor = $transaction->contact;
    } else
      $emisor = $transaction->location;

    $this->setNombre($emisor->name);

    $identification = new IdentificacionType();
    $identification->setTipo($emisor->identificationType->code);
    $identification->setNumero($emisor->identification);
    $this->setIdentificacion($identification);

    if (!empty($emisor->registrofiscal8707)) {
      $this->setRegistrofiscal8707($emisor->registrofiscal8707);
    }

    if (!empty($emisor->commercial_name)) {
      $this->setNombreComercial($emisor->commercial_name);
    }

    // ✅ Configuración de Ubicación
    $ubicacion = new UbicacionType();
    if (!is_null($emisor->province)) {
      $ubicacion->setProvincia($emisor->province->code);
    }
    if (!is_null($emisor->canton)) {
      $ubicacion->setCanton($emisor->canton->code);
    }
    if (!is_null($emisor->distrit)) {
      $ubicacion->setDistrito($emisor->distrit->code);
    }
    if (!empty($emisor->other_signs)) {
      $ubicacion->setOtrasSenas($emisor->other_signs);
    }
    $this->setUbicacion($ubicacion);

    // ✅ Configuración de Teléfono
    if (!is_null($emisor->country) && !is_null($emisor->phone)) {
      $telefono = new TelefonoType();
      $telefono->setCodigoPais($emisor->country->phonecode);
      $telefono->setNumTelefono($emisor->phone);
      $this->setTelefono($telefono);
    }

    // ✅ Configuración de Email
    if (!empty($emisor->email)) {
      $this->addToCorreoElectronico($emisor->email);
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
   * Campo condicional. Se convierte en carácter obligatorio cuando se
   * estén facturando códigosCAByS de bebidas alcohólicas según la Ley
   * 8707. Contiene los datos del número de registro de bebidas
   * alcohólicas, suministrado por la Dirección General de Aduanas
   *
   * @var string $registrofiscal8707
   */
  private $registrofiscal8707 = null;

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
   * @var \App\Models\Hacienda\TelefonoType $telefono
   */
  private $telefono = null;

  /**
   * Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @var string[] $correoElectronico
   */
  private $correoElectronico = [];

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
   * Gets as registrofiscal8707
   *
   * Campo condicional. Se convierte en carácter obligatorio cuando se
   * estén facturando códigosCAByS de bebidas alcohólicas según la Ley
   * 8707. Contiene los datos del número de registro de bebidas
   * alcohólicas, suministrado por la Dirección General de Aduanas
   *
   * @return string
   */
  public function getRegistrofiscal8707()
  {
    return $this->registrofiscal8707;
  }

  /**
   * Sets a new registrofiscal8707
   *
   * Campo condicional. Se convierte en carácter obligatorio cuando se
   * estén facturando códigosCAByS de bebidas alcohólicas según la Ley
   * 8707. Contiene los datos del número de registro de bebidas
   * alcohólicas, suministrado por la Dirección General de Aduanas
   *
   * @param string $registrofiscal8707
   * @return self
   */
  public function setRegistrofiscal8707($registrofiscal8707)
  {
    $this->registrofiscal8707 = $registrofiscal8707;
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
  public function setUbicacion(\App\Models\Hacienda\UbicacionType $ubicacion)
  {
    $this->ubicacion = $ubicacion;
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
   * Adds as correoElectronico
   *
   * Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @return self
   * @param string $correoElectronico
   */
  public function addToCorreoElectronico($correoElectronico)
  {
    $this->correoElectronico[] = $correoElectronico;
    return $this;
  }

  /**
   * isset correoElectronico
   *
   * Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @param int|string $index
   * @return bool
   */
  public function issetCorreoElectronico($index)
  {
    return isset($this->correoElectronico[$index]);
  }

  /**
   * unset correoElectronico
   *
   * Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @param int|string $index
   * @return void
   */
  public function unsetCorreoElectronico($index)
  {
    unset($this->correoElectronico[$index]);
  }

  /**
   * Gets as correoElectronico
   *
   * Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @return string[]
   */
  public function getCorreoElectronico()
  {
    return $this->correoElectronico;
  }

  /**
   * Sets a new correoElectronico
   *
   * Debe cumplir con la siguiente estructura:
   *  \s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*
   *
   * @param string $correoElectronico
   * @return self
   */
  public function setCorreoElectronico(array $correoElectronico)
  {
    $this->correoElectronico = $correoElectronico;
    return $this;
  }
}
