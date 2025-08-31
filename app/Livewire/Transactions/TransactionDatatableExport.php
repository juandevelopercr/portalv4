<?php

namespace App\Livewire\Transactions;

use App\Livewire\Transactions\Export\TransactionExportFromView;
use App\Livewire\Transactions\Export\TransactionExport;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TransactionDatatableExport extends Component
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
    $this->prepareExportTransaction();
  }

  private function prepareExportTransaction()
  {
    $key = uniqid('export_', true);

    cache()->put($key, [
      'search' => $this->search,
      'filters' => $this->filters,
      'selectedIds' => $this->selectedIds,
      'sortBy' => $this->sortBy,
      'sortDir' => $this->sortDir,
      'perPage' => $this->perPage,
    ], now()->addMinutes(5));

    $url = route('exportacion.transacciones.preparar', ['key' => $key]);
    $downloadBase = '/descargar-exportacion-transacciones';
    $this->dispatch('exportReady', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
