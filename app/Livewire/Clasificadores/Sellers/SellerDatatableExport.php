<?php

namespace App\Livewire\Clasificadores\Sellers;

use App\Livewire\Clasificadores\Sellers\Export\SellerExport;
use App\Livewire\Clasificadores\Sellers\Export\SellerExportFromView;
use App\Models\Seller;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SellerDatatableExport extends Component
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
    $dataQuery = Seller::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('sellers.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new SellerExportFromView($dataQuery->get()), 'sellers.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Seller::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('sellers.id', $this->selectedIds);
    }
    return Excel::download(new SellerExport($dataQuery->get()), 'sellers.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Seller::all() : Seller::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.sellers.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'sellers.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
