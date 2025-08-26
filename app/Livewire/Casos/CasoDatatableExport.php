<?php

namespace App\Livewire\Casos;

use App\Livewire\Casos\Export\CasoExport;
use App\Livewire\Casos\Export\CasoExportFromView;
use App\Models\Caso;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CasoDatatableExport extends Component
{
  public $search = ''; // Almacena el término de búsqueda
  public $selectedIds = []; // Almacena los IDs de usuarios seleccionados
  public $filters = [];

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
    ], now()->addMinutes(5));

    $url = route('exportacion.casos.preparar', ['key' => $key]);
    $downloadBase = '/descargar-exportacion-casos';
    $this->dispatch('exportReady', ['prepareUrl' => $url, 'downloadBase' => $downloadBase]);
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
