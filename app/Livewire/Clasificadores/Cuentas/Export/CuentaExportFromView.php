<?php

namespace App\Livewire\Clasificadores\Cuentas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CuentaExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.cuentas.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
