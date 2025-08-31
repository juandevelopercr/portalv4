<?php

namespace App\Livewire\Comprobantes;

use App\Livewire\Comprobantes\Export\ComprobanteExport;
use App\Livewire\Comprobantes\Export\ComprobanteExportFromView;
use App\Models\Comprobante;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ComprobanteDatatableExport extends Component
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
    $dataQuery = Comprobante::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('comprobantes.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ComprobanteExportFromView($dataQuery->get()), 'comprobantes.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Comprobante::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('comprobantes.id', $this->selectedIds);
    }
    return Excel::download(new ComprobanteExport($dataQuery->get()), 'comprobantes.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Bank::all() : Bank::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.comprobantes.export.data-pdf', compact('records'))
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
