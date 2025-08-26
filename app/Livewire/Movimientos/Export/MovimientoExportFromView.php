<?php

namespace App\Livewire\Movimientos\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class MovimientoExportFromView implements FromView, WithColumnFormatting, ShouldAutoSize
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function columnFormats(): array
  {
    return [
      'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Subtotal
      'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Impuesto
      'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Monto
    ];
  }

  public function view(): View
  {
    $chunks = [];
    $this->query->chunk(500, function ($rows) use (&$chunks) {
      $chunks[] = $rows;
    });

    return view('livewire.movimientos.export.data-excel', [
      //'query' => $this->query // NOTA: se itera con ->cursor() en la vista
      'chunks' => $chunks
    ]);
  }
}
