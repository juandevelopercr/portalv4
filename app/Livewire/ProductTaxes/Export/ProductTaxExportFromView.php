<?php

namespace App\Livewire\ProductTaxes\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductTaxExportFromView implements FromView
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function view(): View
    {
        return view('livewire.products-taxes.export.data-excel', [
            'records' => $this->records
        ]);
    }
}
