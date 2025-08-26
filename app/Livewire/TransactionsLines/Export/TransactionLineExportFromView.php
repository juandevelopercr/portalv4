<?php

namespace App\Livewire\TransactionsLines\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionLineExportFromView implements FromView
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function view(): View
    {
        return view('livewire.transactions-lines.export.data-excel', [
            'records' => $this->records
        ]);
    }
}
