<!-- Formulario para productos -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    <div class="row g-6">
      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="name" id="name" class="form-control @error('name') is-invalid @enderror"
            placeholder="{{ __('Name') }}">
        </div>
        @error('name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-2 fv-plugins-icon-container">
        <label class="form-label" for="code">{{ __('Code') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="code" id="code" class="form-control @error('code') is-invalid @enderror"
            placeholder="{{ __('Code') }}">
        </div>
        @error('code')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="caby_code">{{ __('Caby Code') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="caby_code" id="caby_code"
            class="form-control @error('caby_code') is-invalid @enderror" placeholder="{{ __('Caby Code') }}" readonly>
          <button type="button" class="btn btn-primary" wire:click="$dispatch('openCabysModal')">
            {{ __('Select') }}
          </button>
        </div>
        @error('caby_code')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="price">{{ __('Price') }}</label>
        <div class="input-group input-group-merge has-validation" x-data="{
                    rawValue: @js(data_get('price', '')),
                    maxLength: 15,
                    hasError: {{ json_encode($errors->has('price')) }}
                }" x-init="
                    let cleaveInstance = new Cleave($refs.cleaveInput, {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        numeralDecimalMark: '.',
                        delimiter: ',',
                        numeralDecimalScale: 2,
                    });

                    // Inicializa el valor formateado
                    if (rawValue) {
                        cleaveInstance.setRawValue(rawValue);
                    }

                    // Observa cambios en rawValue desde Livewire
                    $watch('rawValue', (newValue) => {
                        if (newValue !== undefined) {
                            cleaveInstance.setRawValue(newValue);
                        }
                    });

                    // Sincroniza cambios del input con Livewire
                    $refs.cleaveInput.addEventListener('input', () => {
                        let cleanValue = cleaveInstance.getRawValue();
                        if (cleanValue.length <= maxLength) {
                            rawValue = cleanValue;
                        } else {
                            // Limita al máximo de caracteres
                            rawValue = cleanValue.slice(0, maxLength);
                            cleaveInstance.setRawValue(rawValue);
                        }
                    });
                ">
            <!-- Ícono alineado con el input -->
            <span class="input-group-text">
                <i class="bx bx-calculator"></i>
            </span>
            <!-- Input con máscara -->
            <input wire:model="price" id="price"
                class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                placeholder="{{ __('Price') }}" x-ref="cleaveInput" />
        </div>
        <!-- Mensaje de error -->
        @error('price')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'unit_type_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="unit_type_id">{{ __('Unit Type') }}</label>
        <select x-ref="select" id="unit_type_id"
                class="select2 form-select @error('unit_type_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->unitTypes as $unit)
            <option value="{{ $unit->id }}">{{ $unit->code. '-'. $unit->name }}</option>
          @endforeach
        </select>
        @error('unit_type_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="col-md-3 fv-plugins-icon-container">
      <div class="form-check form-switch" style="margin-top: 30px;">
        <input type="checkbox" class="form-check-input" id="active" wire:model.defer="active" {{ $active==1
          ? 'checked' : '' }} />

        <label for="active" class="switch-label">{{ __('Active') }}</label>
      </div>
    </div>

    <br>
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
  </form>
</div>

@script()
<script>
  (function() {
    // Función para inicializar Select2
    const initializeSelect2 = () => {
      const selects = [
        'unit_type_id',
        'exoneration_tarifa_iva'
      ];

      selects.forEach((id) => {
        const element = document.getElementById(id);
        if (element) {
          //console.log(`Inicializando Select2 para: ${id}`);

          $(`#${id}`).select2();

          $(`#${id}`).on('change', function() {
            const newValue = $(this).val();
            const livewireValue = @this.get(id);

            if (newValue !== livewireValue) {
              // Actualiza Livewire solo si es el select2 de `condition_sale`
              // Hay que poner wire:ignore en el select2 para que todo vaya bien
              const specificIds = ['condition_sale_id', 'province_id', 'canton_id', 'district_id', 'pay_term_number']; // Lista de IDs específicos

              if (specificIds.includes(id)) {
                @this.set(id, newValue);
              } else {
                // Para los demás select2, actualiza localmente sin llamar al `updated`
                @this.set(id, newValue, false);
              }
            }
          });
        }

        // Sincroniza el valor actual desde Livewire al Select2
        const currentValue = @this.get(id);
        $(`#${id}`).val(currentValue).trigger('change');
      });

    };

    // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
    Livewire.on('reinitFormControls', () => {
      console.log('Reinicializando controles después de Livewire update reinitFormControls');
      setTimeout(() => {
        initializeSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });

  })();
</script>
@endscript
