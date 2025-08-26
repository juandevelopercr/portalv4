<?php

namespace App\Livewire\Clasificadores\CentroCostos;

use App\Livewire\Clasificadores\CentroCostos\Export\CentroCostoExport;
use App\Livewire\Clasificadores\CentroCostos\Export\CentroCostoExportFromView;
use App\Models\CentroCosto;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CentroCostoDatatableExport extends Component
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
    $dataQuery = CentroCosto::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('centro_costos.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CentroCostoExportFromView($dataQuery->get()), 'centro-costos.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CentroCosto::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('centro_costos.id', $this->selectedIds);
    }
    return Excel::download(new CentroCostoExport($dataQuery->get()), 'centro-costos.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CentroCosto::all() : CentroCosto::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadorescentro-costos.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'centro-costos.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
