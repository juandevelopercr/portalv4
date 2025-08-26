<?php

namespace App\Livewire\Movimientos;

use Illuminate\Support\Facades\Log;
use App\Livewire\Movimientos\Export\MovimientoExport;
use App\Livewire\Movimientos\Export\MovimientoExportFromView;
use App\Models\Movimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class MovimientoDatatableExport extends Component
{
  public $search = ''; // Almacena el término de búsqueda
  public $selectedIds = []; // Almacena los IDs de usuarios seleccionados
  public $filters = [];
  public $defaultStatus = null;
  public $filterFecha;
  public $filterCuentas = [];

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
    $this->filterFecha = $data['filterFecha'] ?? '';
    $this->filterCuentas = $data['filterCuentas'] ?? [];
    $this->selectedIds = $data['selectedIds'] ?? [];
    $this->defaultStatus = $data['defaultStatus'] ?? null;
  }

  /*
  public function prepareExportExcel()
  {
    $key = uniqid('export_', true);

    cache()->put($key, [
      'search' => $this->search,
      'filters' => $this->filters,
      'selectedIds' => $this->selectedIds,
      'defaultStatus' => $this->defaultStatus,
    ], now()->addMinutes(5));

    $url = route('movimientos.exportar.descarga', ['key' => $key]);

    $this->dispatch('exportReady', $url); // <-- Esto solo lanza un evento JS
  }
    */
  public function prepareExportExcel()
  {
    $key = uniqid('export_', true);

    cache()->put($key, [
      'search' => $this->search,
      'filters' => $this->filters,
      //'filterFecha' => $this->filterFecha,
      //'filterCuentas' => $this->filterCuentas,
      'selectedIds' => $this->selectedIds,
      'defaultStatus' => $this->defaultStatus,
    ], now()->addMinutes(5));

    $url = route('exportacion.movimientos.preparar', ['key' => $key]);

    $this->dispatch('exportReady', $url);
  }

  /*
  public function exportExcel()
  {
    $this->dispatch('showLoading', 'Procesando exportación...');
    try {
      $dataQuery = Movimiento::search($this->search, $this->filters, $this->defaultStatus);

      if (!empty($this->selectedIds)) {
        $dataQuery->whereIn('movimientos.id', $this->selectedIds);
      }

      $this->dispatch('iniciarDescarga');

      return Excel::download(
        new MovimientoExportFromView($dataQuery),
        'movimientos-' . now()->format('Ymd_His') . '.xlsx'
      );
    } catch (\Throwable $e) {
      // ✅ Ocultar overlay en caso de error
      $this->dispatch('hideLoading');
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while generating the report') . ' ' . $e->getMessage()
      ]);
      return null;
    }
  }
  */

  /*
  public function exportExcel()
  {
    $this->dispatch('showLoading', 'Procesando exportación...');

    try {
      $dataQuery = Movimiento::search($this->search, $this->filters, $this->defaultStatus);

      if (!empty($this->selectedIds)) {
        $dataQuery->whereIn('movimientos.id', $this->selectedIds);
      }

      // Guarda filtros en cache o genera un token temporal
      $key = uniqid('export_', true);

      cache()->put($key, [
        'search' => $this->search,
        'filters' => $this->filters,
        'selectedIds' => $this->selectedIds,
        'defaultStatus' => $this->defaultStatus
      ], now()->addMinutes(5));

      Log::info("INTENTO DESCARGA DESDE PHP CON CLAVE: $key");

      //     dd(route('movimientos.exportar.descarga', ['key' => $key]));

      // Emite evento JS para que frontend descargue vía GET

      //$this->dispatch('startExportDownload', [
       // 'url' => route('movimientos.exportar.descarga', ['key' => $key])
      //]);


      $this->dispatch('startExportDownload', route('movimientos.exportar.descarga', ['key' => $key]));
    } catch (\Throwable $e) {
      $this->dispatch('hideLoading');
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('Error al exportar: ') . $e->getMessage()
      ]);
    }
  }
  */

  public function prepareExportPdf()
  {
    $key = uniqid('export_', true);

    cache()->put($key, [
      'search' => $this->search,
      'filters' => $this->filters,
      'selectedIds' => $this->selectedIds,
      'defaultStatus' => $this->defaultStatus,
    ], now()->addMinutes(5));

    $url = route('movimientos.exportar.descarga-pdf', ['key' => $key]);

    $this->dispatch('exportReady', $url); // <-- Esto solo lanza un evento JS
  }

  /*
  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Movimiento::all() : Movimiento::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.movimientos.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'movimientos.pdf');
  }
  */

  public function exportCsv()
  {
    $dataQuery = Movimiento::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('movimientos.id', $this->selectedIds);
    }
    return Excel::download(new MovimientoExport($dataQuery->get()), 'movimientos.csv');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
