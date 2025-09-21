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
  public $totalDiscount;
  public $totalTax;
  public $totalAditionalCharge;

  public $totalServGravados;
  public $totalServExentos;
  public $totalServExonerado;
  public $totalServNoSujeto;

  public $totalMercGravadas;
  public $totalMercExentas;
  public $totalMercExonerada;
  public $totalMercNoSujeta;

  public $totalGravado;
  public $totalExento;
  public $totalVenta;
  public $totalVentaNeta;
  public $totalExonerado;
  public $totalNoSujeto;
  public $totalImpAsumEmisorFabrica;
  public $totalImpuesto;
  public $totalIVADevuelto;
  public $totalOtrosCargos;
  public $totalComprobante;

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
