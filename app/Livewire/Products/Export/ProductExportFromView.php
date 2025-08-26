<?php

namespace App\Livewire\Products\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductExportFromView implements FromView
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function view(): View
    {
        return view('livewire.products.export.data-excel', [
            'records' => $this->records
        ]);
    }
}
