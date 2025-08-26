<?php

namespace App\Livewire\TransactionsCommissions;

use App\Livewire\TransactionsCommissions\Export\TransactionCommissionExport;
use App\Livewire\TransactionsCommissions\Export\TransactionCommissionExportFromView;
use App\Models\TransactionCommission;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TransactionCommissionDatatableExport extends Component
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
    $dataQuery = TransactionCommission::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('transactions_commissions.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new TransactionCommissionExportFromView($dataQuery->get()), 'transactions-commissions.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = TransactionCommission::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('transactions_commissions.id', $this->selectedIds);
    }
    return Excel::download(new TransactionCommissionExport($dataQuery->get()), 'transactions-commissions.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? TransactionCommission::all() : TransactionCommission::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.transactions-commissions.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'transactions-commissions.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
