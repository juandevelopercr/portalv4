<?php

namespace App\Livewire\Clasificadores\CatalogoCuentas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CatalogoCuentaExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.catalogo-cuentas.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
