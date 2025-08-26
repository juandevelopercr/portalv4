<?php

namespace App\Livewire\Clasificadores\Caratulas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CaratulaExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.caratulas.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
