<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          @if ($this->proforma_no)
          {{ __('Update Proforma') }}: No. {{ $this->proforma_no }}
          @else
          {{ __('Create Proforma') }}
          @endif
        </h5>
        <small class="text-body float-end">Default label</small>
      </div>
      @php
      //dd($this->tabproducts);
      @endphp
      <div class="card-body">
        <div class="col-md-12">
          <div class="nav-align-top nav-tabs-shadow mb-6">
            <ul class="nav nav-tabs nav-fill" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link @if ($this->activeTab == 'invoice') show active @endif" role="tab"
                  data-bs-toggle="tab" data-bs-target="#navs-justified-home" aria-controls="navs-justified-home"
                  aria-selected="true">
                  <span class="d-none d-sm-block"><i
                      class="tf-icons bx bx-info-circle bx-lg me-1_5 align-text-center"></i>
                    {{ __('General Information') }}
                  </span>
                  <i class="bx bx-info-circle bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link @if ($this->activeTab == 'product') show active @endif" role="tab"
                  data-bs-toggle="tab" data-bs-target="#navs-justified-services" aria-controls="navs-justified-services"
                  aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-cog bx-lg me-1_5 align-text-center"></i>
                    {{ __('Services') }}
                    <?php
                    /*
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5 pt-50">3</span>
                    */
                    ?>
                  </span>
                  <i class="bx bx-cog bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link @if ($this->activeTab == 'charges') show active @endif" role="tab"
                  data-bs-toggle="tab" data-bs-target="#navs-justified-charge" aria-controls="navs-justified-charge"
                  aria-selected="true">
                  <span class="d-none d-sm-block"><i class="tf-icons bx bx-dollar bx-lg me-1_5 align-text-center"></i>
                    {{ __('Other Charge') }}
                  </span>
                  <i class="bx bx-dollar bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-document" aria-controls="navs-justified-document"
                  aria-selected="true">
                  <span class="d-none d-sm-block"><i class="tf-icons bx bx-file bx-lg me-1_5 align-text-center"></i>
                    {{ __('Attached Documents') }}
                  </span>
                  <i class="bx bx-file bx-lg d-sm-none"></i>
                </button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show @if ($this->activeTab == 'invoice') show active @endif"
                id="navs-justified-home" role="tabpanel">
                @include('livewire.transactions.partials._form-invoice')
              </div>
              <div class="tab-pane fade @if ($this->activeTab == 'product') show active @endif"
                id="navs-justified-services" role="tabpanel">
                <div class="{{ $this->recordId ? '' : 'd-none' }}">
                  @livewire('transactions-lines.transaction-line-manager', [
                    'canview'   => auth()->user()->can('view-lineas-electronicinvoices'),
                    'cancreate' => auth()->user()->can('create-lineas-electronicinvoices'),
                    'canedit'   => auth()->user()->can('edit-lineas-electronicinvoices'),
                    'candelete' => auth()->user()->can('delete-lineas-electronicinvoices'),
                    'canexport' => auth()->user()->can('export-lineas-electronicinvoices')
                  ])
                </div>

                <div class="{{ $this->recordId ? 'd-none' : '' }}">
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the proforma') }}
                  </div>
                </div>
              </div>
              <div class="tab-pane fade @if ($this->activeTab == 'charges') show active @endif"
                id="navs-justified-charge" role="tabpanel">
                <div class="{{ $this->recordId ? '' : 'd-none' }}">
                  @livewire('transactions-charges.transaction-charge-manager', [
                    'transaction_id' => $this->recordId,
                    'canview'   => auth()->user()->can('view-cargos-electronicinvoices'),
                    'cancreate' => auth()->user()->can('create-cargos-electronicinvoices'),
                    'canedit'   => auth()->user()->can('edit-cargos-electronicinvoices'),
                    'candelete' => auth()->user()->can('delete-cargos-electronicinvoices'),
                    'canexport' => auth()->user()->can('export-cargos-electronicinvoices'),
                  ])
                </div>
                <div class="{{ $this->recordId ? 'd-none' : '' }}">
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the proforma') }}
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-justified-document" role="tabpanel">
                @if($this->recordId)
                @livewire('transactions.documents-manager', [
                    'transaction_id' => $this->recordId,
                    'onlyview' => false,
                    'canview'   => auth()->user()->can('view-documento-electronicinvoices'),
                    'cancreate' => auth()->user()->can('create-documento-electronicinvoices'),
                    'canedit'   => auth()->user()->can('edit-documento-electronicinvoices'),
                    'candelete' => auth()->user()->can('delete-documento-electronicinvoices'),
                    'canexport' => auth()->user()->can('export-documento-electronicinvoices'),

                  ], key('transaction-documents-'.$this->recordId))
                @else
                <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                  <span class="alert-icon rounded-circle">
                    <i class="bx bx-xs bx-wallet"></i>
                  </span>
                  {{ __('Information will be displayed here after you have created the proforma') }}
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div>
  @if($modalCustomerOpen)
  <div class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Select Customer') }}</h5>
          <button type="button" class="btn-close" wire:click="closeCustomerModal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <livewire:contacts.contact-manager enabledSelectedValue=true />
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
</div>
