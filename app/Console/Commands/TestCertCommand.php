<?php

namespace App\Console\Commands;

use App\Models\BusinessLocation;
use App\Services\CertValidationService;
use Illuminate\Console\Command;

class TestCertCommand extends Command
{
    //php artisan cert:test {locationId}

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:test {locationId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica si el certificado digital (.p12) y su PIN son válidos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $locationId = $this->argument('locationId');
      $location = BusinessLocation::find($locationId);

      if (!$location) {
          $this->error("BusinessLocation con ID {$locationId} no existe.");
          return 1;
      }

      $service = new CertValidationService();

      if (!$service->isCertValid($location)) {
          $this->error("El certificado no es válido o ha expirado.");
          return 1;
      }

      $cert = $service->getCertInfo($location);

      $this->info("Certificado cargado correctamente.");
      $this->line("- Subject: " . ($cert['subject']['CN'] ?? 'N/A'));
      $this->line("- Válido hasta: " . date('Y-m-d H:i:s', $cert['validTo_time_t'] ?? 0));

      return 0;
    }
}
