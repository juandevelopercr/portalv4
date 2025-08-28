<?php

namespace App\Livewire\Settings;

use App\Models\BusinessLocation;
use App\Services\CertValidationService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BusinessLocationCerts extends Component
{
  public array $locations = [];
  public array $expanded = [];

  public function toggleDetails($id)
  {
    $this->expanded[$id] = !($this->expanded[$id] ?? false);
  }

  public function mount(CertValidationService $certService)
  {
    $this->locations = BusinessLocation::query()
      ->select('id', 'name', 'certificate_digital_file', 'certificate_pin')
      ->get()
      ->map(function ($location) use ($certService) {
        $info = $certService->getCertInfo($location);

        if (!$info) {
          return [
            'id' => $location->id,
            'name' => $location->name,
            'valid' => false,
            'expires_soon' => false,
            'serial' => null,
            'valid_to' => null,
            'valid_from' => null,
            'cn' => null,
            'issuer' => null,
            'cipher' => 'Error al leer',
          ];
        }

        $cipher = $info['signatureTypeSN'] ?? ($info['signatureTypeLN'] ?? 'Desconocido');

        Log::info("INFO CERT", [
          'id' => $location->id,
          'ruta' => $location->certificate_digital_file,
          'resultado' => $info,
          'error' => openssl_error_string()
        ]);

        return [
          'id' => $location->id,
          'name' => $location->name,
          'valid' => $certService->isCertValid($location),
          'expires_soon' => $certService->expiresSoon($location),
          'serial' => $certService->getSerialNumber($location),
          'valid_to' => isset($info['validTo_time_t']) ? date('Y-m-d H:i:s', $info['validTo_time_t']) : null,
          'valid_from' => isset($info['validFrom_time_t']) ? date('Y-m-d H:i:s', $info['validFrom_time_t']) : null,
          'cn' => $info['subject']['CN'] ?? null,
          'issuer' => $info['issuer']['CN'] ?? null,
          'cipher' => $cipher,
        ];
      })
      ->toArray();
  }

  public function render()
  {
    return view('livewire.settings.business-location-certs');
  }
}
