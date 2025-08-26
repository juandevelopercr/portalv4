<?php

namespace App\Livewire\Clasificadores\ServicesProviders;

use App\Livewire\Clasificadores\ServiceProvider\Export\ServiceProviderExport;
use App\Livewire\Clasificadores\ServicesProviders\Export\ServiceProviderExportFromView;
use App\Models\ServiceProvider;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ServiceProviderDatatableExport extends Component
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
    $dataQuery = ServiceProvider::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('services_providers.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ServiceProviderExportFromView($dataQuery->get()), 'servicios-proveedores.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = ServiceProvider::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('services_providers.id', $this->selectedIds);
    }
    return Excel::download(new ServiceProviderExport($dataQuery->get()), 'servicios-proveedores.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? ServiceProvider::all() : ServiceProvider::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.services-providers.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'servicios-proveedores.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
