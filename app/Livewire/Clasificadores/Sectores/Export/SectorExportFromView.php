<?php

namespace App\Livewire\Clasificadores\Sectores\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SectorExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.sectores.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
