<?php

namespace App\Listeners;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\User;
use App\Services\ApiBCCR;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class StoreSessionVariablesService
{
  protected $apiBCCR;

  /**
   * Constructor para inyectar dependencias.
   */
  public function __construct(ApiBCCR $apiBCCR)
  {
    $this->apiBCCR = $apiBCCR;
  }

  /**
   * Gestiona y guarda las variables de sesión.
   */
  public function storeVariables($user)
  {
    try {
      // Esto lo pongo fijo de momento luego hay que ver la lógica a seguir
      $user->business_id = 1;

      /*
      $bussines = Business::findOrFail($user->business_id);
      $location_id = 1;
      $location = BusinessLocation::find($location_id);
      */


      // Otras variables de sesión
      //Session::put('user.business_id', $user->business_id);
      Session::put('user.name', $user->name);
      //Session::put('user.business', $bussines);
      //Session::put('user.location', $location);

      // Llama al método para obtener el tipo de cambio
      $response = $this->apiBCCR->obtenerIndicadorEconomico(
        318, // Indicador del tipo de cambio
        now()->format('d/m/Y'), // Fecha de inicio
        now()->format('d/m/Y')  // Fecha de fin
      );

      $exchange_rate = '';

      if ($response) {
        // Procesar el XML devuelto
        $exchange_rate = $response;
      }

      // Guarda el tipo de cambio en la sesión
      Session::put('exchange_rate', $exchange_rate);
      Log::info('Tipo de cambio y variables de usuario guardadas en la sesión.', [
        'exchange_rate' => $exchange_rate,
        'user' => $user->name,
      ]);
    } catch (\Exception $e) {
      Log::error('Error al procesar variables de sesión.', ['error' => $e->getMessage()]);
      //throw $e;
    }
  }
}
