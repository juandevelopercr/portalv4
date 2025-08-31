<?php

namespace App\Livewire\Contacts\Contactos\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ContactoExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.contacts.contactos.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
