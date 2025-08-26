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
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="contact_name">{{ __('Compañía') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text">
            <i class="bx bx-user"></i>
          </span>
          <input type="text" wire:model="contact_name" id="contact_name" readonly
            class="form-control @error('contact_name') is-invalid @enderror" placeholder="{{ __('Compañia') }}">
          <!-- Botón con icono -->
          <button type="button" class="btn btn-primary" wire:click="$dispatch('openCustomerModal')">
            <i class="bx bx-search"></i> <!-- Icono en lugar del texto -->
          </button>
        </div>
        @error('contact_name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="customer_name">{{ __('Nombre del cliente') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="customer_name" id="customer_name"
            class="form-control @error('customer_name') is-invalid @enderror" placeholder="{{ __('Nombre del cliente') }}">
        </div>
        @error('customer_name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="town_id">{{ __('Ciudad') }}</label>
          <div wire:ignore>
            <select wire:model="town_id" id="town_id" class="select2 form-select @error('town_id') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->towns as $town)
                <option value="{{ $town->id }}">{{ $town->name }}</option>
              @endforeach
            </select>
          </div>
        @error('town_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="consecutive">{{ __('Consecutivo') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-receipt"></i></span>
          <input type="text" wire:model="consecutive" name="consecutive" id="consecutive" disabled
            class="form-control @error('consecutive') is-invalid @enderror" placeholder="{{ __('Consecutivo') }}">
        </div>
        @error('proforma_no')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">
        {{ __('The system generates it') }}
      </div>
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="type">{{ __('Tipo') }}</label>
          <div wire:ignore>
            <select wire:model="type" id="type" class="select2 form-select @error('type') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->types as $t)
                <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
              @endforeach
            </select>
          </div>
        @error('type')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="pick_up">{{ __('Lugar de recogida') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="pick_up" id="pick_up"
            class="form-control @error('pick_up') is-invalid @enderror" placeholder="{{ __('Lugar de recogida') }}">
        </div>
        @error('pick_up')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="destination">{{ __('Lugar de entrega') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="destination" id="destination"
            class="form-control @error('destination') is-invalid @enderror" placeholder="{{ __('Lugar de entrega') }}">
        </div>
        @error('destination')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="bill_number">{{ __('Número de factura') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="bill_number" id="bill_number"
            class="form-control @error('bill_number') is-invalid @enderror" placeholder="{{ __('Número de factura') }}">
        </div>
        @error('bill_number')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="pax">{{ __('Número de pasajeros') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="pax" id="pax"
            class="form-control @error('pax') is-invalid @enderror" placeholder="{{ __('Número de pasajeros') }}">
        </div>
        @error('pax')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="rack_price">{{ __('Precio Rack') }}</label>
        <div class="input-group input-group-merge has-validation" x-data="{
                    rawValue: @js(data_get('rack_price', '')),
                    maxLength: 15,
                    hasError: {{ json_encode($errors->has('rack_price')) }}
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
            <input wire:model="rack_price" id="rack_price"
                class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                placeholder="{{ __('Change Type') }}" x-ref="cleaveInput" />
        </div>
        <!-- Mensaje de error -->
        @error('rack_price')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="net_cost">{{ __('Costo Neto') }}</label>
        <div class="input-group input-group-merge has-validation" x-data="{
                    rawValue: @js(data_get('net_cost', '')),
                    maxLength: 15,
                    hasError: {{ json_encode($errors->has('net_cost')) }}
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
            <input wire:model="net_cost" id="net_cost"
                class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                placeholder="{{ __('Change Type') }}" x-ref="cleaveInput" />
        </div>
        <!-- Mensaje de error -->
        @error('net_cost')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="date_service">{{ __('Fecha del servicio') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="date_service"
            wire:model="date_service"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('date_service') is-invalid @enderror"
            placeholder="dd-mm-aaaa">
        </div>
        @error('date_service')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="others">{{ __('Comentarios') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="others" id="others"
            class="form-control @error('others') is-invalid @enderror" placeholder="{{ __('Número de factura') }}">
        </div>
        @error('others')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="status">{{ __('Estado') }}</label>
          <div wire:ignore>
            <select wire:model="status" id="status" class="select2 form-select @error('status') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->liststatus as $s)
                <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
              @endforeach
            </select>
          </div>
        @error('status')
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
        'town_id',
        'type',
        'status'
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
