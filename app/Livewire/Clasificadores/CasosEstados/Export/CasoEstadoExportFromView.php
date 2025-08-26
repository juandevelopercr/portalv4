<?php

namespace App\Livewire\Clasificadores\CasosEstados\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoEstadoExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-estados.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
