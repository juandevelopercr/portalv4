<?php

namespace App\Observers;

use App\Helpers\Helpers;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MovimientoObserver
{
  public function creating(Movimiento $movimiento): bool
  {
    // Equivalente a beforeSave($insert=true)
    // Normalizar fecha
    if (!empty($movimiento->fecha)) {
      $movimiento->fecha = Carbon::parse($movimiento->fecha)->format('Y-m-d');
    }

    // Forzar recalculo al crear
    $movimiento->recalcular_saldo = true;
    /*
    Log::info('Observer ejecutado en creating()', [
      'monto' => $movimiento->monto,
      'tipo' => $movimiento->tipo_movimiento,
    ]);
    */
    return true;
  }

  public function updating(Movimiento $movimiento): bool
  {
    $original = $movimiento->getOriginal();

    // Forzar total_general para depósitos
    if ($movimiento->tipo_movimiento === 'DEPOSITO') {
      $movimiento->total_general = $movimiento->saldo_cancelar;
    }

    $montoOriginal = floatval($original['monto'] ?? 0);
    $impuestoOriginal = floatval($original['impuesto'] ?? 0);
    $montoNuevo = floatval($movimiento->monto ?? 0);
    $impuestoNuevo = floatval($movimiento->impuesto ?? 0);
    $clonando = $movimiento->clonando ?? 0;

    $montoAnteriorTotal = $montoOriginal + $impuestoOriginal;
    $montoNuevoTotal = $montoNuevo + $impuestoNuevo;
    $diferencia = $clonando == 1 ? $montoNuevoTotal : $montoNuevoTotal - $montoAnteriorTotal;

    if ($diferencia > 0 && in_array($movimiento->tipo_movimiento, ['CHEQUE', 'ELECTRONICO'])) {
      $fondos = round(Helpers::getSaldoMesCuenta($movimiento->cuenta_id, now()->format('Y-m-d')), 2);

      if ($fondos <= 0 || $diferencia > $fondos) {
        throw new \Exception(__('El nuevo monto supera los fondos disponibles.'));
      }
    }

    // ✅ Si cambió algo relevante, marcar que debe recalcular
    if ($montoNuevoTotal !== $montoAnteriorTotal || $clonando == 1) {
      $movimiento->recalcular_saldo = true;
    }
    /*
    Log::info('Observer -> updating()', [
      'montoAnteriorTotal' => $montoAnteriorTotal,
      'montoNuevoTotal' => $montoNuevoTotal,
      'diferencia' => $diferencia,
      'recalcular_saldo' => $movimiento->recalcular_saldo,
    ]);
    */
    $movimiento->clonando = 0;

    return true;
  }

  public function saved(Movimiento $movimiento): void
  {
    if ($movimiento->recalcular_saldo) {
      Helpers::recalcularBalancesMensuales($movimiento->cuenta_id, $movimiento->fecha);

      // ✅ Emite evento para Livewire
      $componentId = $movimiento->livewire_component_id ?? null;
      if ($componentId) {
        \Livewire\Livewire::getInstance($componentId)?->dispatch('fondos-actualizados', $movimiento->cuenta_id);
      }
      /*
      Log::info('Saldo mensual recalculado (saved)', [
        'movimiento_id' => $movimiento->id,
        'cuenta_id' => $movimiento->cuenta_id,
        'fecha' => $movimiento->fecha,
      ]);
      */
    } else {
      /*
      Log::info('No se recalculó saldo (saved)', [
        'movimiento_id' => $movimiento->id,
      ]);
      */
    }
  }

  /**
   * Handle the Movimiento "deleted" event.
   */
  public function deleted(Movimiento $movimiento): void
  {
    Helpers::recalcularBalancesMensuales($movimiento->cuenta_id, $movimiento->fecha);

    if ($movimiento->tipo_movimiento === 'CHEQUE') {
      // Anular el movimiento en lugar de eliminarlo
      $movimiento->status = 'ANULADO';
      $movimiento->monto = 0;
      $movimiento->monto_letras = '';
      $movimiento->saldo_cancelar = 0;
      $movimiento->diferencia = 0;
      $movimiento->total_general = 0;
      $movimiento->impuesto = 0;
      $movimiento->descripcion = 'NULO: ' . $movimiento->descripcion;

      $movimiento->save();

      // Actualizar todos los centros de costo relacionados
      $movimiento->centrosCostos()->update(['amount' => 0]);
    }

    // Si no es cheque, limpiar datos en las facturas asociadas
    foreach ($movimiento->transactions as $factura) {
      $factura->fecha_deposito_pago = null;
      $factura->numero_deposito_pago = null;
      $factura->save();
    }
    /*
    Log::info('Saldo mensual recalculado (deleted)', [
      'movimiento_id' => $movimiento->id,
      'cuenta_id' => $movimiento->cuenta_id,
      'fecha' => $movimiento->fecha,
    ]);
    */
  }

  /**
   * Handle the Movimiento "created" event.
   */
  public function created(Movimiento $movimiento): void
  {
    //
  }

  /**
   * Handle the Movimiento "updated" event.
   */
  public function updated(Movimiento $movimiento): void
  {
    //
  }

  /**
   * Handle the Movimiento "restored" event.
   */
  public function restored(Movimiento $movimiento): void
  {
    //
  }

  /**
   * Handle the Movimiento "force deleted" event.
   */
  public function forceDeleted(Movimiento $movimiento): void
  {
    //
  }
}
