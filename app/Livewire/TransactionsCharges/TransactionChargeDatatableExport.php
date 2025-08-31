<?php

namespace App\Livewire\TransactionsCharges;

use App\Livewire\TransactionsCharges\Export\TransactionChargeExport;
use App\Livewire\TransactionsCharges\Export\TransactionChargeExportFromView;
use App\Models\TransactionOtherCharge;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TransactionChargeDatatableExport extends Component
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
    $dataQuery = TransactionOtherCharge::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('transactions_other_charges.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new TransactionChargeExportFromView($dataQuery->get()), 'transactions-other-charges.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = TransactionOtherCharge::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('transactions_other_charges.id', $this->selectedIds);
    }
    return Excel::download(new TransactionChargeExport($dataQuery->get()), 'transactions-other-charges.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? TransactionOtherCharge::all() : TransactionOtherCharge::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.transactions-charges.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'transactions-other-charges.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
