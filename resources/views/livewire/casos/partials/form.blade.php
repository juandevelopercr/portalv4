<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Caso Information') }}</h5>
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
              <!-- Facturas asociadas -->
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-invoice" aria-controls="navs-justified-invoice" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-receipt bx-lg me-1_5 align-text-center"></i>
                    {{ __('Facturas') }}
                  </span>
                  <i class="bx bx-receipt bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-history" aria-controls="navs-justified-history" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-history bx-lg me-1_5 align-text-center"></i>
                    {{ __('Historial') }}
                    <?php
                    /*
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5 pt-50">3</span>
                    */
                    ?>
                  </span>
                  <i class="bx bx-history bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-pendiente" aria-controls="navs-justified-pendiente" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-time bx-lg me-1_5 align-text-center"></i>
                    {{ __('Pendientes') }}
                  </span>
                  <i class="bx bx-time bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-defecto" aria-controls="navs-justified-defecto" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bxs-error-alt bx-lg me-1_5 align-text-center"></i>
                    {{ __('Defectos') }}
                  </span>
                  <i class="bx bxs-error-alt bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-doc-generales" aria-controls="navs-justified-doc-generales" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-file bx-lg me-1_5 align-text-center"></i>
                    {{ __('Documentos generales') }}
                  </span>
                  <i class="bx bx-file bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-doc-bancos" aria-controls="navs-justified-doc-bancos" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-file bx-lg me-1_5 align-text-center"></i>
                    {{ __('Documentos bancos') }}
                  </span>
                  <i class="bx bx-file bx-lg d-sm-none"></i>
                </button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="navs-justified-home" role="tabpanel">
                @include('livewire.casos.partials._form-caso')
              </div>
              <div class="tab-pane fade" id="navs-justified-invoice" role="tabpanel">
                @if($this->recordId)
                  @livewire('casos.caso-invoice-manager', ['caso_id' => $this->recordId],
                  key('caso-invoice-' . ($this->recordId ?? uniqid())))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the product') }}
                  </div>
                @endif
              </div>
              <div class="tab-pane fade" id="navs-justified-history" role="tabpanel">
                 @if($this->recordId)
                  @livewire('casos.caso-activity-timeline', ['caso_id' => $this->recordId], key('caso-activity-'.$this->recordId))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the case') }}
                  </div>
                @endif
              </div>
              <div class="tab-pane fade" id="navs-justified-pendiente" role="tabpanel">
                @if($this->recordId)
                  @livewire('casos.caso-situacion-manager', [
                    'caso_id' => $this->recordId,
                    'tipo' => 'PENDIENTE',
                    'onlyview' => false,
                    'canview'   => auth()->user()->can('view-casos'),
                    'cancreate' => auth()->user()->can('create-casos'),
                    'canedit'   => auth()->user()->can('edit-casos'),
                    'candelete' => auth()->user()->can('delete-casos'),
                    'canexport' => auth()->user()->can('export-casos'),

                  ], key('casos-pendientes-'.$this->recordId))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the case') }}
                  </div>
                @endif
              </div>
              <div class="tab-pane fade" id="navs-justified-defecto" role="tabpanel">
                @if($this->recordId)
                  @livewire('casos.caso-situacion-manager', [
                    'caso_id' => $this->recordId,
                    'tipo' => 'DEFECTUOSO',
                    'onlyview' => false,
                    'canview'   => auth()->user()->can('view-casos'),
                    'cancreate' => auth()->user()->can('create-casos'),
                    'canedit'   => auth()->user()->can('edit-casos'),
                    'candelete' => auth()->user()->can('delete-casos'),
                    'canexport' => auth()->user()->can('export-casos'),

                  ], key('casos-defectuosos-'.$this->recordId))
                  @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the case') }}
                  </div>
                @endif
              </div>
              <div class="tab-pane fade" id="navs-justified-doc-generales" role="tabpanel">
                @if($this->recordId)
                  @livewire('casos.caso-document-manager', [
                    'caso_id' => $this->recordId,
                    'collection' => "casos_general_documents",
                    'onlyview' => false,
                    'canview'   => auth()->user()->can('view-documento-proformas'),
                    'cancreate' => auth()->user()->can('create-documento-proformas'),
                    'canedit'   => auth()->user()->can('edit-documento-proformas'),
                    'candelete' => auth()->user()->can('delete-documento-proformas'),
                    'canexport' => auth()->user()->can('export-documento-proformas'),

                  ], key('casos-general_documents-'.$this->recordId))
                  @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-file bx-lg d-sm-none"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the case') }}
                  </div>
                @endif
              </div>
              <div class="tab-pane fade" id="navs-justified-doc-bancos" role="tabpanel">
                @if($this->recordId)
                  @livewire('casos.caso-document-manager', [
                    'caso_id' => $this->recordId,
                    'collection' => "casos_bank_documents",
                    'onlyview' => false,
                    'canview'   => auth()->user()->can('view-documento-proformas'),
                    'cancreate' => auth()->user()->can('create-documento-proformas'),
                    'canedit'   => auth()->user()->can('edit-documento-proformas'),
                    'candelete' => auth()->user()->can('delete-documento-proformas'),
                    'canexport' => auth()->user()->can('export-documento-proformas'),

                  ], key('casos-bank_documents-'.$this->recordId))
                  @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-file bx-lg d-sm-none"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the case') }}
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
