<div class="pt-6 pb-0">
    <h6>2. {{ __('Taxes') }}</h6>
</div>
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

<div class="row g-3">
    @forelse ($taxes as $index => $tax)
    <div class="row g-3">
        <!-- Tax Type ID -->
        <div class="col-md-3 select2-primary fv-plugins-icon-container"
            x-data
            x-init="
                setTimeout(() => {
                    let select = $('#tax_type_id_{{ $index }}');
                    
                    // Inicializa select2
                    select.select2();

                    // Setea el valor desde Livewire (con JS puro)
                    select.val('{{ $taxes[$index]['tax_type_id'] ?? '' }}').trigger('change');

                    // Cuando cambia, envía el valor a Livewire
                    select.on('change', function () {
                        let value = $(this).val();
                        @this.set('taxes.{{ $index }}.tax_type_id', value);
                        $dispatch('updated', { index: {{ $index }}, value });
                    });
                }, 100);
            ">
            <label class="form-label" for="tax_type_id_{{ $index }}">{{ __('Tax Type') }}</label>

            <div wire:ignore>
                <select id="tax_type_id_{{ $index }}"
                        class="select2 form-select @error('taxes.'.$index.'.tax_type_id') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->taxTypes as $taxType)
                        <option value="{{ $taxType->id }}">{{ $taxType->code . '-' . $taxType->name }}</option>
                    @endforeach
                </select>
            </div>

            @error('taxes.'.$index.'.tax_type_id')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Tax Rate ID -->
        <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data
          x-init="
              setTimeout(() => {
                  let select = $('#tax_rate_id_{{ $index }}');

                  // Inicializa select2
                  select.select2();

                  // Setea el valor inicial desde Livewire
                  select.val('{{ $taxes[$index]['tax_rate_id'] ?? '' }}').trigger('change');

                  // Cuando el usuario cambia, actualiza Livewire y despacha evento
                  select.on('change', function () {
                      let value = $(this).val();
                      @this.set('taxes.{{ $index }}.tax_rate_id', value);
                      $dispatch('tax-rate-changed', { index: {{ $index }}, value });
                  });
              }, 100);
          ">
          <label class="form-label" for="tax_rate_id_{{ $index }}">{{ __('Tax Rate') }}</label>

          <div wire:ignore>
              <select id="tax_rate_id_{{ $index }}"
                      class="select2 form-select @error('taxes.'.$index.'.tax_rate_id') is-invalid @enderror">
                  <option value="">{{ __('Seleccione...') }}</option>
                  @foreach ($this->taxRates as $taxRate)
                      <option value="{{ $taxRate->id }}">{{ $taxRate->code . '-' . $taxRate->name }}</option>
                  @endforeach
              </select>
          </div>

          @error('taxes.'.$index.'.tax_rate_id')
              <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <!-- Tax -->
        @php
        /*
        <div class="col-md-3 fv-plugins-icon-container">
          <label class="form-label" for="tax_{{ $index }}">{{ __('Tax') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-dollar"></i></span>
              <input type="text"
                class="form-control cleave-init"
                data-model="taxes.{{ $index }}.tax"
                data-initial="{{ $taxes[$index]['tax'] ?? '' }}"
                data-decimals="2"
                data-allow-negative="false"
                id="tax" />
          </div>
          @error('tax')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>
        */
        @endphp
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="tax_{{ $index }}">{{ __('Tax') }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.tax', '')),
                        maxLength: 15,
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.tax')) }}
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
                <input wire:model="taxes.{{ $index }}.tax" id="tax_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Tax') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.tax')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Tax Amount -->
        @php
        /*
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="tax_amount_{{ $index }}">{{ __('Tax Amount') }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.tax_amount', '')),
                        maxLength: 15,
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.tax_amount')) }}
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
                <input wire:model="taxes.{{ $index }}.tax_amount" id="tax_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Tax Amount') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.tax_amount')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        */
        @endphp

        <!-- tax_type_other -->
        @if($tax['tax_type_id'] == '99')
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="tax_type_other_{{ $index }}">{{ __('Detalle de impuesto OTRO') }}</label>
            <div class="input-group input-group-merge has-validation">
                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                <input type="text" wire:model="taxes.{{ $index }}.tax_type_other" id="tax_type_other_{{ $index }}"
                    class="form-control @error('taxes.'.$index.'.tax_type_other') is-invalid @enderror"
                    placeholder="{{ __('Código de impuesto OTRO') }}">
            </div>
            @error('taxes.'.$index.'.tax_type_other')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- factor_calculo_tax -->
        @if($tax['tax_type_id'] == '8')
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="factor_calculo_tax_{{ $index }}">{{ __('Factor para Calculo IVA') }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.factor_calculo_tax', '')), 
                        maxLength: 15, 
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.factor_calculo_tax')) }}
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
                <input wire:model="taxes.{{ $index }}.factor_calculo_tax" id="factor_calculo_tax_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Factor para Calculo IVA') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.factor_calculo_tax')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- count_unit_type -->
        @if(in_array($tax['tax_type_id'], ['3', '4', '5', '6']))
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="count_unit_type_{{ $index }}">{{ __('Cantidad de la unidad de medida a
                utilizar') }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.count_unit_type', '')), 
                        maxLength: 15, 
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.count_unit_type')) }}
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
                    <i class="bx bx-ruler"></i>
                </span>

                <!-- Input con máscara -->
                <input wire:model="taxes.{{ $index }}.count_unit_type" id="count_unit_type_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Cantidad de la unidad de medida a utilizar') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.count_unit_type')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- percent -->
        @if($tax['tax_type_id'] == '4')
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="percent_{{ $index }}">{{ __('Percent') }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.percent', '')), 
                        maxLength: 15, 
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.percent')) }}
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
                    <i class="bx bx-percent">%</i>
                </span>

                <!-- Input con máscara -->
                <input wire:model="taxes.{{ $index }}.percent" id="percent_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Percent') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.percent')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- proporcion -->
        @if($tax['tax_type_id'] == '4')
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="proporcion_{{ $index }}">{{ __('Proporción') }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.proporcion', '')), 
                        maxLength: 15, 
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.proporcion')) }}
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
                    <i class="bx bx-pie-chart"></i>
                </span>

                <!-- Input con máscara -->
                <input wire:model="taxes.{{ $index }}.proporcion" id="proporcion_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Proporción') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.proporcion')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- volumen -->
        @if($tax['tax_type_id'] == '5')
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="volumen_unidad_consumo_{{ $index }}">{{ __('Volumen por unidad de consumo')
                }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.volumen_unidad_consumo', '')), 
                        maxLength: 15, 
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.volumen_unidad_consumo')) }}
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
                    <i class="bx bx-package"></i>
                </span>

                <!-- Input con máscara -->
                <input wire:model="taxes.{{ $index }}.volumen_unidad_consumo" id="volumen_unidad_consumo_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Volumen por unidad de consumo') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.volumen_unidad_consumo')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- impuesto_unidad -->
        @if(in_array($tax['tax_type_id'], ['3', '4', '5', '6']))
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="impuesto_unidad_{{ $index }}">{{ __('Impuesto por Unidad')
                }}</label>
            <div class="input-group input-group-merge has-validation" x-data="{
                        rawValue: @js(data_get($this->taxes, $index . '.impuesto_unidad', '')), 
                        maxLength: 15, 
                        hasError: {{ json_encode($errors->has('taxes.' . $index . '.impuesto_unidad')) }}
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
                    <i class="bx bx-package"></i>
                </span>

                <!-- Input con máscara -->
                <input wire:model="taxes.{{ $index }}.impuesto_unidad" id="impuesto_unidad_{{ $index }}"
                    class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                    placeholder="{{ __('Impuesto por Unidad') }}" x-ref="cleaveInput" />
            </div>

            <!-- Mensaje de error -->
            @error('taxes.'.$index.'.impuesto_unidad')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        @include('livewire.transactions-lines.partials._form-exhoneration', ['index'=> $index])

        @if ($index > 0)
        <div class="col-md-1 pt-6">
            <button type="button" class="btn btn-label-danger" wire:click="removeTax({{ $index }})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        @endif

        <hr>
    </div>
    @empty
    <div class="col-md-12 text-left">
        <p>{{ __('No taxes added yet.') }}</p>
    </div>
    @endforelse
</div>

<!-- Botón para agregar impuestos (siempre visible) -->
<div class="row g-6">
    <div class="col-md-2">
        <button type="button" class="btn btn-secondary mt-2" wire:click="addTax">+ {{ __('Tax') }}</button>
    </div>
</div>
