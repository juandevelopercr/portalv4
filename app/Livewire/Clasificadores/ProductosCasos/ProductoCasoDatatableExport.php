<?php

namespace App\Livewire\Clasificadores\ProductosCasos;

use App\Livewire\Clasificadores\CatalogoCuentas\Export\CatalogoCuentaExport;
use App\Livewire\Clasificadores\CatalogoCuentas\Export\CatalogoCuentaExportFromView;
use App\Models\CatalogoCuenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ProductoCasoDatatableExport extends Component
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
    $dataQuery = CatalogoCuenta::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('cuentas.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CatalogoCuentaExportFromView($dataQuery->get()), 'catalogo-cuentas.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CatalogoCuenta::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('cuentas.id', $this->selectedIds);
    }
    return Excel::download(new CatalogoCuentaExport($dataQuery->get()), 'catalogo-cuentas.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CatalogoCuenta::all() : CatalogoCuenta::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.catalogo-cuentas.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'catalogo-cuentas.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
