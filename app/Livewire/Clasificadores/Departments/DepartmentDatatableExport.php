<?php

namespace App\Livewire\Clasificadores\Departments;

use App\Livewire\Clasificadores\Departments\Export\DepartmentExport;
use App\Livewire\Clasificadores\Departments\Export\DepartmentExportFromView;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentDatatableExport extends Component
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
    $dataQuery = Department::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('cuentas.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new DepartmentExportFromView($dataQuery->get()), 'departments.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Department::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('departments.id', $this->selectedIds);
    }
    return Excel::download(new DepartmentExport($dataQuery->get()), 'departments.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Department::all() : Department::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.departments.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'departments.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
