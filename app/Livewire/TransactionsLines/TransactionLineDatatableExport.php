<?php

namespace App\Livewire\TransactionsLines;

use App\Livewire\TransactionsLines\Export\TransactionLineExport;
use App\Livewire\TransactionsLines\Export\TransactionLineExportFromView;
use App\Models\TransactionLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TransactionLineDatatableExport extends Component
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

  public function exportExcel()
  {
    $dataQuery = TransactionLine::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('transactions_lines.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new TransactionLineExportFromView($dataQuery->get()), 'transaction-lines.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = TransactionLine::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('transactions-lines.id', $this->selectedIds);
    }
    return Excel::download(new TransactionLineExport($dataQuery->get()), 'transaction-lines.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? TransactionLine::all() : TransactionLine::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.transactions-lines.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'transactions-lines.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
