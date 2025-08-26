<?php

namespace App\Livewire\Casos\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class CasoExportFromView implements FromView, WithColumnFormatting, ShouldAutoSize
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function columnFormats(): array
  {
    return [
      'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Subtotal
      'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Impuesto
    ];
  }

  public function view(): View
  {
    $chunks = [];
    $this->query->chunk(500, function ($rows) use (&$chunks) {
      $chunks[] = $rows;
    });

    return view('livewire.casos.export.data-excel', [
      'chunks' => $chunks
    ]);
  }
}
