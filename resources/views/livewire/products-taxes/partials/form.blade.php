<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>
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

    <div class="row g-6">
      <!-- Tax Type ID -->
      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'tax_type_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="tax_type_id">{{ __('Tax Type') }}</label>
        <select x-ref="select" id="tax_type_id"
                class="select2 form-select @error('tax_type_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->taxTypes as $taxType)
            <option value="{{ $taxType->id }}">{{ $taxType->code.'-'.$taxType->name }}</option>
          @endforeach
        </select>
        @error('tax_type_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- Tax Rate ID -->
      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'tax_rate_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="tax_rate_id">{{ __('Tax Rate') }}</label>
        <select x-ref="select" id="tax_rate_id"
                class="select2 form-select @error('tax_rate_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->taxRates as $taxRate)
            <option value="{{ $taxRate->id }}">{{ $taxRate->code.'-'.$taxRate->name }}</option>
          @endforeach
        </select>
        @error('tax_rate_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- Tax -->
      <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="tax">{{ __('Tax') }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($tax, '')),
                        maxLength: 15,
                        hasError: {{ json_encode($errors->has('tax')) }}
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
                <input wire:model="tax" id="tax"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Tax') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('tax')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
      @php
      /*
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="tax">{{ __('Tax') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $tax ?? '' }}',
            wireModelName: 'tax',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('tax', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="tax" x-ref="cleaveInput" wire:ignore class="form-control js-input-tax" />
          </div>
        </div>
        @error('tax')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      */
      @endphp

      <!-- tax_type_other -->
      @if($tax_type_id == '99')
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="tax_type_other">{{ __('Código de impuesto OTRO') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="tax_type_other" id="tax_type_other"
            class="form-control @error('tax_type_other') is-invalid @enderror"
            placeholder="{{ __('Código de impuesto OTRO') }}">
        </div>

        @error('tax_type_other')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <!-- factor_calculo_tax -->
      @if($tax_type_id == '8')
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="factor_calculo_tax">{{ __('Factor para Calculo IVA') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $factor_calculo_tax ?? '' }}',
            wireModelName: 'factor_calculo_tax',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('factor_calculo_tax', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="factor_calculo_tax" x-ref="cleaveInput" wire:ignore class="form-control js-input-factor_calculo_tax" />
          </div>
        </div>
        @error('factor_calculo_tax')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <!-- count_unit_type -->
      @if(in_array($tax_type_id, ['3', '4', '5', '6']))
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="count_unit_type">{{ __('Cantidad de la unidad de medida a utilizar') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $count_unit_type ?? '' }}',
            wireModelName: 'count_unit_type',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('count_unit_type', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="count_unit_type" x-ref="cleaveInput" wire:ignore class="form-control js-input-count_unit_type" />
          </div>
        </div>
        @error('count_unit_type')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <!-- percent -->
      @if($tax_type_id == '4')
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="percent">{{ __('Percent') }}</label>
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
      @endif

      <!-- proporcion -->
      @if($tax_type_id == '4')
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="proporcion">{{ __('Proporción') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $proporcion ?? '' }}',
            wireModelName: 'proporcion',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('proporcion', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="proporcion" x-ref="cleaveInput" wire:ignore class="form-control js-input-proporcion" />
          </div>
        </div>
        @error('proporcion')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <!-- proporcion -->
      @if($tax_type_id == '5')
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="volumen_unidad_consumo">{{ __('Volumen por Unidad de Consumo') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $volumen_unidad_consumo ?? '' }}',
            wireModelName: 'volumen_unidad_consumo',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('volumen_unidad_consumo', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="volumen_unidad_consumo" x-ref="cleaveInput" wire:ignore class="form-control js-input-volumen_unidad_consumo" />
          </div>
        </div>
        @error('volumen_unidad_consumo')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <!-- impuesto_unidad -->
      @if(in_array($tax_type_id, ['3', '4', '5', '6']))
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="impuesto_unidad">{{ __('Impuesto por Unidad') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $impuesto_unidad ?? '' }}',
            wireModelName: 'impuesto_unidad',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('impuesto_unidad', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="impuesto_unidad" x-ref="cleaveInput" wire:ignore class="form-control js-input-impuesto_unidad" />
          </div>
        </div>
        @error('impuesto_unidad')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif
    </div>

    <br>
    <h6 class="mt-4">2. {{ __('Exhoneration') }}</h6>

    <div class="row g-6">
      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'exoneration_type_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="exoneration_type_id">{{ __('Exhoneration') }}</label>
        <select x-ref="select" id="exoneration_type_id"
                class="select2 form-select @error('exoneration_type_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->exhonerations as $exhoneration)
            <option value="{{ $exhoneration->id }}">{{ $exhoneration->code. '-'. $exhoneration->name }}</option>
          @endforeach
        </select>
        @error('exoneration_type_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- exoneration_doc_other -->
      @if($exoneration_type_id == '99')
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="exoneration_doc_other">{{ __('Document No.') }} {{ __('Other') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-detail"></i></span>
          <input type="text" wire:model="exoneration_doc_other" id="exoneration_doc_other"
            class="form-control @error('exoneration_doc_other') is-invalid @enderror"
            placeholder="{{ __('Document No.') }} {{ __('Other') }}">
        </div>

        @error('exoneration_doc_other')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="exoneration_doc">{{ __('Document No.') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-file"></i></span>
          <input type="text" wire:model="exoneration_doc" id="exoneration_doc"
            class="form-control @error('exoneration_doc') is-invalid @enderror" placeholder="{{ __('Document No.') }}">
        </div>

        @error('exoneration_doc')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- exoneration_article -->
      @if(in_array($exoneration_type_id, ['2', '3', '6', '7', '8']))
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="exoneration_article">{{ __('Article') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-file"></i></span>
          <input type="text" wire:model="exoneration_article" id="exoneration_article"
            class="form-control @error('exoneration_article') is-invalid @enderror" placeholder="{{ __('Article') }}">
        </div>

        @error('exoneration_article')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <!-- exoneration_inciso -->
      @if(in_array($exoneration_type_id, ['2', '3', '6', '7', '8']))
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="exoneration_inciso">{{ __('Inciso') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-file"></i></span>
          <input type="text" wire:model="exoneration_inciso" id="exoneration_inciso"
            class="form-control @error('exoneration_inciso') is-invalid @enderror" placeholder="{{ __('Inciso') }}">
        </div>

        @error('exoneration_inciso')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="exoneration_institution_id">{{ __('Institute Name') }}</label>
          <select wire:model="exoneration_institution_id" id="exoneration_institution_id" class="select2 form-select @error('exoneration_institution_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->institutes as $institute)
              <option value="{{ $institute->id }}">{{ $institute->code.'-'.$institute->name }}</option>
            @endforeach
          </select>
        @error('exoneration_institution_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- exoneration_institute_other -->
      @if($exoneration_institution_id == '99')
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="exoneration_institute_other">{{ __('Institute Name') }} {{ __('Other')
          }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-detail"></i></span>
          <input type="text" wire:model="exoneration_institute_other" id="exoneration_institute_other"
            class="form-control @error('exoneration_institute_other') is-invalid @enderror"
            placeholder="{{ __('Institute Name') }} {{ __('Other') }}">
        </div>

        @error('exoneration_institute_other')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="exoneration_date">{{ __('Date') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="exoneration_date" @if (!$recordId) readonly @endif
            wire:model="exoneration_date"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('exoneration_date') is-invalid @enderror"
            placeholder="dd-mm-aaaa">
        </div>
        @error('exoneration_date')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="exoneration_percent">{{ __('Percent') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $exoneration_percent ?? '' }}',
            wireModelName: 'exoneration_percent',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('exoneration_percent', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="exoneration_percent" x-ref="cleaveInput" wire:ignore class="form-control js-input-exoneration_percent" />
          </div>
        </div>
        @error('exoneration_percent')
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
$(document).ready(function() {

  const initializeSelect2 = () => {
      const selects = [
        'exoneration_institution_id',
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
              const specificIds = ['exoneration_institution_id']; // Lista de IDs específicos

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
    Livewire.on('reinitSelect2Controls', () => {
      console.log('Reinicializando controles después de Livewire update reinitFormControls');
      setTimeout(() => {
        initializeSelect2();
      }, 300); // Retraso para permitir que el DOM se estabilice
    });
});

</script>
@endscript
