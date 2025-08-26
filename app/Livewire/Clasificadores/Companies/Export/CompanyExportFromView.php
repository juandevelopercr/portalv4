<?php

namespace App\Livewire\Clasificadores\Companies\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CompanyExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.companies.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
