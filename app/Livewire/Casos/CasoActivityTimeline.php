<?php

namespace App\Livewire\Casos;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class CasoActivityTimeline extends Component
{
  use WithPagination;

  public $caso_id;

  public function mount($caso_id)
  {
    $this->caso_id = $caso_id;
  }

  #[On('updateCasoContext')]
  public function handleUpdateContext($data)
  {
    $this->caso_id = $data['caso_id'];
    $this->loadActivities();
  }

  public function render()
  {
    $logs = $this->loadActivities();

    return view('livewire.casos.caso-activity-timeline', compact('logs'));
  }

  public function loadActivities()
  {
    return Activity::where('log_name', 'caso')
      ->where('subject_type', \App\Models\Caso::class)
      ->where('subject_id', $this->caso_id)
      ->latest()
      ->paginate(5);
  }
}
