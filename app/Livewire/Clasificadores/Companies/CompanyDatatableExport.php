<?php

namespace App\Livewire\Clasificadores\Companies;

use App\Livewire\Clasificadores\Companies\Export\CompanyExport;
use App\Livewire\Clasificadores\Companies\Export\CompanyExportFromView;
use App\Models\ProviderCompany;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CompanyDatatableExport extends Component
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
    $dataQuery = ProviderCompany::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('companies.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CompanyExportFromView($dataQuery->get()), 'companies.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = ProviderCompany::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('companies.id', $this->selectedIds);
    }
    return Excel::download(new CompanyExport($dataQuery->get()), 'companies.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? ProviderCompany::all() : ProviderCompany::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.companies.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'towns.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
