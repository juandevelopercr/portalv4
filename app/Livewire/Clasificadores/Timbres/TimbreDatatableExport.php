<?php

namespace App\Livewire\Clasificadores\Timbres;

use App\Livewire\Clasificadores\Timbres\Export\TimbreExport;
use App\Livewire\Clasificadores\Timbres\Export\TimbreExportFromView;
use App\Models\Timbre;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TimbreDatatableExport extends Component
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
    $dataQuery = Timbre::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('timbres.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new TimbreExportFromView($dataQuery->get()), 'timbres.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Timbre::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('timbres.id', $this->selectedIds);
    }
    return Excel::download(new TimbreExport($dataQuery->get()), 'timbres.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Timbre::all() : Timbre::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.timbres.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'timbres.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
