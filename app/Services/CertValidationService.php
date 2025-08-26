<?php

namespace App\Services;

use App\Models\BusinessLocation;

class CertValidationService
{
  //Devuelve el número de serie del certificado en formato hexadecimal.
  //$serial = $service->getSerialNumber($location);

  //Retorna true si el certificado vence dentro de los próximos X días (por defecto 30).
  //$vencePronto = $service->expiresSoon($location); // dentro de 30 días
  //$venceEn15 = $service->expiresSoon($location, 15); // dentro de 15 días

  // Verifica si el certificado es válido y no está vencido
  //$esValido = $service->isCertValid($location);

  // Retorna la información completa del certificado
  //$info = $service->getCertInfo($location);

  public function isCertValid(BusinessLocation $location): bool
  {
    $cert = $this->getCertInfo($location);

    if (!$cert) {
      return false;
    }

    $now = time();
    return isset($cert['validTo_time_t']) && $now <= $cert['validTo_time_t'];
  }

  /*
  Esto para archivos .p12
  public function getCertInfo(BusinessLocation $location): ?array
  {
    $pfxRelativePath = $location->certificate_digital_file;
    $pin = trim($location->certificate_pin);

    $pfxPath = public_path("storage/assets/certificates/{$pfxRelativePath}");

    if (!file_exists($pfxPath)) {
      return null;
    }

    $pfxContent = file_get_contents($pfxPath);
    $certData = [];

    if (!openssl_pkcs12_read($pfxContent, $certData, $pin)) {

      return null;
    }
    return openssl_x509_parse($certData['cert']);
  }
  */

  public function getCertInfo(BusinessLocation $location): ?array
  {
    // Obtener la ruta relativa del archivo PEM
    $pemRelativePath = $location->certificate_digital_file;
    $pemPath = public_path("storage/assets/certificates/{$pemRelativePath}");

    // Verificar si el archivo existe
    if (!file_exists($pemPath) || empty($pemRelativePath) || is_null($pemRelativePath)) {
      return null;
    }

    // Leer el contenido del archivo PEM
    $pemContent = file_get_contents($pemPath);

    // Cargar el certificado desde el contenido PEM
    $cert = openssl_x509_read($pemContent);

    if (!$cert) {
      return null;
    }

    // Parsear la información del certificado
    $certData = openssl_x509_parse($cert);

    // Devolver la información del certificado
    return $certData;
  }

  public function getSerialNumber(BusinessLocation $location): ?string
  {
    $cert = $this->getCertInfo($location);
    return $cert['serialNumberHex'] ?? null;
  }

  public function expiresSoon(BusinessLocation $location, int $days = 30): bool
  {
    $cert = $this->getCertInfo($location);

    if (!$cert || !isset($cert['validTo_time_t'])) {
      return false;
    }

    $now = time();
    $limit = $now + ($days * 86400);

    return $cert['validTo_time_t'] <= $limit;
  }
}
