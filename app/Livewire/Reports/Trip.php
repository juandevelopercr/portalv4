<?php

namespace App\Livewire\Reports;

use App\Models\Town;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Department;
use App\Exports\TripReport;
use App\Models\Transaction;
use App\Exports\FacturacionReport;
use Maatwebsite\Excel\Facades\Excel;

class Trip extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_town;
  public $filter_type;
  public $filter_status;

  public $status;
  public $towns;
  public $types;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.trips');
  }

  public function mount()
  {
    $this->status = [['id' => 'INICIADO', 'name' => 'INICIADO'], ['id' => 'FINALIZADO', 'name' => 'FINALIZADO'], ['id' => 'ANULADO', 'name' => 'ANULADO']];

    $this->types = [['id' => 'DIARIO', 'name' => 'DIARIO'], ['id' => 'PRIVADO', 'name' => 'PRIVADO'], ['id' => 'SERVICIOTAXI', 'name' => 'SERVICIO DE TAXI']];

    $this->towns = Town::orderBy('name')->get();

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
    return Excel::download(new TripReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_town' => $this->filter_town,
        'filter_type' => $this->filter_type,
        'filter_status' => $this->filter_status,
      ],
      'REPORTE DE VIAJES ' . $this->filter_date
    ), 'reporte-viajes.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
