<form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
  <h6><span class="badge bg-primary">1. {{ __('General Information') }}</span></h6>
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
    <div class="col-md-3 fv-plugins-icon-container">
      <label class="form-label" for="proforma_no">{{ __('No. Cotización') }}</label>
      <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-receipt"></i></span>
        <input type="text" wire:model="proforma_no" name="proforma_no" id="proforma_no" readonly
          class="form-control @error('proforma_no') is-invalid @enderror" placeholder="{{ __('No. Cotización') }}"
          aria-label="{{ __('No. Cotización') }}" aria-describedby="spanproforma_no">
      </div>
      @error('proforma_no')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
      <div class="form-text">
        {{ __('The system generates it') }}
      </div>
    </div>
    <div class="col-md-6 fv-plugins-icon-container">
      <label class="form-label" for="customer_name">{{ __('Customer Name') }}</label>
      <div class="input-group input-group-merge has-validation">
        <span class="input-group-text">
          <i class="bx bx-user"></i>
        </span>
        <input type="text" wire:model="customer_name" id="customer_name" readonly
          class="form-control @error('customer_name') is-invalid @enderror" placeholder="{{ __('Customer Name') }}">
        <!-- Botón con icono -->
        <button type="button" class="btn btn-primary" wire:click="$dispatch('openCustomerModal')">
          <i class="bx bx-search"></i> <!-- Icono en lugar del texto -->
        </button>
      </div>
      @error('customer_name')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
      @if ($this->tipoIdentificacion)
        <blockquote class="blockquote mt-4">
          <p class="mb-0">
          <strong>{{ __('Identification Type') }}: </strong> {{ $this->tipoIdentificacion }}
          <strong>{{ __('Identification') }}: </strong> {{ $this->identificacion }}
          </p>
        </blockquote>
      @endif
    </div>

    <div class="col-md-3 fv-plugins-icon-container">
      <label class="form-label" for="show_transaction_date">{{ __('Emmision Date') }}</label>
      <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
        <input type="text" id="show_transaction_date" @if (!$recordId) readonly @endif
          wire:model="show_transaction_date"
          x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
          x-init="init($el)"
          wire:ignore
          class="form-control date-picke @error('show_transaction_date') is-invalid @enderror"
          placeholder="dd-mm-aaaa">
      </div>
      @error('show_transaction_date')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-3 select2-primary fv-plugins-icon-container">
      <label class="form-label" for="currency_id">{{ __('Currency') }}</label>
      <div wire:ignore>
        <select wire:model="currency_id" id="currency_id" class="select2 form-select @error('currency_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->currencies as $currency)
            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
          @endforeach
        </select>
      </div>
      @error('currency_id')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-3 fv-plugins-icon-container">
      <label class="form-label" for="proforma_change_type">{{ __('Change Type') }}</label>
      <div class="input-group input-group-merge has-validation" x-data="{
                  rawValue: @js(data_get('proforma_change_type', '')),
                  maxLength: 15,
                  hasError: {{ json_encode($errors->has('proforma_change_type')) }}
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
          <input wire:model="proforma_change_type" id="proforma_change_type"
              class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
              placeholder="{{ __('Change Type') }}" x-ref="cleaveInput" />
      </div>
      <!-- Mensaje de error -->
      @error('proforma_change_type')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-3 select2-primary fv-plugins-icon-container">
      <label class="form-label" for="proforma_status">{{ __('Status') }}</label>
      <div wire:ignore>
        <select wire:model="proforma_status" id="proforma_status" class="select2 form-select @error('proforma_status') is-invalid @enderror" disabled>
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->statusOptions as $statu)
            <option value="{{ $statu['id'] }}">{{ $statu['name'] }}</option>
          @endforeach
        </select>
      </div>
      @error('proforma_status')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6 fv-plugins-icon-container">
      <label class="form-label" for="email_cc">{{ __('Email CC') }}</label>
      <textarea class="form-control @if(isset($invalidEmails) && is_array($invalidEmails) && count($invalidEmails)) is-invalid @endif"
        wire:model.live.debounce.600ms="email_cc" name="email_cc" id="email_cc" rows="2"
        placeholder="{{ __('Email CC') }}">
            </textarea>
      @error('email_cc')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror

      @if ($this->clientEmail)
      <blockquote class="blockquote mt-4">
          <p class="mb-0">
          <strong>{{ __('Email del cliente') }}: </strong> {{ $this->clientEmail }}
          </p>
      </blockquote>
      @endif

      <!-- Mostrar correos inválidos -->
      @if(isset($invalidEmails) && is_array($invalidEmails) && count($invalidEmails))
      <div class="mt-1 text-danger form-text">
        <strong>{{ __('Invalid Emails') }}:</strong>
        <ul>
          @foreach ($invalidEmails as $email)
          <li>{{ $email }}</li>
          @endforeach
        </ul>
      </div>
      @endif
    </div>

    @if($this->condition_sale == '99')
    <div class="col-md-6 fv-plugins-icon-container">
      <label class="form-label" for="condition_sale_other">{{ __('Detaill Condition Sale Other') }}</label>
      <div class="input-group input-group-merge has-validation">
        <span class="input-group-text">
          <i class="bx bx-receipt"></i>
        </span>
        <input type="text" wire:model="condition_sale_other" id="condition_sale_other"
          class="form-control @error('condition_sale_other') is-invalid @enderror" placeholder="{{ __('Detaill Condition Sale Other') }}">
      </div>
      @error('condition_sale_other')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>
    @endif
  </div>

  <br>
  <h6 class="mt-4"><span class="badge bg-primary">2. {{ __('Additional Information') }}</span></h6>
  <div class="row g-6">
    <div class="col-md-6 fv-plugins-icon-container">
      <label class="form-label" for="message">{{ __('Message') }}</label>
      <textarea class="form-control" wire:model="message" name="message" id="message" rows="5"
        placeholder="{{ __('Message') }}"></textarea>
      @error('message')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6 fv-plugins-icon-container">
      <label class="form-label" for="notes">{{ __('Notes') }}</label>
      <textarea class="form-control" wire:model="notes" name="notes" id="notes" rows="5"
        placeholder="{{ __('Notes') }}"></textarea>
      @error('notes')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <br>
  <h6 class="mt-4"><span class="badge bg-primary">3. {{ __('Payment Information') }}</span></h6>
  @include('livewire.transactions.partials._form-payment')

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

