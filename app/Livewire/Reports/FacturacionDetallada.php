<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Department;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturacionDetalladaReport;

class FacturacionDetallada extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_department;
  public $filter_status;
  public $departments;
  public $status;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.facturacion-detallada');
  }

  public function mount()
  {
    $this->filter_status = Transaction::FACTURADA;
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

    $this->status = $this->getStatusOptions();

    $this->dispatch('reinitFormControls');
  }

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'dateRangeSelected' => 'dateRangeSelected',
    // 'dateSelected' => 'handleDateSelected',
  ];

  public function dateRangeSelected($id, $range)
  {
    $this->$id = $range;
  }

  public function getStatusOptions()
  {
    // Retornar los estados
    $is_invoice = false;

    $estados = Transaction::getStatusOptionsforReports($is_invoice);
    return $estados;
  }

  public function exportExcel()
  {
    $this->loading = true;

    // Generar y descargar el Excel
    return Excel::download(new FacturacionDetalladaReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_department' => $this->filter_department,
        'filter_status' => $this->filter_status
      ],
      'REPORTE DE FACTURACIÓN DETALLADA' . $this->filter_date
    ), 'reporte-facturacion-detallada.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
