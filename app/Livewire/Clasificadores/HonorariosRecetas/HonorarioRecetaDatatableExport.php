<?php

namespace App\Livewire\Clasificadores\HonorariosRecetas;

use App\Livewire\Clasificadores\HonorariosRecetas\Export\HonorarioRecetaExport;
use App\Models\HonorarioReceta;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class HonorarioRecetaDatatableExport extends Component
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
    $dataQuery = HonorarioReceta::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('honorarios-banks.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new HonorarioRecetaExport($dataQuery->get()), 'honorario-recetas.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = HonorarioReceta::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('honorarios-recetas.id', $this->selectedIds);
    }
    return Excel::download(new HonorarioRecetaExport($dataQuery->get()), 'honorario-recetas.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? HonorarioReceta::all() : HonorarioReceta::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.honorarios-recetas.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'honorario-recetas.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
