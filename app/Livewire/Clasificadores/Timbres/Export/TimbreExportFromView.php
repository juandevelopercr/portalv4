<?php

namespace App\Livewire\Clasificadores\Timbres\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TimbreExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.timbres.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
