<?php

namespace App\Livewire\Clasificadores\CasosEstados;

use App\Livewire\Clasificadores\CasosEstados\Export\CasoEstadoExport;
use App\Livewire\Clasificadores\CasosEstados\Export\CasoEstadoExportFromView;
use App\Models\CasoEstado;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CasoEstadoDatatableExport extends Component
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
    $dataQuery = CasoEstado::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-estados.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoEstadoExportFromView($dataQuery->get()), 'CasoEstados.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoEstado::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-estados.id', $this->selectedIds);
    }
    return Excel::download(new CasoEstadoExport($dataQuery->get()), 'casos-estados.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoEstado::all() : CasoEstado::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-estados.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-estados.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
