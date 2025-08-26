<?php

namespace App\Livewire\Casos\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoSituacionExportFromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.casos.export.data-excel-pendientes', [
      'records' => $this->records
    ]);
  }
}
