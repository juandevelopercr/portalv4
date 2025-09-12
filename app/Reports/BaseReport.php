<?php

namespace App\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseReport implements FromCollection, WithHeadings, WithStyles
{
  abstract public function query(); // cada reporte define su query

  abstract public function columns(): array;
  // ejemplo: [['label' => 'Cliente', 'field' => 'client_name', 'align' => 'left']]

  public function collection()
  {
    return $this->query()->get()->map(function ($row) {
      return collect($this->columns())->map(fn($col) => $row->{$col['field']});
    });
  }

  public function headings(): array
  {
    return collect($this->columns())->pluck('label')->toArray();
  }

  public function styles(Worksheet $sheet)
  {
    foreach ($this->columns() as $index => $col) {
      $columnLetter = chr(65 + $index); // A, B, C...
      if (($col['align'] ?? null) === 'right') {
        $sheet->getStyle($columnLetter)->getAlignment()->setHorizontal('right');
      } elseif (($col['align'] ?? null) === 'center') {
        $sheet->getStyle($columnLetter)->getAlignment()->setHorizontal('center');
      }
    }

    return [
      1 => ['font' => ['bold' => true]], // encabezados en negrita
    ];
  }
}
