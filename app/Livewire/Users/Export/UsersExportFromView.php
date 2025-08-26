<?php

namespace App\Livewire\Users\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UsersExportFromView implements FromView
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function view(): View
    {
        return view('livewire.user-manager.export.user-excel', [
            'users' => $this->users,
        ]);
    }
}
