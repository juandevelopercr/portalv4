<?php

namespace App\Livewire\Clasificadores\HonorariosRecetas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class HonorarioRecetaExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.honorarios-recetas.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
