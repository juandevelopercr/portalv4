<?php

namespace App\Livewire\Transactions\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionExportFromView implements FromView
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function view(): View
  {
    return view('livewire.transactions.export.data-excel', [
      'query' => $this->query // NOTA: se itera con ->cursor() en la vista
    ]);
  }
}
