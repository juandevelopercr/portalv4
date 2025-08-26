@php
  use App\Models\Movimiento;
@endphp
<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Movement Information') }}</h5>
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
              <!-- FACTURAS -->
              @if($this->tipo_movimiento == Movimiento::TYPE_DEPOSITO)
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-tax" aria-controls="navs-justified-tax" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-coin bx-lg me-1_5 align-text-center"></i>
                    {{ __('Facturas') }}
                  </span>
                  <i class="bx bx-coin bx-lg d-sm-none"></i>
                </button>
              </li>
              @endif
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-ht" aria-controls="navs-justified-ht" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-file bx-lg me-1_5 align-text-center"></i>
                    {{ __('Documents') }}
                    <?php
                    /*
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5 pt-50">3</span>
                    */
                    ?>
                  </span>
                  <i class="bx bx-file bx-lg d-sm-none"></i>
                </button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="navs-justified-home" role="tabpanel">
                @include('livewire.movimientos.partials._form-movimiento')
              </div>

              @if($this->tipo_movimiento == Movimiento::TYPE_DEPOSITO)
              <div class="tab-pane fade" id="navs-justified-tax" role="tabpanel">
                @if($this->recordId)
                    @livewire('movimientos.movimientos-facturas-no-pagadas', [
                            'movimientoId' => $this->recordId,
                    ])
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('La información será mostrada después que haya creado el movimiento') }}
                  </div>
                @endif
              </div>
              @endif
              <div class="tab-pane fade" id="navs-justified-ht" role="tabpanel">
                @if($this->recordId)
                  @livewire('movimientos.movimiento-document-manager', [
                    'movimiento_id' => $this->recordId,
                    false,
                    'canview'   => auth()->user()->can('view-movimiento'),
                    'cancreate' => auth()->user()->can('create-movimiento'),
                    'canedit'   => auth()->user()->can('edit-movimiento'),
                    'candelete' => auth()->user()->can('delete-movimiento'),
                    'canexport' => auth()->user()->can('export-movimiento'),

                  ], key('movimiento-documents-'.$this->recordId))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-file bx-lg d-sm-none"></i>
                    </span>
                    {{ __('La información será mostrada después que haya creado el movimiento') }}
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

<livewire:modals.caby-modal />
