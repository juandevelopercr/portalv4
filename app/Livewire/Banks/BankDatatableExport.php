<?php

namespace App\Livewire\Banks;

use App\Livewire\Banks\Export\BankExport;
use App\Livewire\Banks\Export\BankExportFromView;
use App\Models\Bank;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class BankDatatableExport extends Component
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
    $dataQuery = Bank::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('banks.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new BankExportFromView($dataQuery->get()), 'banks.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Bank::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('banks.id', $this->selectedIds);
    }
    return Excel::download(new BankExport($dataQuery->get()), 'banks.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Bank::all() : Bank::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.banks.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'banks.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
