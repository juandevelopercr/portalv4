<?php

namespace App\Livewire\Products;

use App\Livewire\Products\Export\ProductExport;
use App\Livewire\Products\Export\ProductExportFromView;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ProductDatatableExport extends Component
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
    $dataQuery = Product::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('products.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ProductExportFromView($dataQuery->get()), 'products.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Product::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('products.id', $this->selectedIds);
    }
    return Excel::download(new ProductExport($dataQuery->get()), 'products.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Product::all() : Product::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.products.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'products.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
