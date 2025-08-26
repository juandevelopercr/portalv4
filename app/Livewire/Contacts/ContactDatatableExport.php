<?php

namespace App\Livewire\Contacts;

use App\Livewire\Contacts\Export\ContactExport;
use App\Livewire\Contacts\Export\ContactExportFromView;
use App\Models\Contact;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ContactDatatableExport extends Component
{
  public $search = ''; // Almacena el término de búsqueda
  public $selectedIds = []; // Almacena los IDs de usuarios seleccionados

  //#[On('updateSelectedIds')]
  protected $listeners = ['updateSelectedIds', 'updateSearch'];

  public function updateSelectedIds($selectedIds)
  {
    $this->selectedIds = $selectedIds;
  }

  public function updateSearch($search)
  {
    $this->search = $search;
  }

  public function prepareExportExcel()
  {
    $dataQuery = Contact::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('contacts.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ContactExportFromView($dataQuery->get()), 'contacts.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Contact::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('Contacts.id', $this->selectedIds);
    }
    return Excel::download(new ContactExport($dataQuery->get()), 'Contacts.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Contact::all() : Contact::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.Contacts.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'Contacts.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
