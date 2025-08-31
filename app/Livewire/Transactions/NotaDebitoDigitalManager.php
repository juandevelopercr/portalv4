<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;

class NotaDebitoDigitalManager extends TransactionManager
{
  public function mount()
  {
    parent::mount();
    // Lógica específica para notas de débito
  }

  protected function afterTransactionSaved()
  {
    // Lógica específica tras guardar una nota de débito
    // Ejemplo: registrar cargo adicional, actualizar balances, etc.
  }

  public function render()
  {
    return view('livewire.transactions.nota-debito-digital-manager');
  }
}
