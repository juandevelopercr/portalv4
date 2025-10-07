<?php

namespace App\Livewire\Reports;

use App\Exports\CustomersReport;
use App\Models\Department;
use App\Models\Transaction;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerReport extends Component
{
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.customer');
  }

  public function mount()
  {
    $this->dispatch('reinitFormControls');
  }

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  public function dateRangeSelected($id, $range)
  {
    $this->$id = $range;
  }

  public function exportExcel()
  {
    $this->loading = true;

    // Generar y descargar el Excel
    return Excel::download(new CustomersReport(
      [],
      'REPORTE DE CLIENTES'
    ), 'reporte-clientes.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
