<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="numero_cuenta">{{ __('No. Cuenta') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="numero_cuenta" name="numero_cuenta"
            class="form-control @error('numero_cuenta') is-invalid @enderror" placeholder="{{ __('No. Cuenta') }}">
        </div>
        @error('numero_cuenta')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="nombre_cuenta">{{ __('Nombre de Cuenta') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-font"></i></span>
          <input type="text" wire:model="nombre_cuenta" name="iniciales"
            class="form-control @error('nombre_cuenta') is-invalid @enderror" placeholder="{{ __('Nombre de Cuenta') }}">
        </div>
        @error('nombre_cuenta')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'moneda_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="moneda_id">{{ __('Currency') }}</label>
        <select x-ref="select" id="moneda_id"
                class="select2 form-select @error('moneda_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->currencies as $currency)
            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
          @endforeach
        </select>
        @error('moneda_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="balance">{{ __('Balance') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $balance ?? '' }}',
            wireModelName: 'balance',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('balance', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="balance" x-ref="cleaveInput" wire:ignore class="form-control js-input-balance" />
          </div>
        </div>
        @error('balance')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="saldo">{{ __('Saldo') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $saldo ?? '' }}',
            wireModelName: 'saldo',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('saldo', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="saldo" x-ref="cleaveInput" wire:ignore class="form-control js-input-saldo" />
          </div>
        </div>
        @error('saldo')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="ultimo_cheque">{{ __('Último cheque') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx bx-receipt"></i></span>
          <input type="text" wire:model="ultimo_cheque" id="ultimo_cheque"
            class="form-control @error('ultimo_cheque') is-invalid @enderror" placeholder="{{ __('Último cheque') }}">
        </div>
        @error('ultimo_cheque')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-12 fv-plugins-icon-container"
          x-data="select2LivewireMultipleWithToggle({
            wireModelName: 'selected_banks',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="selected_banks">{{ __('Bank') }}</label>
        <select x-ref="select" id="selected_banks"
                class="form-select"
                multiple>
          @foreach ($this->banks as $bank)
            <option value="{{ $bank->id }}"> {{ $bank->name }} </option>
          @endforeach
        </select>
        @error('selected_banks')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-12 fv-plugins-icon-container"
          x-data="select2LivewireMultipleWithToggle({
            wireModelName: 'selected_locations',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="selected_locations">{{ __('Issuer') }}</label>
        <select x-ref="select" id="selected_locations"
                class="form-select"
                multiple>
          @foreach ($this->locations as $issuer)
            <option value="{{ $issuer->id }}">{{ $issuer->name }}</option>
          @endforeach
        </select>
        @error('selected_locations')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-12 fv-plugins-icon-container"
          x-data="select2LivewireMultipleWithToggle({
            wireModelName: 'selected_departments',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="selected_departments">{{ __('Departments') }}</label>
        <select x-ref="select" id="selected_departments"
                class="form-select"
                multiple>
          @foreach ($this->departments as $department)
            <option value="{{ $department->id }}"> {{ $department->name }} </option>
          @endforeach
        </select>
        @error('selected_departments')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="intruccionesPagoNacional">{{ __('Instrucciones de pago nacional') }}</label>
        <textarea class="form-control" wire:model="intruccionesPagoNacional" name="intruccionesPagoNacional" id="intruccionesPagoNacional" rows="7"
            placeholder="{{ __('Instrucciones de Pago columna uno') }}"></textarea>
        @error('intruccionesPagoNacional')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="intruccionesPagoInternacional">{{ __('Instrucciones de pago internacional') }}</label>
        <textarea class="form-control" wire:model="intruccionesPagoInternacional" name="intruccionesPagoInternacional" id="intruccionesPagoInternacional" rows="7"
            placeholder="{{ __('Instrucciones de Pago columna dos') }}"></textarea>
        @error('intruccionesPagoInternacional')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="lugar_fecha_y">{{ __('Lugar y Fecha Y') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $lugar_fecha_y ?? '' }}',
            wireModelName: 'lugar_fecha_y',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('lugar_fecha_y', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="lugar_fecha_y" x-ref="cleaveInput" wire:ignore class="form-control js-input-lugar_fecha_y" />
          </div>
        </div>
        @error('lugar_fecha_y')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="lugar_fecha_x">{{ __('Lugar y Fecha X') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $lugar_fecha_x ?? '' }}',
            wireModelName: 'lugar_fecha_x',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('lugar_fecha_x', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="lugar_fecha_x" x-ref="cleaveInput" wire:ignore class="form-control js-input-lugar_fecha_x" />
          </div>
        </div>
        @error('lugar_fecha_x')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="beneficiario_y">{{ __('Beneficiario Y') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $beneficiario_y ?? '' }}',
            wireModelName: 'beneficiario_y',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('beneficiario_y', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="beneficiario_y" x-ref="cleaveInput" wire:ignore class="form-control js-input-beneficiario_y" />
          </div>
        </div>
        @error('beneficiario_y')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="beneficiario_x">{{ __('Beneficiario X') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $beneficiario_x ?? '' }}',
            wireModelName: 'beneficiario_x',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('beneficiario_x', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="beneficiario_x" x-ref="cleaveInput" wire:ignore class="form-control js-input-beneficiario_x" />
          </div>
        </div>
        @error('beneficiario_x')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="monto_y">{{ __('Monto Y') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $monto_y ?? '' }}',
            wireModelName: 'monto_y',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('monto_y', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="monto_y" x-ref="cleaveInput" wire:ignore class="form-control js-input-monto_y" />
          </div>
        </div>
        @error('monto_y')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="monto_x">{{ __('Monto X') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $monto_x ?? '' }}',
            wireModelName: 'monto_x',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('monto_x', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="monto_x" x-ref="cleaveInput" wire:ignore class="form-control js-input-monto_x" />
          </div>
        </div>
        @error('monto_x')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="monto_letras_y">{{ __('Monto y Letras Y') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $monto_letras_y ?? '' }}',
            wireModelName: 'monto_letras_y',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('monto_letras_y', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="monto_letras_y" x-ref="cleaveInput" wire:ignore class="form-control js-input-monto_letras_y" />
          </div>
        </div>
        @error('monto_letras_y')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="monto_letras_x">{{ __('Monto y Letras X') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $monto_letras_x ?? '' }}',
            wireModelName: 'monto_letras_x',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('monto_letras_x', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="monto_letras_x" x-ref="cleaveInput" wire:ignore class="form-control js-input-monto_letras_x" />
          </div>
        </div>
        @error('monto_letras_x')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="detalles_y">{{ __('Detalles Y') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $detalles_y ?? '' }}',
            wireModelName: 'detalles_y',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('detalles_y', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="detalles_y" x-ref="cleaveInput" wire:ignore class="form-control js-input-detalles_y" />
          </div>
        </div>
        @error('detalles_y')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="detalles_x">{{ __('Detalles X') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $detalles_x ?? '' }}',
            wireModelName: 'detalles_x',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('detalles_x', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="detalles_x" x-ref="cleaveInput" wire:ignore class="form-control js-input-detalles_x" />
          </div>
        </div>
        @error('detalles_x')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 my-6">
          <input type="checkbox" class="form-check-input" id="mostrar_lugar" wire:model="mostrar_lugar" {{ $mostrar_lugar==1
            ? 'checked' : '' }} />

          <label for="mostrar_lugar" class="switch-label">{{ __('Mostrar lugar') }}</label>
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="perosna_sociedad">{{ __('Persona o sociedad') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text">
            <i class="bx bx-user"></i>
          </span>
          <input type="text" wire:model="perosna_sociedad" id="perosna_sociedad"
            class="form-control @error('perosna_sociedad') is-invalid @enderror" placeholder="{{ __('Persona o sociedad') }}">
        </div>
        @error('perosna_sociedad')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <br>

    <fieldset class="border p-3 mb-4">
      <legend class="w-auto px-2">{{ __('Configuración para Cuentas 3-101') }}</legend>
      <div class="row g-6">
        <div class="col-md-3 fv-plugins-icon-container">
          <div class="form-check form-switch ms-2 my-2">
            <input type="checkbox" class="form-check-input" id="is_cuenta_301" wire:model="is_cuenta_301" {{ $is_cuenta_301==1
              ? 'checked' : '' }} />

            <label for="is_cuenta_301" class="switch-label">{{ __('¿Es una cuenta 3-101?') }}</label>
          </div>
        </div>

        <div class="col-md-3 fv-plugins-icon-container">
          <div class="form-check form-switch ms-2 my-2">
            <input type="checkbox" class="form-check-input" id="calcular_pendiente_registro" wire:model="calcular_pendiente_registro" {{ $calcular_pendiente_registro==1
              ? 'checked' : '' }} />

            <label for="calcular_pendiente_registro" class="switch-label">{{ __('Calcular Pendiente Registro') }}</label>
          </div>
        </div>

        <div class="col-md-3 fv-plugins-icon-container">
          <div class="form-check form-switch ms-2 my-2">
            <input type="checkbox" class="form-check-input" id="calcular_traslado_gastos" wire:model="calcular_traslado_gastos" {{ $calcular_traslado_gastos==1
              ? 'checked' : '' }} />

            <label for="calcular_traslado_gastos" class="switch-label">{{ __('Calcular Traslado de Gastos') }}</label>
          </div>
        </div>

        <div class="col-md-3 fv-plugins-icon-container">
          <div class="form-check form-switch ms-2 my-2">
            <input type="checkbox" class="form-check-input" id="calcular_traslado_honorarios" wire:model="calcular_traslado_honorarios" {{ $calcular_traslado_honorarios==1
              ? 'checked' : '' }} />

            <label for="calcular_traslado_honorarios" class="switch-label">{{ __('Calcular Traslado de Honorarios') }}</label>
          </div>
        </div>

        <div class="col-md-4 select2-primary fv-plugins-icon-container"
            x-data="select2Livewire({
              wireModelName: 'banco_id',
              postUpdate: true
            })"
            x-init="init($refs.select)"
            wire:ignore>
          <label class="form-label" for="banco_id">{{ __('Bank') }}</label>
          <select x-ref="select" id="banco_id"
                  class="select2 form-select @error('banco_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->banks as $bank)
              <option value="{{ $bank->id }}"> {{ $bank->name }} </option>
            @endforeach
          </select>
          @error('banco_id')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

      </div>
    </fieldset>

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
  (function() {
    // Función para inicializar Select2
    const initializeSelect2 = () => {
      const selects = [
        'moneda_id', 'selected_banks', 'selected_locations', 'selected_departments', 'banco_id'
      ];

      selects.forEach((id) => {
        $(`#${id}`).select2();

        $(`#${id}`).on('change', function() {
          const newValue = $(this).val();
          const livewireValue = @this.get(id);

          if (newValue !== livewireValue) {
            // Actualiza Livewire solo si es el select2 de `condition_sale`
            // Hay que poner wire:ignore en el select2 para que todo vaya bien
            //const specificIds = ['condition_sale', 'location_id', 'department_id']; // Lista de IDs específicos
            const specificIds = []; // Lista de IDs específicos

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

    // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
    Livewire.on('reinitFormCuentaControls', () => {
      console.log('Reinicializando controles después de Livewire update reinitFormCuentaControls');
      setTimeout(() => {
        initializeSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });
  })();
</script>
@endscript
*/
@endphp
