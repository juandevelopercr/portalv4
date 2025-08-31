<?php

namespace App\Listeners;

use App\Listeners\StoreSessionVariablesService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class StoreSessionVariables
{
  protected $storeSessionVariablesService;

  /**
   * Constructor para inyectar el servicio.
   */
  public function __construct(StoreSessionVariablesService $storeSessionVariablesService)
  {
    $this->storeSessionVariablesService = $storeSessionVariablesService;
  }

  /**
   * Maneja el evento de inicio de sesión.
   */
  public function handle($event)
  {
    try {
      // Llama al servicio para gestionar las variables de sesión
      $this->storeSessionVariablesService->storeVariables($event->user);

      //Log::info('Variables de sesión guardadas correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al gestionar las variables de sesión', ['error' => $e->getMessage()]);
    }
  }
}
