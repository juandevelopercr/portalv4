<?php

namespace App\Livewire\TransactionsCommissions\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionCommissionExportFromView implements FromView
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function view(): View
    {
        return view('livewire.transactions-commissions.export.data-excel', [
            'records' => $this->records
        ]);
    }
}
