<?php

namespace App\Livewire\Movimientos\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MovimientoExport implements FromCollection, WithHeadings
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
      "Cuenta",
      "Número",
      "Fecha",
      "Beneficiario",
      "Moneda",
      "Monto",
      "Tipo movimiento",
      "Descripción",
      "Código contable",
      "Centro de costo",
      "Estado",
      "Bloqueo de fondos",
      "Clonado",
      "Comnprobante pendiente"
    ];
  }
}
