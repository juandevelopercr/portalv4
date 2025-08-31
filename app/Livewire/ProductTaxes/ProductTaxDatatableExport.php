<?php

namespace App\Livewire\ProductTaxes;

use App\Livewire\ProductTaxes\Export\ProductTaxExport;
use App\Livewire\ProductTaxes\Export\ProductTaxExportFromView;
use App\Models\ProductTax;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ProductTaxDatatableExport extends Component
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
    $dataQuery = ProductTax::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('products_taxes.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ProductTaxExportFromView($dataQuery->get()), 'product-taxes.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = ProductTax::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('products_taxes.id', $this->selectedIds);
    }
    return Excel::download(new ProductTaxExport($dataQuery->get()), 'product-taxes.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? ProductTax::all() : ProductTax::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.products-taxes.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'product-taxes.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
