<?php

namespace App\Livewire\ProviderAndSellers\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProviderAndSellerExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.providers-and-sellers.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
