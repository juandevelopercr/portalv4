<?php

namespace App\Livewire\Clasificadores\Sellers\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SellerExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.sellers.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
