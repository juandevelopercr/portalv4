<?php

namespace App\Livewire\ProductHonorariosTimbres\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductHonorarioTimbreExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.products-honorarios-timbres.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
