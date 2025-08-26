<div>
  <div class="card mb-6">
    <form wire:submit.prevent="desglosarServicio" class="card-body">
      <h6>1. {{ __('Desglose del servicio') }}</h6>

      <div class="row g-6">
        <div class="col-md-3 fv-plugins-icon-container">
          <label class="form-label" for="desgloseMonto">{{ __('Monto') }}</label>
          <div
            x-data="cleaveLivewire({
              initialValue: '{{ $desgloseMonto ?? '' }}',
              wireModelName: 'desgloseMonto',
              postUpdate: false,
              decimalScale: 3,
              allowNegative: true,
              rawValueCallback: (val) => {
                //console.log('Callback personalizado:', val);
                // lógica extra aquí si deseas
                const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
                if (component) {
                  component.set('desgloseMonto', val); // <- Esto envía el valor sin comas
                  component.set('activeTab', 3); // <-- AQUÍ LA SOLUCIÓN
                }
              }
            })"
            x-init="init($refs.cleaveInput)"
          >
            <div class="input-group input-group-merge has-validation">            
              <input type="text" id="desgloseMonto" x-ref="cleaveInput" wire:ignore class="form-control js-input-desgloseMonto" 
              x-on:keydown.stop />
            </div>
          </div>
          @error('desgloseMonto')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>


        <div class="col-md-3 select2-primary fv-plugins-icon-container">
          <label class="form-label" for="desgloseMoneda">{{ __('Currency') }}</label>
          <div wire:ignore>
            <select wire:model="desgloseMoneda" id="desgloseMoneda" class="select2 form-select @error('desgloseMoneda') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->code }}</option>
              @endforeach
            </select>
          </div>
          @error('desgloseMoneda')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3 select2-primary fv-plugins-icon-container">
          <label class="form-label" for="desgloseBanco">{{ __('Bank') }}</label>
          <div wire:ignore>
            <select wire:model="desgloseBanco" id="desgloseBanco" class="select2 form-select @error('desgloseBanco') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->listbanks as $bank)
                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
              @endforeach
            </select>
          </div>
          @error('desgloseBanco')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3 select2-primary fv-plugins-icon-container">
          <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="calcularDesglose"
            wire:loading.attr="disabled" wire:target="calcularDesglose">
            <span wire:loading.remove wire:target="calcularDesglose">
              <span class="fa fa-remove bx-18px me-2"></span>{{ __('calcularDesglose') }}
            </span>
            <span wire:loading wire:target="calcularDesglose">
              <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('calculando...') }}
            </span>
          </button>
        <div>
      </div>  
    </form>
  </div>

  <div class="card mb-6">
    <div class="card-datatable table-responsive">
      <div class="table-responsive" style="max-width: 1200px"> <!-- Ancho máximo personalizable -->
          {!! $this->degloseHtml !!}
      </div>
    </div>
  </div>
</div>
