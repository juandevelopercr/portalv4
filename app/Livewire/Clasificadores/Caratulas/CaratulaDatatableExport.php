<?php

namespace App\Livewire\Clasificadores\Caratulas;

use App\Livewire\Clasificadores\Caratulas\Export\CaratulaExport;
use App\Livewire\Clasificadores\Caratulas\Export\CaratulaExportFromView;
use App\Models\Caratula;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CaratulaDatatableExport extends Component
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
    $dataQuery = Caratula::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('caratulas.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CaratulaExportFromView($dataQuery->get()), 'Caratulas.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Caratula::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('caratulas.id', $this->selectedIds);
    }
    return Excel::download(new CaratulaExport($dataQuery->get()), 'caratulas.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Caratula::all() : Caratula::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.caratulas.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'caratulas.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
