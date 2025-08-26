<?php

namespace App\Livewire\Trips;

use App\Livewire\Trips\Export\TripExport;
use App\Livewire\Trips\Export\TripExportFromView;
use App\Models\Trip;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TripDatatableExport extends Component
{
  public $search = ''; // Almacena el término de búsqueda
  public $selectedIds = []; // Almacena los IDs de usuarios seleccionados
  public $filters = [];

  public $sortBy = 'transactions.transaction_date';
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
    $this->sortBy = $data['sortBy'] ?? 'transactions.transaction_date';
    $this->sortDir = $data['sortDir'] ?? 'DESC';
    $this->perPage = $data['perPage'] ?? 10;
  }

  public function prepareExportExcel()
  {
    $dataQuery = Trip::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('trips.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new TripExportFromView($dataQuery->get()), 'trips.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Trip::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('trips.id', $this->selectedIds);
    }
    return Excel::download(new TripExport($dataQuery->get()), 'trips.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Trip::all() : Trip::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.trips.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'trips.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
