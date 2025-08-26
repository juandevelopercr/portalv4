<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class TransactionPayments extends Component
{
  public $transactionId;
  public $record;
  public $payments = [];
  public $totalComprobante = 0;
  public $paymentMethods;
  public $closeForm = false;

  public $totalPagado = 0;
  public $vuelto = 0;
  public $pendientePorPagar = 0;
  public $payment_status = 'due';
  public $created_at;

  protected $listeners = ['setTotalComprobante'];

  public function mount($transactionId, $paymentMethods)
  {
    $this->transactionId = $transactionId;
    $this->paymentMethods = $paymentMethods;
  }

  public function updatedPayments()
  {
    $this->recalcularVuelto();
  }

  public function setTotalComprobante($monto)
  {
    $this->totalComprobante = $monto;
    $this->recalcularVuelto();
  }

  public function addPayment()
  {
    $today = Carbon::now()->toDateString();
    // Convertir a formato d-m-Y para mostrar en el input
    $this->payments[] = [
      'tipo_medio_pago' => '04',
      'medio_pago_otros' => '',
      'total_medio_pago' => '0',
      'banco' => '',
      'referencia' => '',
      'detalle' => '',
      'created_at' => Carbon::parse($today)->format('d-m-Y')
    ];

    //dd($this->payments);
    $this->recalcularVuelto();
  }

  public function removePayment($index)
  {
    unset($this->payments[$index]);
    $this->payments = array_values($this->payments); // Reindexar
    $this->recalcularVuelto();
  }

  public function recalcularVuelto()
  {
    $this->totalPagado = collect($this->payments ?? [])->sum(function ($p) {
      $valor = str_replace(',', '', $p['total_medio_pago'] ?? 0);
      return floatval($valor);
    });

    $this->vuelto = max(0, $this->totalPagado - floatval($this->totalComprobante));
    $this->pendientePorPagar = max(0, floatval($this->totalComprobante) - $this->totalPagado);

    if ($this->totalPagado <= 0) {
      $this->payment_status = 'due';
    } elseif ($this->pendientePorPagar == 0) {
      $this->payment_status = 'paid';
    } else {
      $this->payment_status = 'partial';
    }
  }

  public function save()
  {
    // --- Sincronizar pagos ---
    // 1. Obtener los IDs actuales en la BD
    $existingPaymentIds = $this->record->payments()->pluck('id')->toArray();

    // 2. Obtener los IDs que aún están en $this->payments
    $submittedPaymentIds = collect($this->payments)
      ->pluck('id')
      ->filter() // elimina null
      ->toArray();

    // 3. Detectar los eliminados (los que ya no están)
    $idsToDelete = array_diff($existingPaymentIds, $submittedPaymentIds);

    // 4. Eliminar los pagos que ya no están
    if (!empty($idsToDelete)) {
      TransactionPayment::whereIn('id', $idsToDelete)->delete();
    }

    // 5. Crear o actualizar los que se enviaron
    foreach ($this->payments as $pago) {
      $pago['total_medio_pago'] = (float) str_replace(',', '', $pago['total_medio_pago']);
      $pago['created_at'] = Carbon::createFromFormat('d-m-Y', $pago['created_at'])
        ->format('Y-m-d H:i:s');

      if (!empty($pago['id'])) {

        // Actualización directa evitando el modelo
        DB::table('transactions_payments')
          ->where('id', $pago['id'])
          ->update($pago);
      } else {
        $this->record->payments()->create($pago);
      }
    }
    $this->recalcularVuelto();
    $this->record->payment_status = $this->payment_status;
    $this->record->save();

    if ($this->closeForm) {
      $this->dispatch('close-payment-modal');
      $this->dispatch('cuentas-cobrar-updated');
    }
    $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);
  }

  public function updateAndClose()
  {
    // ... el resto del código
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->save();
  }

  public function closePaymentModal()
  {
    $this->dispatch('close-payment-modal');
  }


  public function render()
  {
    $record = Transaction::find($this->transactionId);
    $this->record = $record;
    $today = Carbon::now()->toDateString();

    $this->payments = $record->payments->map(fn($p) => [
      'id'              => $p->id,
      'tipo_medio_pago' => $p->tipo_medio_pago,
      'medio_pago_otros' => $p->medio_pago_otros,
      'total_medio_pago' => Helpers::formatDecimal($p->total_medio_pago),
      'banco' => $p->banco,
      'referencia' => $p->referencia,
      'detalle' => $p->detalle,
      'created_at' => optional($p->created_at)->format('d-m-Y'), // ← este cambio
    ])->toArray();

    if (empty($this->payments))
      $this->payments = [[
        'tipo_medio_pago' => '04', // Transferencia
        'medio_pago_otros' => '',
        'total_medio_pago' => '0',
        'banco' => '',
        'referencia' => '',
        'detalle' => '',
        'created_at' => Carbon::parse($today)->format('d-m-Y')
      ]];

    $this->totalComprobante = $record->totalComprobante;
    $this->recalcularVuelto();

    return view('livewire.transactions.transaction-payments');
  }
}
