<?php

namespace App\Livewire\Honorarios\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class HonorarioExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.honorarios.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
