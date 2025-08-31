<?php

namespace App\Livewire\TransactionsCharges\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionChargeExportFromView implements FromView
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function view(): View
    {
        return view('livewire.transactions-charges.export.data-excel', [
            'records' => $this->records
        ]);
    }
}
