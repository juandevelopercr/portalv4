<?php

namespace App\Livewire\MovimientoNotifications;

use App\Livewire\MovimientoNotifications\Export\MovimientoNotificationExport;
use App\Livewire\MovimientoNotifications\Export\MovimientoNotificationExportFromView;
use App\Models\MovimientoNotificacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class MovimientoNotificationDatatableExport extends Component
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
    $dataQuery = MovimientoNotificacion::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('movimientos_notificaciones.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new MovimientoNotificationExportFromView($dataQuery->get()), 'movimientos-notificaciones.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = MovimientoNotificacion::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('movimientos_notificaciones.id', $this->selectedIds);
    }
    return Excel::download(new MovimientoNotificationExport($dataQuery->get()), 'movimientos-notificaciones.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? MovimientoNotificacion::all() : MovimientoNotificacion::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.movimientos-notifications.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'movimientos-notificaciones.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
