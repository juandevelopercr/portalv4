<?php

namespace App\Livewire\Clasificadores\AreasPracticas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AreaPracticaExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.areas-practicas.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
