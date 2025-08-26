<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    <div class="row g-6">
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="base">{{ __('Base') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $base ?? '' }}',
            wireModelName: 'base',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('base', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="base" x-ref="cleaveInput" wire:ignore class="form-control js-input-base" />
          </div>
        </div>
        @error('base')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="porcada">{{ __('Por Cada') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $porcada ?? '' }}',
            wireModelName: 'porcada',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('porcada', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="porcada" x-ref="cleaveInput" wire:ignore class="form-control js-input-porcada" />
          </div>
        </div>
        @error('porcada')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'tipo',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="tipo">{{ __('Type') }}</label>
        <select x-ref="select" id="tipo"
                class="select2 form-select @error('tipo') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->types as $type)
            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
          @endforeach
        </select>
        @error('tipo')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="orden">{{ __('Order') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span id="spanorder" class="input-group-text"><i class="bx bx-sort"></i></span>
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
