<?php

namespace App\Livewire\Reports;

use App\Models\User;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Transaction;
use App\Exports\ComisionReport;
use App\Models\Comisionista;
use Maatwebsite\Excel\Facades\Excel;

class Comision extends Component
{
  public $filter_date;
  public $filter_abogado;
  public $filter_department;
  public $filter_currency;
  public $filter_type;
  public $filter_pagar;
  public $departments;
  public $status;
  public $comisionistas;
  public $currencies;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.comisiones');
  }

  public function mount()
  {
    $this->filter_type = 1;
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

    $this->comisionistas = Comisionista::where('active', 1)
        ->orderBy('nombre', 'ASC')
        ->get();

    $this->status = $this->getStatusOptions();

    $this->currencies = Currency::orderBy('code', 'ASC')->get();

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
    return Excel::download(new ComisionReport(
      [
        'filter_date' => $this->filter_date,
        'filter_abogado' => $this->filter_abogado,
        'filter_department' => $this->filter_department,
        'filter_currency' => $this->filter_currency,
        'filter_type' => $this->filter_type,
        'filter_pagar' => $this->filter_pagar
      ],
      'REPORTE DE COMISIONES ' . $this->filter_date
    ), 'reporte-comisiones.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }

  public function updatedFilterPagar($value)
  {
    $this->filter_pagar = (int) $value;
  }
}
