<?php

namespace App\Livewire\ProductHonorariosTimbres;

use App\Livewire\ProductHonorariosTimbres\Export\ProductHonorarioTimbreExport;
use App\Livewire\ProductHonorariosTimbres\Export\ProductHonorarioTimbreExportFromView;
use App\Models\ProductHonorariosTimbre;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ProductHonorarioTimbreDatatableExport extends Component
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
    $dataQuery = ProductHonorariosTimbre::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('product_honorarios_timbres.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ProductHonorarioTimbreExportFromView($dataQuery->get()), 'product-honorarios-timbres.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = ProductHonorariosTimbre::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('product_honorarios_timbres.id', $this->selectedIds);
    }
    return Excel::download(new ProductHonorarioTimbreExport($dataQuery->get()), 'product-honorarios-timbres.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? ProductHonorariosTimbre::all() : ProductHonorariosTimbre::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.products-honorarios-timbres.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'product-honorarios-timbres.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
