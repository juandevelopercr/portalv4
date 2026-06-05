<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Transaction;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class TransactionTotals extends Component
{
  public $transaction_id;
  public $totalDiscount = NULL;
  public $totalTax = NULL;
  public $totalAditionalCharge = NULL;

  public $totalServGravados = NULL;
  public $totalServExentos = NULL;
  public $totalServExonerado = NULL;
  public $totalServNoSujeto = NULL;

  public $totalMercGravadas = NULL;
  public $totalMercExentas = NULL;
  public $totalMercExonerada = NULL;
  public $totalMercNoSujeta = NULL;

  public $totalGravado = NULL;
  public $totalExento = NULL;
  public $totalVenta = NULL;
  public $totalVentaNeta = NULL;
  public $totalExonerado = NULL;
  public $totalNoSujeto = NULL;
  public $totalImpAsumEmisorFabrica = NULL;
  public $totalImpuesto = NULL;
  public $totalIVADevuelto = NULL;
  public $totalOtrosCargos = NULL;
  public $totalComprobante = NULL;

  public $currencyCode = '';

  #[On('updateTransactionContext')]
  public function handleUpdateContext($data)
  {
    $this->transaction_id = $data['transaction_id'];
    $this->refreshTotal($this->transaction_id);
  }

  public function mount($transaction_id)
  {
    $transaction = Transaction::find($transaction_id);
    if ($transaction) {

      $this->totalAditionalCharge = Helpers::formatDecimal($transaction->totalAditionalCharge ?? 0);

      $this->totalServGravados = Helpers::formatDecimal($transaction->totalServGravados ?? 0);
      $this->totalServExentos = Helpers::formatDecimal($transaction->totalServExentos ?? 0);
      $this->totalServExonerado = Helpers::formatDecimal($transaction->totalServExonerado ?? 0);
      $this->totalServNoSujeto = Helpers::formatDecimal($transaction->totalServNoSujeto ?? 0);

      $this->totalMercGravadas = Helpers::formatDecimal($transaction->totalMercGravadas ?? 0);
      $this->totalMercExentas = Helpers::formatDecimal($transaction->totalMercExentas ?? 0);
      $this->totalMercExonerada = Helpers::formatDecimal($transaction->totalMercExonerada ?? 0);
      $this->totalMercNoSujeta = Helpers::formatDecimal($transaction->totalMercNoSujeta ?? 0);

      $this->totalGravado = Helpers::formatDecimal($transaction->totalGravado ?? 0);
      $this->totalExento = Helpers::formatDecimal($transaction->totalExento ?? 0);
      $this->totalExonerado = Helpers::formatDecimal($transaction->totalExonerado ?? 0);
      $this->totalNoSujeto = Helpers::formatDecimal($transaction->totalNoSujeto ?? 0);

      $this->totalVenta = Helpers::formatDecimal($transaction->totalVenta ?? 0);
      $this->totalDiscount = Helpers::formatDecimal($transaction->totalDiscount ?? 0);
      $this->totalVentaNeta = Helpers::formatDecimal($transaction->totalVentaNeta ?? 0);
      $this->totalTax = Helpers::formatDecimal($transaction->totalTax ?? 0);
      $this->totalImpuesto = Helpers::formatDecimal($transaction->totalImpuesto ?? 0);
      $this->totalImpAsumEmisorFabrica = Helpers::formatDecimal($transaction->totalImpAsumEmisorFabrica ?? 0);
      $this->totalIVADevuelto = Helpers::formatDecimal($transaction->totalIVADevuelto ?? 0);
      $this->totalOtrosCargos = Helpers::formatDecimal($transaction->totalOtrosCargos ?? 0);
      $this->totalComprobante = Helpers::formatDecimal($transaction->totalComprobante ?? 0);

      $this->currencyCode = $transaction->currency->code;
    }
  }

  // Recibe los totales ya calculados directamente desde TransactionManager,
  // evitando la condición de carrera de leer la DB antes de que se guarden.
  #[On('totalsRefreshed')]
  public function applyRefreshedTotals($data)
  {
    $d = is_array($data) && isset($data[0]) ? $data[0] : $data;

    $this->totalAditionalCharge      = Helpers::formatDecimal($d['totalAditionalCharge'] ?? 0);
    $this->totalServGravados         = Helpers::formatDecimal($d['totalServGravados'] ?? 0);
    $this->totalServExentos          = Helpers::formatDecimal($d['totalServExentos'] ?? 0);
    $this->totalServExonerado        = Helpers::formatDecimal($d['totalServExonerado'] ?? 0);
    $this->totalServNoSujeto         = Helpers::formatDecimal($d['totalServNoSujeto'] ?? 0);
    $this->totalMercGravadas         = Helpers::formatDecimal($d['totalMercGravadas'] ?? 0);
    $this->totalMercExentas          = Helpers::formatDecimal($d['totalMercExentas'] ?? 0);
    $this->totalMercExonerada        = Helpers::formatDecimal($d['totalMercExonerada'] ?? 0);
    $this->totalMercNoSujeta         = Helpers::formatDecimal($d['totalMercNoSujeta'] ?? 0);
    $this->totalGravado              = Helpers::formatDecimal($d['totalGravado'] ?? 0);
    $this->totalExento               = Helpers::formatDecimal($d['totalExento'] ?? 0);
    $this->totalExonerado            = Helpers::formatDecimal($d['totalExonerado'] ?? 0);
    $this->totalNoSujeto             = Helpers::formatDecimal($d['totalNoSujeto'] ?? 0);
    $this->totalVenta                = Helpers::formatDecimal($d['totalVenta'] ?? 0);
    $this->totalDiscount             = Helpers::formatDecimal($d['totalDiscount'] ?? 0);
    $this->totalVentaNeta            = Helpers::formatDecimal($d['totalVentaNeta'] ?? 0);
    $this->totalImpuesto             = Helpers::formatDecimal($d['totalImpuesto'] ?? 0);
    $this->totalTax                  = Helpers::formatDecimal($d['totalImpuesto'] ?? 0);
    $this->totalImpAsumEmisorFabrica = Helpers::formatDecimal($d['totalImpAsumEmisorFabrica'] ?? 0);
    $this->totalIVADevuelto          = Helpers::formatDecimal($d['totalIVADevuelto'] ?? 0);
    $this->totalOtrosCargos          = Helpers::formatDecimal($d['totalOtrosCargos'] ?? 0);
    $this->totalComprobante          = Helpers::formatDecimal($d['totalComprobante'] ?? 0);
    if (!empty($d['currencyCode'])) {
      $this->currencyCode = $d['currencyCode'];
    }
  }

  // Fallback: lectura directa de DB (usado por updateTransactionContext y como respaldo)
  #[On('productUpdated')]
  #[On('chargeUpdated')]
  public function refreshTotal($transaction_id)
  {
    $transaction = Transaction::where('id', $transaction_id)->first();
    if ($transaction) {
      $this->totalAditionalCharge = Helpers::formatDecimal($transaction->totalAditionalCharge ?? 0);
      $this->totalServGravados = Helpers::formatDecimal($transaction->totalServGravados ?? 0);
      $this->totalServExentos = Helpers::formatDecimal($transaction->totalServExentos ?? 0);
      $this->totalServExonerado = Helpers::formatDecimal($transaction->totalServExonerado ?? 0);
      $this->totalServNoSujeto = Helpers::formatDecimal($transaction->totalServNoSujeto ?? 0);
      $this->totalMercGravadas = Helpers::formatDecimal($transaction->totalMercGravadas ?? 0);
      $this->totalMercExentas = Helpers::formatDecimal($transaction->totalMercExentas ?? 0);
      $this->totalMercExonerada = Helpers::formatDecimal($transaction->totalMercExonerada ?? 0);
      $this->totalMercNoSujeta = Helpers::formatDecimal($transaction->totalMercNoSujeta ?? 0);
      $this->totalGravado = Helpers::formatDecimal($transaction->totalGravado ?? 0);
      $this->totalExento = Helpers::formatDecimal($transaction->totalExento ?? 0);
      $this->totalExonerado = Helpers::formatDecimal($transaction->totalExonerado ?? 0);
      $this->totalNoSujeto = Helpers::formatDecimal($transaction->totalNoSujeto ?? 0);
      $this->totalVenta = Helpers::formatDecimal($transaction->totalVenta ?? 0);
      $this->totalDiscount = Helpers::formatDecimal($transaction->totalDiscount ?? 0);
      $this->totalVentaNeta = Helpers::formatDecimal($transaction->totalVentaNeta ?? 0);
      $this->totalTax = Helpers::formatDecimal($transaction->totalTax ?? 0);
      $this->totalImpuesto = Helpers::formatDecimal($transaction->totalImpuesto ?? 0);
      $this->totalImpAsumEmisorFabrica = Helpers::formatDecimal($transaction->totalImpAsumEmisorFabrica ?? 0);
      $this->totalIVADevuelto = Helpers::formatDecimal($transaction->totalIVADevuelto ?? 0);
      $this->totalOtrosCargos = Helpers::formatDecimal($transaction->totalOtrosCargos ?? 0);
      $this->totalComprobante = Helpers::formatDecimal($transaction->totalComprobante ?? 0);
      $this->currencyCode = $transaction->currency->code;
    }
  }

  public function render()
  {
    return view('livewire.transactions.transaction-totals');
  }
}
