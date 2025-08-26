<?php

namespace App\Livewire\ProviderAndSellers;

use App\Livewire\ProviderAndSellers\Export\ProviderAndSellerExport;
use App\Livewire\ProviderAndSellers\Export\ProviderAndSellerExportFromView;
use App\Models\ProviderAndSeller;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ProviderAndSellerDatatableExport extends Component
{
  public $search = ''; // Almacena el término de búsqueda
  public $selectedIds = []; // Almacena los IDs de usuarios seleccionados
  public $filters = [];

  public $sortBy = 'providers_sellers.id';
  public $sortDir = 'DESC';
  public $perPage = 10;

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

  #[On('updateExportFilters')]
  public function actualizarFiltros($data)
  {
    $this->search = $data['search'] ?? '';
    $this->filters = $data['filters'] ?? [];
    $this->selectedIds = $data['selectedIds'] ?? [];
    $this->sortBy = $data['sortBy'] ?? 'providers_sellers.id';
    $this->sortDir = $data['sortDir'] ?? 'DESC';
    $this->perPage = $data['perPage'] ?? 10;
  }

  public function prepareExportExcel()
  {
    $dataQuery = ProviderAndSeller::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('providers_sellers.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ProviderAndSellerExportFromView($dataQuery->get()), 'providers_sellers.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = ProviderAndSeller::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('providers_sellers.id', $this->selectedIds);
    }
    return Excel::download(new ProviderAndSellerExport($dataQuery->get()), 'providers_sellers.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? ProviderAndSeller::all() : ProviderAndSeller::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.providers-and-sellers.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'providers_sellers.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
