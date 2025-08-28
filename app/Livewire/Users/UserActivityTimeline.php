<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class UserActivityTimeline extends Component
{
  public $activities;

  public function mount()
  {
    $this->fetchActivities();
  }

  public function fetchActivities()
  {
    // Trae las últimas 50 actividades, personaliza según sea necesario
    $this->activities = Activity::latest()->limit(50)->get();
  }

  public function render()
  {
    return view('livewire.user-manager.user-activity-timeline');
  }
}
