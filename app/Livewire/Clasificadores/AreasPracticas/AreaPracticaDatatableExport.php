<?php

namespace App\Livewire\Clasificadores\AreasPracticas;

use App\Livewire\Clasificadores\AreasPracticas\Export\AreaPracticaExport;
use App\Livewire\Clasificadores\AreasPracticas\Export\AreaPracticaExportFromView;
use App\Models\AreaPractica;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class AreaPracticaDatatableExport extends Component
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
    $dataQuery = AreaPractica::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('areas_practicas.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new AreaPracticaExportFromView($dataQuery->get()), 'areas-practicas.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = AreaPractica::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('areas_practicas.id', $this->selectedIds);
    }
    return Excel::download(new AreaPracticaExport($dataQuery->get()), 'areas-practicas.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? AreaPractica::all() : AreaPractica::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.areas-practicas.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'areas-practicas.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
