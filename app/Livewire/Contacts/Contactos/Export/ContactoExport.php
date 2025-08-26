<?php

namespace App\Livewire\Contacts\Contactos\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactoExport implements FromCollection, WithHeadings
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
    return ["ID", "Nombre", "Email", "Teléfono", "Ext", "Celular", "Grupo empresarial", "Sector", " Área de práctica", "Clasificación", "Tipo de cliente", "Fecha de nacimiento", "Año de ingreso"];
  }
}
