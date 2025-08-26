<?php

namespace App\Livewire\ProductHonorariosTimbres\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductHonorarioTimbreExport implements FromCollection, WithHeadings
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
        return ["ID", "Description", "Base", "Bank", "Por Cada", "Timbre Abogados Bienes Inmuebles", "Timbre Abogados Bienes Muebles", "Tabla Honorarios", "Fijo", "Porcentaje", "Descuento"];
    }
}
