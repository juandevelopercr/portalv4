<?php

namespace App\Livewire\Banks\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BankExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.banks.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
