<?php

namespace App\Livewire\Clasificadores\ServiceProvider\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiceProviderExport implements FromCollection, WithHeadings
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
    return ["ID", "name"];
  }
}
