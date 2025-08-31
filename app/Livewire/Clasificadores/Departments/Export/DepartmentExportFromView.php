<?php

namespace App\Livewire\Clasificadores\Departments\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DepartmentExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.departments.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
