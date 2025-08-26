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
      <!-- Aditional charge -->
      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'additional_charge_type_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="additional_charge_type_id">{{ __('Charge Type') }}</label>
        <select x-ref="select" id="additional_charge_type_id"
                class="select2 form-select @error('additional_charge_type_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->chargeTypes as $chargeType)
            <option value="{{ $chargeType->id }}">{{ $chargeType->code . '-' . $chargeType->name }}</option>
          @endforeach
        </select>
        @error('additional_charge_type_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- additional_charge_other -->
      @if($additional_charge_type_id == 99)
        <div class="col-md-3 fv-plugins-icon-container">
          <label class="form-label" for="additional_charge_other">{{ __('Description Other Charge') }}</label>
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-barcode"></i></span>
            <input type="text" wire:model="additional_charge_other" id="additional_charge_other"
              class="form-control @error('additional_charge_other') is-invalid @enderror"
              placeholder="{{ __('Description Other Charge') }}">
          </div>
          @error('additional_charge_other')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
      @endif

      <!-- third_party_identification -->
      @if($additional_charge_type_id == 4)
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="third_party_name">{{ __('Third Party Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="third_party_name" id="third_party_name"
            class="form-control @error('third_party_name') is-invalid @enderror"
            placeholder="{{ __('Third Party Name') }}">
        </div>
        @error('third_party_name')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'third_party_identification_type',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="third_party_identification_type">{{ __('Identification Type') }}</label>
        <select x-ref="select" id="third_party_identification_type"
                class="select2 form-select @error('third_party_identification_type') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->identificationTypes as $identificationType)
              <option value="{{ $identificationType->code }}">{{ $identificationType->code . '-' . $identificationType->name }}</option>
          @endforeach
        </select>
        @error('third_party_identification_type')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="third_party_identification">{{ __('Third Party Identification') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="third_party_identification" id="third_party_identification"
            class="form-control @error('third_party_identification') is-invalid @enderror"
            placeholder="{{ __('Third Party Identification') }}">
        </div>
        @error('third_party_identification')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="detail">{{ __('Detail') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="detail" id="detail" class="form-control @error('detail') is-invalid @enderror"
            placeholder="{{ __('Detail') }}">
        </div>
        @error('detail')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- percent -->
      @php
      /*
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="percent">{{ __('Percent')
          }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calculator"></i></span>
          <input type="number" wire:model="percent" name="percent" id="percent"
            class="form-control @error('percent') is-invalid @enderror" placeholder=" {{ __('Percent') }}" step="0.01">
        </div>
        @error('percent')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      */
      @endphp

      <!-- quantity -->
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="quantity">{{ __('Quantity') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $quantity ?? '' }}',
            wireModelName: 'quantity',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('quantity', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-hash"></i></span>
            <input type="text" id="quantity" x-ref="cleaveInput" wire:ignore class="form-control js-input-quantity" />
          </div>
        </div>
        @error('quantity')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- amount -->
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="amount">{{ __('Amount') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $amount ?? '' }}',
            wireModelName: 'amount',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('amount', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
            <div class="input-group input-group-merge has-validation">              
              <input type="text" id="amount" x-ref="cleaveInput" wire:ignore class="form-control js-input-amount" />
            </div>
          </div>
          @error('amount')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
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
  (function () {

    // Inicializar Cleave.js
    const initializeCleave = () => {
      document.querySelectorAll('.numeral-mask').forEach((numeralMask) => {
        if (!numeralMask.dataset.cleaveInitialized) {
          console.log(`Inicializando Cleave.js para el control con id: ${numeralMask.id}`);
          new Cleave(numeralMask, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: '.',
            delimiter: ',',
            numeralDecimalScale: 2
          });
          numeralMask.dataset.cleaveInitialized = true;
        }
      });
    };


    // Función para inicializar Select2
    const initializeSelect2 = () => {
        const selects = [
            'additional_charge_type_id',
            'third_party_identification_type',
        ];

        selects.forEach((id) => {
            $(`#${id}`).select2();
            console.log("Inicializando select2 " + id);
            $(`#${id}`).on('change', function () {
                const newValue = $(this).val();
                const livewireValue = @this.get(id);

                if (newValue !== livewireValue) {
                    // Actualiza Livewire solo si es el select2 de `condition_sale`
                    // Hay que poner wire:ignore en el select2 para que todo vaya bien
                    const specificIds = ['additional_charge_type_id', 'third_party_identification_type']; // Lista de IDs específicos

                    if (specificIds.includes(id)) {
                        @this.set(id, newValue);
                    } else {
                        // Para los demás select2, actualiza localmente sin llamar al `updated`
                        @this.set(id, newValue, false);
                    }
                }
            });

            // Sincroniza el valor actual desde Livewire al Select2
            const currentValue = @this.get(id);
            $(`#${id}`).val(currentValue).trigger('change');
        });
    };

    // Re-ejecutar después de actualizaciones de Livewire
    Livewire.on('reinitControls', () => {
        console.log("Reinicializando controles después de Livewire update");
        setTimeout(() => {
            initializeCleave();
            initializeSelect2();
        }, 200); // Retraso para permitir que el DOM se estabilice
    });

  })();
</script>
@endscript
*/
@endphp
