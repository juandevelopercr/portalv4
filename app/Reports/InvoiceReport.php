<?php

namespace App\Reports;

use App\Models\Transaction;
use App\Reports\BaseReport;

class InvoiceReport extends BaseReport
{
  public function query()
  {
    return Transaction::query()->with('client');
  }

  public function columns(): array
  {
    return [
      ['label' => 'Fecha', 'field' => 'created_at', 'align' => 'center'],
      ['label' => 'Cliente', 'field' => 'client_name', 'align' => 'left'],
      ['label' => 'Monto', 'field' => 'amount', 'align' => 'right'],
    ];
  }
}
