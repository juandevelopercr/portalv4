<?php

namespace App\Livewire\Trips\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TripExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.trips.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
