<?php

namespace App\Livewire\Contacts\Contactos;

use App\Livewire\Contacts\Contactos\Export\ContactoExport;
use App\Livewire\Contacts\Contactos\Export\ContactoExportFromView;
use App\Models\ContactContacto;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ContactoDatatableExport extends Component
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
    $dataQuery = ContactContacto::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('contacts_contactos.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ContactoExportFromView($dataQuery->get()), 'contactos.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Contactcontacto::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('contacts_contactos.id', $this->selectedIds);
    }
    return Excel::download(new ContactoExport($dataQuery->get()), 'contactos.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? ContactContacto::all() : ContactContacto::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.contacts.contactos.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'contactos.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
