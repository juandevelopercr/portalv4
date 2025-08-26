<?php

namespace App\Livewire\Contacts\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ContactExportFromView implements FromView
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function view(): View
    {
        return view('livewire.contacts.export.data-excel', [
            'records' => $this->records
        ]);
    }
}
