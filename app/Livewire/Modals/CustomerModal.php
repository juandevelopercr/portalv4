<?php

namespace App\Livewire\Modals;

use App\Models\Cabys;
use App\Models\Contact;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CustomerModal extends Component
{
  use WithFileUploads;
  use WithPagination;

  public $search = '';

  public $active = '';

  public $sortBy = 'name';

  public $sortDir = 'ASC';

  public $perPage = 10;

  public $modalCustomerOpen = false; // Controla el estado del modal

  protected $listeners = [
    'openCustomerModal' => 'openCustomerModal',
  ];

  public function openCustomerModal()
  {
    $this->modalCustomerOpen = true;
  }

  public function closeCustomerModal()
  {
    $this->modalCustomerOpen = false;
  }

  public function selectCustomerData($id)
  {
    // Emite un evento para el componente principal
    // Dispatch para el componente principal
    try {
      // Buscar el cliente
      $customer = Contact::findOrFail($id);

      $data = [
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_comercial_name' => $customer->customer_comercial_name,
        'customer_email' => $customer->email,
        'email_cc' => $customer->email_cc,
        'condition_sale' => !is_null($customer->conditionSale) ? $customer->conditionSale->code : '',
        'pay_term_number' => (int)$customer->pay_term_number,
        'identification_type_id' => $customer->identification_type_id,
        'tipoIdentificacion' => $customer->identificationType->name,
        'identification' => $customer->identification
      ];
      // Emitir el evento con los datos del cliente
      $this->dispatch('customerSelected', $data);
    } catch (ModelNotFoundException $e) {
      // Si el cliente no existe, enviar datos vacíos
      $data = [
        'customer_id' => '',
        'customer_name' => '',
        'customer_comercial_name' => '',
        'customer_email' => '',
        'email_cc' => '',
        'condition_sale' => '',
        'pay_term_number' => '',
        'identification_type_id' => '',
        'tipoIdentificacion' => '',
        'identification' => ''
      ];

      $this->dispatch('customerSelected', $data);
    }

    // Cerrar el modal en cualquier caso
    $this->modalCustomerOpen = false;
  }

  public function render()
  {
    $customers = Contact::search($this->search)
      ->when($this->active !== '', function ($query) {
        $query->where('contacts.active', $this->active);
      })
      ->where('type', '=', 'customer')
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.customers.customer-modal', compact('customers'));
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
