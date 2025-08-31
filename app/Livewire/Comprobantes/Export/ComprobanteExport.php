<?php

namespace App\Livewire\Comprobantes\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComprobanteExport implements FromCollection, WithHeadings
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
    return ["ID", "Clave", "Consecutivo", "Fecha", "Emisor", "Receptor", "Tipo", "Impuestos", "Descuentos", "Total"];
  }
}
