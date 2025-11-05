<?php

namespace App\Livewire\Modals;

use App\Models\Cabys;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CabyModal extends Component
{
  use WithFileUploads;
  use WithPagination;

  protected string $pageName = 'cabysPage'; // nombre único para esta tabla

  public $search = '';

  public $active = '';

  public $sortBy = 'code';

  public $sortDir = 'ASC';

  public $perPage = 10;

  public $modalCabysOpen = false; // Controla el estado del modal

  public $type = NULL;

  //public $cabys;

  protected $listeners = [
    'openCabysModal' => 'openCabysModal',
  ];

  public function openCabysModal(...$params)
  {
      // Livewire envía los parámetros como array, incluso si es solo uno
      $this->resetPage();
      $this->type = $params[0] ?? null;
      $this->modalCabysOpen = true;

      // Depuración
      // dd($this->type);
  }

  public function closeCabysModal()
  {
    $this->modalCabysOpen = false;
  }

  public function selectCabyCode($code)
  {
    // Emite un evento para el componente principal
    // Dispatch para el componente principal
    $this->dispatch('cabyCodeSelected', ['code' => $code]);
    $this->modalCabysOpen = false;
  }

  public function render()
  {
    $cabys = Cabys::search($this->search)
      ->when($this->active !== '', function ($query) {
        $query->where('active', $this->active);
      })
      ->when($this->type == 'single', function ($query) {
            // Filtra códigos cuyo primer dígito sea 0,1,2,3,4
            $query->whereRaw('LEFT(code, 1) BETWEEN 0 AND 4');
        })
        ->when($this->type != 'single', function ($query) {
            // Filtra códigos cuyo primer dígito sea mayor que 4
            $query->whereRaw('LEFT(code, 1) > 4');
      })
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.cabys.caby-modal', compact('cabys'));
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }
}
