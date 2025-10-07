<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Department;
use App\Models\CentroCosto;
use App\Models\Transaction;
use App\Exports\RegistroReport;
use Maatwebsite\Excel\Facades\Excel;

class Registro extends Component
{
  public $filter_date;
  public $filter_centroCosto;
  public $filter_department;
  public $departments;
  public $centrosCosto;

  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.registro');
  }

  public function mount()
  {
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

    $this->centrosCosto = CentroCosto::orderBy('descrip', 'ASC')->get();

    //Banca Retail Normal
    $this->filter_centroCosto = 1;

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

    if (empty($this->filter_centroCosto) || is_null($this->filter_centroCosto))
      $idsCostos = $this->centrosCosto->pluck('id');
    else
      $idsCostos = $this->filter_centroCosto;

    // Generar y descargar el Excel
    return Excel::download(new RegistroReport(
      [
        'filter_date' => $this->filter_date,
        'filter_centroCosto' => $this->filter_centroCosto,
        'filter_department' => $this->filter_department,
        'idsCostos' => $idsCostos,
      ],
      'REPORTE DE FACTURACIÓN ' . $this->filter_date
    ), 'reporte-registro.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
