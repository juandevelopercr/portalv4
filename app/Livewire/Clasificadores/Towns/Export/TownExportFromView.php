<?php

namespace App\Livewire\Clasificadores\Towns\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TownExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.towns.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
