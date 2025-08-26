<?php

namespace App\Livewire\Clasificadores\Sectores;

use App\Livewire\Clasificadores\Sectores\Export\SectorExport;
use App\Livewire\Clasificadores\Sectores\Export\SectorExportFromView;
use App\Models\Sector;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SectorDatatableExport extends Component
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
    $dataQuery = Sector::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('sectores.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new SectorExportFromView($dataQuery->get()), 'sectores.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Sector::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('sectores.id', $this->selectedIds);
    }
    return Excel::download(new SectorExport($dataQuery->get()), 'sectores.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Sector::all() : Sector::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.sectores.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'sectores.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
