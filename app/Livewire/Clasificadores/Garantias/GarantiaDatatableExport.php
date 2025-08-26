<?php

namespace App\Livewire\Clasificadores\Garantias;

use App\Livewire\Clasificadores\Garantias\Export\GarantiaExport;
use App\Livewire\Clasificadores\Garantias\Export\GarantiaExportFromView;
use App\Models\Garantia;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class GarantiaDatatableExport extends Component
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
    $dataQuery = Garantia::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('garantias.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new GarantiaExportFromView($dataQuery->get()), 'garantias.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Garantia::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('garantias.id', $this->selectedIds);
    }
    return Excel::download(new GarantiaExport($dataQuery->get()), 'garantias.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Garantia::all() : Garantia::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.garantias.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'garantias.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
