<?php

namespace App\Livewire;

use App\Models\Bank;
use App\Models\DataTableConfig;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

abstract class BaseComponent extends Component
{
  public array $selectedIds = [];
  public bool $selectAll = false;

  // Este método debe ser overrideado por cada hijo que defina su modelo
  abstract protected function getModelClass(): string;

  public function updatedSelectedIds()
  {
    // Emite los IDs seleccionados cada vez que se actualice la selección
    $this->dispatch('updateSelectedIds', $this->selectedIds);
  }

  public function toggleSelectAll(): void
  {
    $this->selectAll = !$this->selectAll;

    if ($this->selectAll) {
      // Obtener el modelo base
      $model = $this->getModelClass();

      // Ejecutar la misma consulta que se usa en render()
      $query = $model::search($this->search, $this->filters)
        ->orderBy($this->sortBy, $this->sortDir);

      // Paginar y obtener solo los IDs de la página actual
      $this->selectedIds = $query->paginate($this->perPage)->pluck('id')->toArray();
    } else {
      $this->selectedIds = [];
    }

    $this->dispatch('updateSelectedIds', $this->selectedIds);
  }

  public function getRecordAction($recordId)
  {
    if (!isset($recordId) || is_null($recordId)) {
      if (empty($this->selectedIds)) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => 'Debe seleccionar un registro.'
        ]);
        return;
      }

      if (count($this->selectedIds) > 1) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => 'Solo se permite seleccionar un registro.'
        ]);
        return;
      }

      if (count($this->selectedIds) == 1) {
        $recordId = $this->selectedIds[0];
      }
    }

    return $recordId;
  }

  public function getRecordListAction()
  {
    if (empty($this->selectedIds)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => 'Debe seleccionar al menos un registro.'
      ]);
      return;
    }

    return $this->selectedIds;
  }
}
