<?php

namespace App\Livewire\Clasificadores\CentroCostos\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CentroCostoExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.centro-costos.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
