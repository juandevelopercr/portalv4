<?php

namespace App\Livewire\Components;

use App\Models\DataTableConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class DatatableSettings extends Component
{
  public $datatableName; // Nombre del datatable
  public $columns; // Configuración actual de las columnas
  public $perPage  = 10;
  public $availableColumns; // Columnas disponibles para el datatable

  public function mount($datatableName, $availableColumns, $perPage)
  {
    $this->datatableName = $datatableName;
    $this->availableColumns = $availableColumns;
    $this->perPage = $perPage;

    // Carga configuración del usuario, o usa la configuración predeterminada
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', $datatableName)
      ->first();

    $this->columns = $config ? $config->columns : $this->availableColumns;
    $this->perPage = $config ? $config->perPage : $this->perPage;
  }

  public function defaultColumns()
  {
    return collect($this->availableColumns)
      ->map(fn($column) => ['key' => $column['key'], 'label' => $column['label'], 'visible' => true])
      ->toArray();
  }

  public function save()
  {
    DataTableConfig::updateOrCreate(
      [
        'user_id' => Auth::id(),
        'datatable_name' => $this->datatableName,
      ],
      [
        'columns' => $this->columns,
        'perPage' => $this->perPage,
      ]
    );

    $this->dispatch('closeModal');
    $this->dispatch('datatableSettingChange');
    $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);
  }

  #[On('updateOrder')]
  public function updateOrder($orderedKeys)
  {
    // Reorganiza las columnas según el nuevo orden recibido desde el frontend
    $newColumns = [];
    foreach ($orderedKeys as $key) {
      foreach ($this->columns as $column) {
        if ($column['field'] === $key) {
          $newColumns[] = $column;
          break;
        }
      }
    }

    // Actualiza el orden de las columnas
    $this->columns = $newColumns;
  }

  public function render()
  {
    return view('livewire.components.datatable-settings');
  }
}
