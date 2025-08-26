<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->

<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <form wire:submit.prevent="update" class="card-body">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            {{ __('Actualizar Calculo de Registro') }}: No. {{ $this->proforma_no }}
          </h5>
          <small class="text-body float-end">Default label</small>
        </div>
        <div class="card-body">
            @include('livewire.transactions.partials._form_info_calculo_registro')
        </div>

        <div class="card-body">
          <div class="col-md-12">
            <div class="nav-align-top nav-tabs-shadow mb-6">
              @include('livewire.transactions.partials._form_calculo_registro')
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@if($modalCustomerOpen)
<div id="customer-modal" class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Select Customer') }}</h5>
        <button type="button" class="btn-close" wire:click="closeCustomerModal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @livewire('contacts.contact-manager', [
                    'enabledSelectedValue' => true,
                    'type' => 'customer'
                  ],
                  key('contact-manager'.$this->recordId))
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">
          {{ __('Close') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endif
