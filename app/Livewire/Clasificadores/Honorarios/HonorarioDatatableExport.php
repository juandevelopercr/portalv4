<?php

namespace App\Livewire\Clasificadores\Honorarios;

use App\Livewire\Honorarios\Export\HonorarioExport;
use App\Livewire\Honorarios\Export\HonorarioExportFromView;
use App\Models\Honorario;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class HonorarioDatatableExport extends Component
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
    $dataQuery = Honorario::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('honorarios.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new HonorarioExportFromView($dataQuery->get()), 'honorarios.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Honorario::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('honorarios.id', $this->selectedIds);
    }
    return Excel::download(new HonorarioExport($dataQuery->get()), 'honorarios.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Honorario::all() : Honorario::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.honorarios.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'honorarios.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
