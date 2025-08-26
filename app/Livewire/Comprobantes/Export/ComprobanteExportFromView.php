<?php

namespace App\Livewire\Comprobantes\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ComprobanteExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.comprobantes.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
