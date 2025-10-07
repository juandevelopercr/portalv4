<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Transaction;
use App\Exports\FacturacionReport;
use Maatwebsite\Excel\Facades\Excel;

class Facturacion extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_status;
  public $filter_type;
  public $filter_currency;
  public $filter_tax_type;
  public $status;
  public $currencies;
  public $tax_types;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.facturacion');
  }

  public function mount()
  {
    $this->filter_status = Transaction::ACEPTADA;

    $this->status = $this->getStatusOptions();

    $this->currencies = Currency::orderBy('code', 'ASC')->get();

    $this->tax_types = [['id'=>'GRAVADO', 'name'=>'GRAVADO'], ['id'=>'EXONERADO', 'name'=>'EXONERADO']];

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
    return Excel::download(new FacturacionReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_type' => $this->filter_type,
        'filter_status' => $this->filter_status,
        'filter_currency' => $this->filter_currency,
        'filter_tax_type' => $this->filter_tax_type
      ],
      'REPORTE DE FACTURACIÓN ' . $this->filter_date
    ), 'reporte-facturacion.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
