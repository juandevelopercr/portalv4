<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    @php
    /*
    @if ($errors->any())
    <div class="alert alert-danger">
      <strong>{{ __('Please fix the following errors:') }}</strong>
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    */
    @endphp

    <div class="row g-6">
      <!-- Centro de Costo -->
      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'centro_costo_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="centro_costo_id">{{ __('Centro Costo') }}</label>
        <select x-ref="select" id="centro_costo_id"
                class="select2 form-select @error('centro_costo_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->centrosCostos as $centrosCosto)
            <option value="{{ $centrosCosto->id }}">{{ $centrosCosto->codigo . '-'. $centrosCosto->descrip }}</option>
          @endforeach
        </select>
        @error('centro_costo_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- Abogado a Cargo -->
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="abogado_encargado">{{ __('Abogado a Cargo') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="abogado_encargado" id="abogado_encargado"
            class="form-control @error('abogado_encargado') is-invalid @enderror"
            placeholder="{{ __('Description Other Charge') }}">
        </div>
        @error('abogado_encargado')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- percent -->
      <div class="col-md-3 fv-plugins-icon-container">
          <label class="form-label" for="percent">{{ __('Porciento de Participación') }}</label>
          <div
            x-data="cleaveLivewire({
              initialValue: '{{ $percent ?? '' }}',
              wireModelName: 'percent',
              postUpdate: false,
              decimalScale: 2,
              allowNegative: true,
              rawValueCallback: (val) => {
                //console.log('Callback personalizado:', val);
                // lógica extra aquí si deseas
                const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
                if (component) {
                  component.set('percent', val); // <- Esto envía el valor sin comas
                }
              }
            })"
            x-init="init($refs.cleaveInput)"
          >
            <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-dollar"></i></span>
              <input type="text" id="percent" x-ref="cleaveInput" wire:ignore class="form-control js-input-percent" />
            </div>
          </div>
          @error('percent')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>

      <!-- Comisionista -->
      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'comisionista_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="comisionista_id">{{ __('Comisionista') }}</label>
        <select x-ref="select" id="comisionista_id"
                class="select2 form-select @error('comisionista_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->comisionistas as $comisionista)
            <option value="{{ $comisionista->id }}">{{ $comisionista->name }}</option>
          @endforeach
        </select>
        @error('comisionista_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- Porciento de Comisión -->
      <div class="col-md-3 fv-plugins-icon-container">
          <label class="form-label" for="commission_percent">{{ __('Porciento de Comisión') }}</label>
          <div
            x-data="cleaveLivewire({
              initialValue: '{{ $commission_percent ?? '' }}',
              wireModelName: 'commission_percent',
              postUpdate: false,
              decimalScale: 2,
              allowNegative: true,
              rawValueCallback: (val) => {
                //console.log('Callback personalizado:', val);
                // lógica extra aquí si deseas
                const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
                if (component) {
                  component.set('commission_percent', val); // <- Esto envía el valor sin comas
                }
              }
            })"
            x-init="init($refs.cleaveInput)"
          >
            <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-dollar"></i></span>
              <input type="text" id="commission_percent" x-ref="cleaveInput" wire:ignore class="form-control js-input-commission_percent" />
            </div>
          </div>
          @error('commission_percent')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
    </div>

    <div class="row g-6">
      <div class="pt-6">
        {{-- Incluye botones de guardar y guardar y cerrar --}}
        @include('livewire.includes.button-saveAndSaveAndClose')

        <!-- Botón Cancel -->
        <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="cancel"
          wire:loading.attr="disabled" wire:target="cancel">
          <span wire:loading.remove wire:target="cancel">
            <span class="fa fa-remove bx-18px me-2"></span>{{ __('Cancel') }}
          </span>
          <span wire:loading wire:target="cancel">
            <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Cancelling...') }}
          </span>
        </button>
      </div>
    </div>
  </form>
</div>

@php
/*
@script()
<script>
  Livewire.on('reinitControls', postId => {
      jQuery(document).ready(function () {
          $('#centro_costo_id').select2();
          $('#centro_costo_id').on('change', function (e) {
              var data = $('#centro_costo_id').select2("val");
              @this.set('centro_costo_id', data, false);
          });

          $('#comisionista_id').select2();
          $('#comisionista_id').on('change', function (e) {
              var data = $('#comisionista_id').select2("val");
              @this.set('comisionista_id', data, false);
          });
      });
  });
</script>
@endscript
*/
@endphp
