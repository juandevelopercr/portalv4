<?php

namespace App\Livewire\MovimientoNotifications\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MovimientoNotificationExport implements FromCollection, WithHeadings
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function collection()
  {
    return $this->records;
  }

  public function headings(): array
  {
    return ["ID", "nombre", "email", "copia", "enviar_rechazo", "enviar_aprobado", "activo"];
  }
}