@if($action == 'create' || $action == 'edit')
@script()
<script>
  $(document).ready(function() {
    // Para la busqueda del caso
    // Configuración AJAX para caso_id
    window.select2Config = {
      currency_id: {fireEvent: false},
      contact_economic_activity_id: {fireEvent: false},
      location_economic_activity_id: {fireEvent: false},
      location_id: {fireEvent: true},
      codigo_contable_id: {fireEvent: false},
      proforma_status: {fireEvent: false}
    };

    $('#caso_id').select2({
      placeholder: $('#caso_id').data('placeholder'),
      minimumInputLength: 2,
      ajax: {
        url: '/api/casos/search',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term
          };
        },
        processResults: function (data) {
          return {
            results: data.map(item => ({
              id: item.id,
              text: item.text
            }))
          };
        },
        cache: true
      }
    });

    // Manejar selección y enviar a Livewire
    $('#caso_id').on('change', function () {
      const val = $(this).val();
      if (typeof $wire !== 'undefined') {
        $wire.set('caso_id', val);
      }
    });

    //**************************************************************
    //*****Para todos los demás select2****************
    //**************************************************************
    Object.entries(select2Config).forEach(([id, config]) => {
      const $select = $('#' + id);
      if (!$select.length) return;

      $select.select2();

      // Default values
      const fireEvent = config.fireEvent ?? false;
      //const allowClear = config.allowClear ?? false;
      //const placeholder = config.placeholder ?? 'Seleccione una opción';

      $select.on('change', function() {
        let data = $(this).val();
        $wire.set(id, data, fireEvent);
        $wire.id = data;
        //@this.department_id = data;
        console.log(data);
      });
    });
  })

  Livewire.on('setSelect2Value', ({ id, value, text }) => {
    const option = new Option(text, value, true, true);
    console.log("Entró al setSelect2Value con option: " + option);
    $('#' + id).append(option).trigger('change');
  });

  Livewire.on('updateSelect2Options', ({ id, options }) => {
    const $select = $('#' + id);
    $select.empty(); // Limpiar opciones

    console.log("Se limpia el select2 " + id);

    options.forEach(opt => {
        const option = new Option(opt.text, opt.id, false, false);
        $select.append(option);
        console.log("Se adiciona el valor " + option);
    });

    $select.trigger('change');
    console.log("Se dispara el change");
  });

  const initializeSelect2 = () => {
      const selects = [
        'condition_sale',
        'invoice_type',
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
              const specificIds = ['condition_sale', 'invoice_type']; // Lista de IDs específicos

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
        initSelect2Other();
      }, 300); // Retraso para permitir que el DOM se estabilice
    });

    function initSelect2Other(){
        Object.entries(select2Config).forEach(([id, config]) => {
        const $select = $('#' + id);
        if (!$select.length) return;

        $select.select2();

        // Default values
        const fireEvent = config.fireEvent ?? false;
        //const allowClear = config.allowClear ?? false;
        //const placeholder = config.placeholder ?? 'Seleccione una opción';

        $select.on('change', function() {
          let data = $(this).val();
          $wire.set(id, data, fireEvent);
          $wire.id = data;
          //@this.department_id = data;
          console.log(data);
        });
      });
    }

</script>
@endscript
@endif
