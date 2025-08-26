<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">@if($this->type == 'customer') {{ __('Customer') }} @else {{ __('Supplier') }} @endif</h5>
        <small class="text-body float-end">Default label</small>
      </div>
      <div class="card-body">
        <div class="col-md-12">
          <div class="nav-align-top nav-tabs-shadow mb-6">
            <ul class="nav nav-tabs nav-fill" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-home" aria-controls="navs-justified-home" aria-selected="true">
                  <span class="d-none d-sm-block"><i
                      class="tf-icons bx bx-info-circle bx-lg me-1_5 align-text-center"></i>
                    {{ __('General Information') }}
                  </span>
                  <i class="bx bx-info-circle bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-contacts" aria-controls="navs-justified-contacts" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-user bx-lg me-1_5 align-text-center"></i>
                    {{ __('Contactos') }}
                  </span>
                  <i class="bx bx-user bx-lg d-sm-none"></i>
                </button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="navs-justified-home" role="tabpanel">
                @include('livewire.contacts.partials._form')
              </div>
              <div class="tab-pane fade" id="navs-justified-contacts" role="tabpanel">
                  @if($this->recordId)
                    @livewire('contacts.contactos.contacto-manager', ['contact_id' =>
                    $this->recordId, 'contactName'=> $this->name],
                    key('contacto-'.($this->recordId ?? uniqid())))
                  @else
                    <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                      <span class="alert-icon rounded-circle">
                        <i class="bx bx-xs bx-wallet"></i>
                      </span>
                      {{ __('Information will be displayed here after you have created the product') }}
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
