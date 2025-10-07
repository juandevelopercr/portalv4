<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Department;
use App\Models\CentroCosto;
use App\Models\Transaction;
use App\Exports\GeneralReport;
use App\Exports\FacturacionReport;
use Maatwebsite\Excel\Facades\Excel;

class General extends Component
{
  public $filter_date;
  public $filter_centroCosto;
  public $filter_department;
  public $filter_type;
  public $departments;
  public $centrosCosto;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.generales');
  }

  public function mount()
  {
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

    $this->filter_type = 1;

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

    if (empty($this->filter_centroCosto) || is_null($this->filter_centroCosto)){
        $this->filter_centroCosto = CentroCosto::pluck('id');
    }

    $title = $this->getReportName($this->filter_type);

    // Generar y descargar el Excel
    return Excel::download(new GeneralReport(
      [
        'filter_date' => $this->filter_date,
        'filter_centroCosto' => $this->filter_centroCosto,
        'filter_department' => $this->filter_department,
        'filter_type' => $this->filter_type
      ],
      'REPORTE GENERAL ' .$title .' '. $this->filter_date
    ), 'reporte-general-'.$title.'.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }

  public function getReportName($tipo){
    $title = '';
    switch ($tipo) {
      case 1:
        $title = 'con-deposito';
        break;
      case 2:
        $title = 'sin-deposito';
        break;
      case 3:
        $title = 'honoarios';
        break;
      case 4:
        $title = 'gastos';
        break;
    }
    return $title;
  }
}
