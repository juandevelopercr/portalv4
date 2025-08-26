<?php

namespace App\Livewire\Clasificadores\Cuentas;

use App\Livewire\Clasificadores\Cuentas\Export\CuentaExport;
use App\Livewire\Clasificadores\Cuentas\Export\CuentaExportFromView;
use App\Models\Cuenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CuentaDatatableExport extends Component
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
    $dataQuery = Cuenta::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('cuentas.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CuentaExportFromView($dataQuery->get()), 'cuentas.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Cuenta::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('cuentas.id', $this->selectedIds);
    }
    return Excel::download(new CuentaExport($dataQuery->get()), 'cuentas.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Cuenta::all() : Cuenta::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.cuentas.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'cuentas.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
