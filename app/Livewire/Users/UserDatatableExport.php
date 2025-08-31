<?php

namespace App\Livewire\Users;

use App\Livewire\Users\Export\UsersExport;
use App\Livewire\Users\Export\UsersExportFromView;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class UserDatatableExport extends Component
{
  public $search = ''; // Almacena el término de búsqueda
  public $selectedUsers = []; // Almacena los IDs de usuarios seleccionados

  //#[On('updateSelectedUsers')]
  protected $listeners = ['updateSelectedUsers', 'updateSearch'];

  public function updateSelectedUsers($selectedUsers)
  {
    $this->selectedUsers = $selectedUsers;
  }

  public function updateSearch($search)
  {
    $this->search = $search;
  }

  public function prepareExportExcel()
  {
    $usersQuery = User::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedUsers)) {
      $usersQuery->whereIn('users.id', $this->selectedUsers);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new UsersExportFromView($usersQuery->get()), 'users.xlsx');
    //return Excel::download(new UsersExport($usersQuery->get()), 'users.xlsx');
  }

  public function exportCsv()
  {
    $usersQuery = User::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedUsers)) {
      $usersQuery->whereIn('users.id', $this->selectedUsers);
    }
    return Excel::download(new UsersExport($usersQuery->get()), 'users.csv');
  }

  public function exportPdf()
  {
    $users = empty($this->selectedUsers) ? User::all() : User::whereIn('id', $this->selectedUsers)->get();
    $pdf = Pdf::loadView('livewire.user-manager.export.user-pdf', compact('users'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'user.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
