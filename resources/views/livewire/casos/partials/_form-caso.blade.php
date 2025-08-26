<?php
use App\Models\User;
use App\Models\CasoEstado;
?>
<!-- Formulario para productos -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    <div class="row g-6">
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="numero">{{ __('Número') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="numero" id="numero"
              class="form-control @error('numero') is-invalid @enderror"
              placeholder="{{ __('Número') }}"
              disabled>
        </div>
        @error('numero')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="deudor">{{ __('Deudor') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="deudor" id="deudor" class="form-control @error('deudor') is-invalid @enderror"
            placeholder="{{ __('Deudor') }}"
             {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('deudor')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="cedula_deudor">{{ __('Cédula deudor') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="cedula_deudor" id="cedula_deudor" class="form-control @error('cedula_deudor') is-invalid @enderror"
            placeholder="{{ __('Cédula deudor') }}">
        </div>
        @error('cedula_deudor')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="numero_tomo">{{ __('Número de tomo') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="numero_tomo" id="numero_tomo" class="form-control @error('numero_tomo') is-invalid @enderror"
            placeholder="{{ __('Número de tomo') }}"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('numero_tomo')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="asiento_presentacion">{{ __('Asiento de presentación') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="asiento_presentacion" id="asiento_presentacion" class="form-control @error('asiento_presentacion') is-invalid @enderror"
            placeholder="{{ __('Asiento de presentación') }}"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('asiento_presentacion')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="department_id">{{ __('Department') }}</label>
        <div wire:ignore>
          <select wire:model.live="department_id" id="department_id" class="select2 form-select @error('department_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->departments as $department)
              <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
          </select>
        </div>
        @error('department_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <br>
      <h6 class="mt-4">2. {{ __('Información de Fechas') }}</h6>
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fecha_firma">{{ __('Fecha de firma') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="fecha_firma" @if (!$recordId) readonly @endif
            wire:model="fecha_firma"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('fecha_firma') is-invalid @enderror"
            placeholder="dd-mm-aaaa"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('fecha_firma')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fecha_presentacion">{{ __('Fecha de presentación') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="fecha_presentacion" @if (!$recordId) readonly @endif
            wire:model="fecha_presentacion"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('fecha_presentacion') is-invalid @enderror"
            placeholder="dd-mm-aaaa"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('fecha_presentacion')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fecha_inscripcion">{{ __('Fecha de Inscripción') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="fecha_inscripcion" @if (!$recordId) readonly @endif
            wire:model="fecha_inscripcion"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('fecha_inscripcion') is-invalid @enderror"
            placeholder="dd-mm-aaaa"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('fecha_inscripcion')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fecha_entrega">{{ __('Fecha de entrega') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="fecha_entrega" @if (!$recordId) readonly @endif
            wire:model="fecha_entrega"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('fecha_entrega') is-invalid @enderror"
            placeholder="dd-mm-aaaa"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('fecha_entrega')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <br>
      <h6 class="mt-4">3. {{ __('Información de abogados') }}</h6>
      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="abogado_cargo_id">{{ __('Abogado a cargo') }}</label>
        <div wire:ignore>
          <select wire:model="abogado_cargo_id" id="abogado_cargo_id" class="select2 form-select @error('abogado_cargo_id') is-invalid @enderror"
          {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->abogados as $abogado)
              <option value="{{ $abogado->id }}">{{ $abogado->name }}</option>
            @endforeach
          </select>
        </div>
        @error('abogado_cargo_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="abogado_revisor_id">{{ __('Abogado revisor') }}</label>
        <div wire:ignore>
          <select wire:model="abogado_revisor_id" id="abogado_revisor_id" class="select2 form-select @error('abogado_revisor_id') is-invalid @enderror"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->abogados as $abogado)
              <option value="{{ $abogado->id }}">{{ $abogado->name }}</option>
            @endforeach
          </select>
        </div>
        @error('abogado_revisor_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="abogado_formalizador_id">{{ __('Abogado formalizador') }}</label>
        <div wire:ignore>
          <select wire:model="abogado_formalizador_id" id="abogado_formalizador_id" class="select2 form-select @error('abogado_formalizador_id') is-invalid @enderror"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->abogados as $abogado)
              <option value="{{ $abogado->id }}">{{ $abogado->name }}</option>
            @endforeach
          </select>
        </div>
        @error('abogado_formalizador_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="asistente_id">{{ __('Asistente') }}</label>
        <div wire:ignore>
          <select wire:model="asistente_id" id="asistente_id" class="select2 form-select @error('asistente_id') is-invalid @enderror"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->asistentes as $asistente)
              <option value="{{ $asistente->id }}">{{ $asistente->name }}</option>
            @endforeach
          </select>
        </div>
        @error('asistente_id-')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <br>
      <h6>4. {{ __('Información complementaria') }}</h6>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="bank_id">{{ __('Bank') }}</label>
        <select wire:model="bank_id" id="bank_id" class="select2 form-select @error('bank_id') is-invalid @enderror"
          {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->banks as $bank)
            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
          @endforeach
        </select>
        @error('bank_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="sucursal">{{ __('Sucursal') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="sucursal" id="sucursal"
              class="form-control @error('sucursal') is-invalid @enderror"
              placeholder="{{ __('Sucursal') }}"
              {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('sucursal')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="currency_id">{{ __('Currency') }}</label>
        <div wire:ignore>
          <select wire:model="currency_id" id="currency_id" class="select2 form-select @error('currency_id') is-invalid @enderror"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
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
        <label class="form-label" for="monto">{{ __('Monto') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $monto ?? '' }}',
            wireModelName: 'monto',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('monto', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="monto" x-ref="cleaveInput" wire:ignore class="form-control js-input-monto"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
          </div>
        </div>
        @error('monto')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="caratula_id">{{ __('Tipo de Carátula') }}</label>
        <div wire:ignore>
          <select wire:model="caratula_id" id="caratula_id" class="select2 form-select @error('caratula_id') is-invalid @enderror"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->caratulas as $caratula)
              <option value="{{ $caratula->id }}">{{ $caratula->name }}</option>
            @endforeach
          </select>
        </div>
        @error('caratula_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="garantia_id">{{ __('Número Garantía') }}</label>
        <div wire:ignore>
          <select wire:model="garantia_id" id="garantia_id" class="select2 form-select @error('garantia_id') is-invalid @enderror"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->garantias as $garantia)
              <option value="{{ $garantia->id }}">{{ $garantia->name }}</option>
            @endforeach
          </select>
        </div>
        @error('garantia_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="numero_garantia">{{ __('Número de garantía') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="numero_garantia" id="numero_garantia"
              class="form-control @error('numero_garantia') is-invalid @enderror"
              placeholder="{{ __('Número de garantía') }}"
              {!! in_array(session('current_role_name'), [User::ABOGADO, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('numero_garantia')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="costo_caso_retiro">{{ __('Costo de caso por retiro') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $costo_caso_retiro ?? '' }}',
            wireModelName: 'costo_caso_retiro',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('costo_caso_retiro', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="costo_caso_retiro" x-ref="cleaveInput" wire:ignore class="form-control js-input-costo_caso_retiro"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
          </div>
        </div>
        @error('costo_caso_retiro')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="nombre_formalizo">{{ __('Ejecutivo a cargo') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="nombre_formalizo" id="nombre_formalizo"
              class="form-control @error('nombre_formalizo') is-invalid @enderror"
              placeholder="{{ __('Ejecutivo a cargo') }}"
              {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('nombre_formalizo')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fiduciaria">{{ __('Fiduciaria') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="fiduciaria" id="fiduciaria"
              class="form-control @error('fiduciaria') is-invalid @enderror"
              placeholder="{{ __('fiduciaria') }}"
              {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('fiduciaria')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="desarrollador">{{ __('Desarrollador') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-box"></i></span>
          <input type="text" wire:model="desarrollador" id="desarrollador"
              class="form-control @error('desarrollador') is-invalid @enderror"
              placeholder="{{ __('Desarrollador') }}"
              {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('desarrollador')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="diasFirma">{{ __('Días desde la firma') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $diasFirma ?? '' }}',
            wireModelName: 'diasFirma',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('diasFirma', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="diasFirma" x-ref="cleaveInput" wire:ignore class="form-control js-input-diasFirma" disabled>
          </div>
        </div>
        @error('diasFirma')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="diasEntrega">{{ __('Días entrega') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $diasEntrega ?? '' }}',
            wireModelName: 'diasEntrega',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('diasEntrega', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="diasEntrega" x-ref="cleaveInput" wire:ignore class="form-control js-input-diasEntrega" disabled>
          </div>
        </div>
        @error('diasEntrega')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="numero_gestion">{{ __('Número de gestión') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="numero_gestion" id="numero_gestion" class="form-control @error('numero_gestion') is-invalid @enderror"
            placeholder="{{ __('Número de gestión') }}"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ABOGADO_EDITOR, User::ASISTENTE, User::BANCO]) ? 'disabled' : '' !!}>
        </div>
        @error('numero_gestion')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      @php
        if (session('current_role_name') || $this->estado_id == CasoEstado::DEFECTUOSO)
				  $this->estado_id = CasoEstado::EN_TRAMITE;
      @endphp

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="estado_id">{{ __('Estado') }}</label>
        <div wire:ignore>
          <select wire:model="estado_id" id="estado_id" class="select2 form-select @error('estado_id') is-invalid @enderror"
            {!! in_array(session('current_role_name'), [User::ABOGADO, User::ASISTENTE, User::AYUDANTE_JEFE, User::BANCO]) ? 'disabled' : '' !!}>
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->estados as $estado)
              <option value="{{ $estado->id }}">{{ $estado->name }}</option>
            @endforeach
          </select>
        </div>
        @error('estado_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-12 fv-plugins-icon-container">
        <label class="form-label" for="observaciones">{{ __('Observaciones') }}</label>
        <textarea class="form-control" wire:model="observaciones" name="observaciones" id="observaciones" rows="3" placeholder="{{ __('Observaciones') }}"></textarea>
        @error('observaciones')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
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

  window.select2Config = {
    department_id: {
      fireEvent: true,
      wireIgnore: true,
    },
    bank_id: {
      fireEvent: false,
      wireIgnore: false,
    },
    abogado_cargo_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    abogado_revisor_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    abogado_formalizador_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    asistente_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    currency_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    caratula_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    garantia_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    estado_id: {
      fireEvent: false,
      wireIgnore: true,
    }
  };


  $(document).ready(function() {
    Object.entries(select2Config).forEach(([id, config]) => {
      const $select = $('#' + id);
      if (!$select.length) return;

      $select.select2();

      // Default values
      const fireEvent = config.fireEvent ?? false;
      const wireIgnore = config.wireIgnore ?? false;
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

    window.initSelect2 = () => {
      Object.entries(select2Config).forEach(([id, config]) => {
        const $select = $('#' + id);
        if (!$select.length) return;

        const wireIgnore = config.wireIgnore ?? false;

        if (!wireIgnore) {
          $select.select2();
          console.log("Se reinició el select2 " + id);
        }
      });
    }

    initSelect2();

    Livewire.on('select2', () => {
      setTimeout(() => {
        initSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });
  })
</script>
@endscript
