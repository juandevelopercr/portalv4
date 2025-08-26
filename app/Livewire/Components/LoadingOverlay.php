<?php

namespace App\Livewire\Components;

use Livewire\Attributes\On;
use Livewire\Component;

class LoadingOverlay extends Component
{
  public $message = 'Procesando...';
  public $visible = false;

  #[On('showLoading')]
  public function showLoading($payload)
  {
    $this->message = $payload['message'] ?? 'Cargando...';
    $this->visible = true;
  }

  #[On('updateLoadingMessage')]
  public function updateLoadingMessage($payload)
  {
    $this->message = $payload['message'] ?? 'Cargando...';
  }

  #[On('hideLoading')]
  public function hideLoading()
  {
    $this->visible = false;
  }

  public function render()
  {
    return view('livewire.components.loading-overlay');
  }
}
