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
      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="seller_id">{{ __('Vendedor') }}</label>
          <div wire:ignore>
            <select wire:model="seller_id" id="seller_id" class="select2 form-select @error('seller_id') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->sellers as $seller)
                <option value="{{ $seller->id }}">{{ $seller->name }}</option>
              @endforeach
            </select>
          </div>
        @error('seller_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fecha_venta">{{ __('Fecha de venta') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="fecha_venta"
            wire:model="fecha_venta"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('fecha_venta') is-invalid @enderror"
            placeholder="dd-mm-aaaa">
        </div>
        @error('fecha_venta')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="company_provider_id">{{ __('Compañia') }}</label>
          <div wire:ignore>
            <select wire:model="company_provider_id" id="company_provider_id" class="select2 form-select @error('company_provider_id') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->companies as $company)
                <option value="{{ $company->id }}">{{ $company->name }}</option>
              @endforeach
            </select>
          </div>
        @error('company_provider_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="service_provider_id">{{ __('Servicio') }}</label>
          <div wire:ignore>
            <select wire:model="service_provider_id" id="service_provider_id" class="select2 form-select @error('service_provider_id') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->providerServices as $service)
                <option value="{{ $service->id }}">{{ $service->name }}</option>
              @endforeach
            </select>
          </div>
        @error('service_provider_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="pick_up_place">{{ __('Lugar de recogida') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-map"></i></span>
          <input type="text" wire:model="pick_up_place" id="pick_up_place"
            class="form-control @error('pick_up_place') is-invalid @enderror" placeholder="{{ __('Lugar de recogida') }}">
        </div>
        @error('pick_up_place')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="pick_up_time">{{ __('Hora de recogida') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-time"></i></span>
          <input type="text" wire:model="pick_up_time" id="pick_up_time"
            class="form-control @error('pick_up_time') is-invalid @enderror" placeholder="{{ __('Hora de recogida') }}">
        </div>
        @error('pick_up_time')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="num_pax">{{ __('Número de pasajeros') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-group"></i></span>
          <input type="text" wire:model="num_pax" id="num_pax"
            class="form-control @error('num_pax') is-invalid @enderror" placeholder="{{ __('num_pax') }}">
        </div>
        @error('num_pax')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="cliente">{{ __('Cliente') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="cliente" id="cliente"
            class="form-control @error('cliente') is-invalid @enderror" placeholder="{{ __('Cliente') }}">
        </div>
        @error('cliente')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="num_recibo">{{ __('No. Factura') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-receipt"></i></span>
          <input type="text" wire:model="num_recibo" id="num_recibo"
            class="form-control @error('num_recibo') is-invalid @enderror" placeholder="{{ __('No. Factura') }}">
        </div>
        @error('num_recibo')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fecha_servicio">{{ __('Fecha de Servicio') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="fecha_servicio"
            wire:model="fecha_servicio"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('fecha_servicio') is-invalid @enderror"
            placeholder="dd-mm-aaaa">
        </div>
        @error('fecha_servicio')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="precio_rank">{{ __('Precio Rack') }}</label>
        <div class="input-group input-group-merge has-validation" x-data="{
                    rawValue: @js(data_get('precio_rank', '')),
                    maxLength: 15,
                    hasError: {{ json_encode($errors->has('precio_rank')) }}
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
            <input wire:model="precio_rank" id="precio_rank"
                class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                placeholder="{{ __('Precio Rack') }}" x-ref="cleaveInput" />
        </div>
        <!-- Mensaje de error -->
        @error('precio_rank')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="precio_neto">{{ __('Precio Neto') }}</label>
        <div class="input-group input-group-merge has-validation" x-data="{
                    rawValue: @js(data_get('precio_neto', '')),
                    maxLength: 15,
                    hasError: {{ json_encode($errors->has('precio_neto')) }}
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
            <input wire:model="precio_neto" id="precio_neto"
                class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                placeholder="{{ __('Precio Neto') }}" x-ref="cleaveInput" />
        </div>
        <!-- Mensaje de error -->
        @error('precio_neto')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="dop_off">{{ __('Dop Off') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
          <input type="text" wire:model="dop_off" id="dop_off"
            class="form-control @error('dop_off') is-invalid @enderror" placeholder="{{ __('Dop Off') }}">
        </div>
        @error('dop_off')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="comment">{{ __('Comentario') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-comment"></i></span>
          <input type="text" wire:model="comment" id="comment"
            class="form-control @error('comment') is-invalid @enderror" placeholder="{{ __('Comentario') }}">
        </div>
        @error('comment')
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

@if($modalCustomerOpen)
<div id="customer-modal" class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Select Customer') }}</h5>
        <button type="button" class="btn-close" wire:click="closeCustomerModal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @livewire('contacts.contact-manager', [
                    'enabledSelectedValue' => true,
                    'type' => 'customer'
                  ],
                  key('contact-manager'.$this->recordId))
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">
          {{ __('Close') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endif

@script()
<script>
    const initializeSelect2 = () => {
      const selects = [
        'seller_id',
        'company_provider_id',
        'service_provider_id'
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
              const specificIds = ['condition_sale', 'invoice_type', 'location_id', 'bank_id']; // Lista de IDs específicos

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
</script>
@endscript
