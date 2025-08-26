<?php

namespace App\Livewire\ProviderAndSellers\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProviderAndSellerExport implements FromCollection, WithHeadings
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
      "fecha_venta",
      "service_provider_id",
      "seller_id",
      "fecha_servicio",
      "company_provider_id",
      "num_pax",
      "cliente",
      "precio_rank",
      "precio_neto",
      "num_recibo",
      "pick_up_time",
      "pick_up_place",
      "comment",
      "dop_off"
    ];
  }
}
