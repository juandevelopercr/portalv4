<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->

<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Honorario Information') }}</h5>
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
                  data-bs-target="#navs-justified-profile" aria-controls="navs-justified-profile" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-money bx-lg me-1_5 align-text-center"></i>
                    {{ __('Recetas') }}
                    <?php
                    /*
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5 pt-50">3</span>
                    */
                    ?>
                  </span>
                  <i class="bx bx-money bx-lg d-sm-none"></i>
                </button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="navs-justified-home" role="tabpanel">
                @include('livewire.clasificadores.honorarios.partials._form-honorario', [
                'name' =>$name,
                'active'=>$active
                ])
              </div>
              <div class="tab-pane fade" id="navs-justified-profile" role="tabpanel">
                @if($this->recordId)
                @livewire('clasificadores.honorarios-recetas.honorario-receta-manager', ['honorario_id' => $this->recordId],
                key('honorario-receta-'.$this->recordId))
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
