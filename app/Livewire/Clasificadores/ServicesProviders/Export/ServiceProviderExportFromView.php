<?php

namespace App\Livewire\Clasificadores\ServicesProviders\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ServiceProviderExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.services-providers.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
