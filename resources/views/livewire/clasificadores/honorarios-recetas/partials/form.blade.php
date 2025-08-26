<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    <div class="row g-6">
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="desde">{{ __('From') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $desde ?? '' }}',
            wireModelName: 'desde',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('desde', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="desde" x-ref="cleaveInput" wire:ignore class="form-control js-input-desde" />
          </div>
        </div>
        @error('desde')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="hasta">{{ __('Until') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $hasta ?? '' }}',
            wireModelName: 'hasta',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('hasta', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="hasta" x-ref="cleaveInput" wire:ignore class="form-control js-input-hasta" />
          </div>
        </div>
        @error('hasta')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="porcentaje">{{ __('Percent') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $porcentaje ?? '' }}',
            wireModelName: 'porcentaje',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('porcentaje', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="porcentaje" x-ref="cleaveInput" wire:ignore class="form-control js-input-porcentaje" />
          </div>
        </div>
        @error('porcentaje')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="orden">{{ __('Order') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span id="spanorder" class="input-group-text"><i class="bx bx-dollar"></i></span>
          <input type="number" wire:model="orden" id="orden" class="form-control @error('orden') is-invalid @enderror"
            placeholder="{{ __('Order') }}" step="0.01">
        </div>
        @error('orden')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>
    <br>

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

@script()
<script>
  Livewire.on('reinitControls', postId => {
      jQuery(document).ready(function () {
          $('#bank_id').select2();
          $('#bank_id').on('change', function (e) {
              var data = $('#bank_id').select2("val");
              @this.set('bank_id', data);
          });
      });
  });


</script>
@endscript
