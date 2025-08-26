<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

  public $editingIndex = null;
  public $maxPaymentAmount = 0;
  public $provisionalPayment = 0;

  // Propiedades individuales para el nuevo pago
  public $paymentMethod = '04';
  public $otherMethod = '';
  public $paymentAmount = '0';
  public $bank = '';
  public $reference = '';
  public $details = '';
  public $paymentDate;

  protected $listeners = ['setTotalComprobante'];

  public function mount($transactionId, $paymentMethods)
  {
    $this->transactionId = $transactionId;
    $this->paymentMethods = $paymentMethods;
    $this->paymentDate = Carbon::now()->format('d-m-Y');
    $this->loadTransactionData();
  }

  public function loadTransactionData()
  {
    $this->record = Transaction::find($this->transactionId);

    if (!$this->record) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'Transacción no encontrada'
      ]);
      $this->closeModal();
      return;
    }

    $this->payments = $this->record->payments->map(function ($p) {
      return [
        'id' => $p->id,
        'tipo_medio_pago' => $p->tipo_medio_pago,
        'medio_pago_otros' => $p->medio_pago_otros,
        'total_medio_pago' => $p->total_medio_pago, // Mantener como número
        'banco' => $p->banco,
        'referencia' => $p->referencia,
        'detalle' => $p->detalle,
        'created_at' => optional($p->created_at)->format('d-m-Y'),
      ];
    })->toArray();

    $this->totalComprobante = $this->record->totalComprobante;
    $this->recalcularVuelto();
    $this->calculateMaxPayment();
  }

  public function calculateMaxPayment()
  {
    $currentPaid = collect($this->payments)->sum(function ($p) {
      return is_numeric($p['total_medio_pago'])
        ? $p['total_medio_pago']
        : floatval(str_replace(',', '', $p['total_medio_pago']));
    });

    $this->maxPaymentAmount = max(0, $this->totalComprobante - $currentPaid);
  }

  public function addPayment()
  {
    $this->validate([
      'paymentMethod' => 'required',
      'paymentAmount' => 'required',
      'paymentDate' => 'required|date_format:d-m-Y',
    ], [
      'paymentMethod.required' => 'Seleccione un método de pago',
      'paymentAmount.required' => 'Ingrese el monto del pago',
      'paymentDate.required' => 'Ingrese la fecha del pago',
      'paymentDate.date_format' => 'Formato de fecha inválido (dd-mm-yyyy)',
    ]);

    if ($this->paymentMethod === '99') {
      $this->validate([
        'otherMethod' => 'required',
      ], [
        'otherMethod.required' => 'Especifique el método de pago',
      ]);
    }

    $amount = floatval(str_replace(',', '', $this->paymentAmount));

    if ($amount > $this->maxPaymentAmount) {
      $amount = $this->maxPaymentAmount;
    }

    $this->payments[] = [
      'tipo_medio_pago' => $this->paymentMethod,
      'medio_pago_otros' => $this->otherMethod,
      'total_medio_pago' => $amount,
      'banco' => $this->bank,
      'referencia' => $this->reference,
      'detalle' => $this->details,
      'created_at' => $this->paymentDate,
    ];

    $this->resetPaymentFields();
    $this->provisionalPayment = 0;
    $this->recalcularVuelto();
  }

  public function resetPaymentFields()
  {
    $this->paymentMethod = '04';
    $this->otherMethod = '';
    $this->paymentAmount = '0';
    $this->bank = '';
    $this->reference = '';
    $this->details = '';
    $this->paymentDate = Carbon::now()->format('d-m-Y');
  }

  public function editPayment($index)
  {
    $this->editingIndex = $index;
    $payment = $this->payments[$index];

    $this->paymentMethod = $payment['tipo_medio_pago'];
    $this->otherMethod = $payment['medio_pago_otros'];
    $this->paymentAmount = number_format($payment['total_medio_pago'], 2);
    $this->bank = $payment['banco'];
    $this->reference = $payment['referencia'];
    $this->details = $payment['detalle'];
    $this->paymentDate = $payment['created_at'];

    $this->provisionalPayment = $payment['total_medio_pago'];
    $this->calculateMaxPaymentForEdit($index);
  }

  public function calculateMaxPaymentForEdit($index)
  {
    $currentPaid = 0;
    foreach ($this->payments as $i => $payment) {
      if ($i != $index) {
        $amount = $payment['total_medio_pago'];
        if (!is_numeric($amount)) {
          $amount = floatval(str_replace(',', '', $amount));
        }
        $currentPaid += $amount;
      }
    }

    $this->maxPaymentAmount = max(0, $this->totalComprobante - $currentPaid);
  }

  public function updatePayment()
  {
    if (!is_null($this->editingIndex)) {
      $this->validate([
        'paymentMethod' => 'required',
        'paymentAmount' => 'required',
        'paymentDate' => 'required|date_format:d-m-Y',
      ], [
        'paymentMethod.required' => 'Seleccione un método de pago',
        'paymentAmount.required' => 'Ingrese el monto del pago',
        'paymentDate.required' => 'Ingrese la fecha del pago',
        'paymentDate.date_format' => 'Formato de fecha inválido (dd-mm-yyyy)',
      ]);

      if ($this->paymentMethod === '99') {
        $this->validate([
          'otherMethod' => 'required',
        ], [
          'otherMethod.required' => 'Especifique el método de pago',
        ]);
      }

      $amount = floatval(str_replace(',', '', $this->paymentAmount));

      if ($amount > $this->maxPaymentAmount) {
        $amount = $this->maxPaymentAmount;
      }

      $this->payments[$this->editingIndex] = [
        'tipo_medio_pago' => $this->paymentMethod,
        'medio_pago_otros' => $this->otherMethod,
        'total_medio_pago' => $amount,
        'banco' => $this->bank,
        'referencia' => $this->reference,
        'detalle' => $this->details,
        'created_at' => $this->paymentDate,
      ];

      $this->editingIndex = null;
      $this->resetPaymentFields();
      $this->provisionalPayment = 0;
      $this->recalcularVuelto();
    }
  }

  public function cancelEdit()
  {
    $this->editingIndex = null;
    $this->resetPaymentFields();
    $this->provisionalPayment = 0;
    $this->recalcularVuelto();
  }

  public function removePayment($index)
  {
    unset($this->payments[$index]);
    $this->payments = array_values($this->payments);
    $this->recalcularVuelto();
    $this->calculateMaxPayment();
  }

  public function recalcularVuelto($includeProvisional = false)
  {
    $totalPagado = collect($this->payments)->sum(function ($p) {
      return is_numeric($p['total_medio_pago'])
        ? $p['total_medio_pago']
        : floatval(str_replace(',', '', $p['total_medio_pago']));
    });

    if ($includeProvisional) {
      $totalPagado += $this->provisionalPayment;
    }

    $this->totalPagado = $totalPagado;
    $this->vuelto = max(0, $this->totalPagado - floatval($this->totalComprobante));
    $this->pendientePorPagar = max(0, floatval($this->totalComprobante) - $this->totalPagado);

    if ($this->pendientePorPagar == 0) {
      $this->payment_status = 'paid';
    } elseif ($this->totalPagado > 0) {
      $this->payment_status = 'partial';
    } else {
      $this->payment_status = 'due';
    }

    $this->calculateMaxPayment();
  }

  public function save()
  {
    try {
      DB::beginTransaction();

      $existingPaymentIds = $this->record->payments()->pluck('id')->toArray();
      $submittedPaymentIds = [];

      foreach ($this->payments as $pago) {
        $pagoData = $pago;

        // Asegurar que el monto sea numérico
        if (!is_numeric($pagoData['total_medio_pago'])) {
          $pagoData['total_medio_pago'] = floatval(str_replace(',', '', $pagoData['total_medio_pago']));
        }

        $pagoData['created_at'] = Carbon::createFromFormat('d-m-Y', $pagoData['created_at'])
          ->format('Y-m-d H:i:s');

        if (!empty($pagoData['id'])) {
          $submittedPaymentIds[] = $pagoData['id'];
          DB::table('transactions_payments')
            ->where('id', $pagoData['id'])
            ->update($pagoData);
        } else {
          $newPayment = $this->record->payments()->create($pagoData);
          $submittedPaymentIds[] = $newPayment->id;
        }
      }

      $idsToDelete = array_diff($existingPaymentIds, $submittedPaymentIds);
      if (!empty($idsToDelete)) {
        TransactionPayment::whereIn('id', $idsToDelete)->delete();
      }

      $this->record->payment_status = $this->payment_status;
      $this->record->save();

      DB::commit();

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => 'Pagos actualizados correctamente'
      ]);

      if ($this->closeForm) {
        $this->closeModal();
      }
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'Error al guardar pagos: ' . $e->getMessage()
      ]);
    }
  }

  public function updateAndClose()
  {
    $this->closeForm = true;
    $this->save();
  }

  public function closeModal()
  {
    $this->dispatch('close-payment-modal');
  }

  public function render()
  {
    // Si no hemos cargado los datos, cargarlos
    if (!$this->record) {
      $this->loadTransactionData();
    }

    return view('livewire.transactions.transaction-payments');
  }

  public function updatedPaymentAmount($value)
  {
    $cleaned = preg_replace('/[^0-9.]/', '', $value);
    $this->paymentAmount = $cleaned;
    $this->provisionalPayment = floatval($cleaned);
    $this->recalcularVuelto(true);
  }

  public function updatePaymentAmount($value)
  {
    $cleaned = preg_replace('/[^0-9.]/', '', $value);
    $this->paymentAmount = $cleaned;
    $this->provisionalPayment = floatval($cleaned);
    $this->recalcularVuelto(true);
  }
}
