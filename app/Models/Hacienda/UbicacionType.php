<?php

namespace App\Models\Hacienda;

/**
 * Class representing UbicacionType
 *
 *
 * Hacienda Type: UbicacionType
 */
class UbicacionType
{
  /**
   * @var int $provincia
   */
  private $provincia = null;

  /**
   * @var int $canton
   */
  private $canton = null;

  /**
   * @var int $distrito
   */
  private $distrito = null;

  /**
   * @var string $barrio
   */
  private $barrio = null;

  /**
   * @var string $otrasSenas
   */
  private $otrasSenas = null;

  /**
   * Gets as provincia
   *
   * @return int
   */
  public function getProvincia()
  {
    return $this->provincia;
  }

  /**
   * Sets a new provincia
   *
   * @param int $provincia
   * @return self
   */
  public function setProvincia($provincia)
  {
    $this->provincia = $provincia;
    return $this;
  }

  /**
   * Gets as canton
   *
   * @return int
   */
  public function getCanton()
  {
    return $this->canton;
  }

  /**
   * Sets a new canton
   *
   * @param int $canton
   * @return self
   */
  public function setCanton($canton)
  {
    $this->canton = str_pad((int)$canton, 2, '0', STR_PAD_LEFT);
    return $this;
  }

  /**
   * Gets as distrito
   *
   * @return int
   */
  public function getDistrito()
  {
    return $this->distrito;
  }

  /**
   * Sets a new distrito
   *
   * @param int $distrito
   * @return self
   */
  public function setDistrito($distrito)
  {
    $this->distrito = str_pad((int)$distrito, 2, '0', STR_PAD_LEFT);
    return $this;
  }

  /**
   * Gets as barrio
   *
   * @return string
   */
  public function getBarrio()
  {
    return $this->barrio;
  }

  /**
   * Sets a new barrio
   *
   * @param string $barrio
   * @return self
   */
  public function setBarrio($barrio)
  {
    $this->barrio = $barrio;
    return $this;
  }

  /**
   * Gets as otrasSenas
   *
   * @return string
   */
  public function getOtrasSenas()
  {
    return $this->otrasSenas;
  }

  /**
   * Sets a new otrasSenas
   *
   * @param string $otrasSenas
   * @return self
   */
  public function setOtrasSenas($otrasSenas)
  {
    $this->otrasSenas = $otrasSenas;
    return $this;
  }
}
