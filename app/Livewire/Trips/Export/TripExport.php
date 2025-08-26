<?php

namespace App\Livewire\Trips\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TripExport implements FromCollection, WithHeadings
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
    return [
      "ID",
      "Tipo",
      "Estado",
      "Compañia",
      "Fecha",
      "Ciudad",
      "Lugar de recogida",
      "Lugar de entrega",
      "# de Factura",
      "No. Pasajeros",
      "Nombre cliente",
      "Precio Rack",
      "Costo Neto",
      "Comentarios",
      "Consecutivo"
    ];
  }
}
