<?php

namespace App\Livewire\MovimientoNotifications\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MovimientoNotificationExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.movimiento-notifications.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
