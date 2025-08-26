<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    <div class="row g-6">
      <div class="col-md-5 fv-plugins-icon-container">
        <label class="form-label" for="description">{{ __('Description') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-detail"></i></span>
          <input type="text" wire:model="description" name="description" id="description"
            class="form-control @error('description') is-invalid @enderror" placeholder="{{ __('Description') }}"
            aria-label="{{ __('Description') }}" aria-describedby="spandescription">
        </div>
        @error('description')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-2 fv-plugins-icon-container">
        <label class="form-label" for="base">{{ __('Base') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $base ?? '' }}',
            wireModelName: 'base',
            postUpdate: false,
            decimalScale: 3,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('base', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="base" x-ref="cleaveInput" wire:ignore class="form-control js-input-base" />
          </div>
        </div>
        @error('base')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-2 fv-plugins-icon-container">
        <label class="form-label" for="porcada">{{ __('Por Cada') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $porcada ?? '' }}',
            wireModelName: 'porcada',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('porcada', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="porcada" x-ref="cleaveInput" wire:ignore class="form-control js-input-porcada" />
          </div>
        </div>
        @error('porcada')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'tipo',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="tipo">{{ __('Type of Notarial Act') }}</label>
        <select x-ref="select" id="tipo"
                class="select2 form-select @error('tipo') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          <option value="HONORARIO" wire:key="type_notarial_act-honorario">HONORARIO</option>
          <option value="GASTO" wire:key="type_notarial_act-gasto">GASTO</option>
        </select>
        @error('tipo')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>
    <br>

    <h6>2. {{ __('Configuración del Honorario o Timbre') }}</h6>

    <div class="row g-6">

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch" style="margin-top: 30px;">
          <input type="checkbox" class="form-check-input" id="porciento" wire:model.defer="porciento" {{ $porciento==1
            ? 'checked' : '' }} />
          <label for="future-billing" class="switch-label">{{ __('Percent') }}%</label>
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch" style="margin-top: 30px;">
          <input type="checkbox" class="form-check-input" id="tabla_abogado_inscripciones"
            wire:model.defer="tabla_abogado_inscripciones" @if($tabla_abogado_inscripciones==1) checked @endif
            @if($tipo=='HONORARIO' ) disabled @endif />
          <label for="tabla_abogado_inscripciones" class="switch-label">
            {{ __('Timbre Abogados Bienes Inmuebles') }}
          </label>
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch" style="margin-top: 30px;">
          <input type="checkbox" class="form-check-input" id="tabla_abogado_traspasos"
            wire:model.defer="tabla_abogado_traspasos" @if($tabla_abogado_traspasos==1) checked @endif
            @if($tipo=='HONORARIO' ) disabled @endif />

          <label for="tabla_abogado_traspasos" class="switch-label">
            {{ __('Timbre Abogados Bienes Muebles') }}
          </label>
        </div>
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'honorario_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="honorario_id">{{ __('Honorario') }}</label>
        <select x-ref="select" id="honorario_id"
                class="select2 form-select @error('honorario_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->honorarios as $honorario)
            <option value="{{ $honorario->id }}">{{ $honorario->name }}</option>
          @endforeach
        </select>
        @error('honorario_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch" style="margin-top: 30px;">
          <input type="checkbox" class="form-check-input" id="fijo" wire:model.defer="fijo" {{ $fijo==1 ? 'checked' : ''
            }} />
          <label for="future-billing" class="switch-label">{{ __('Fijo') }}</label>
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch" style="margin-top: 30px;">
          <input type="checkbox" class="form-check-input" id="monto_manual" wire:model.defer="monto_manual" {{
            $monto_manual==1 ? 'checked' : '' }} />
          <label for="future-billing" class="switch-label">{{ __('Monto Manual') }}</label>
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch" style="margin-top: 30px;">
          <input type="checkbox" class="form-check-input" id="fijo" wire:model.defer="descuento_timbre" {{
            $descuento_timbre==1 ? 'checked' : '' }} @if ($tipo=='HONORARIO' ) disabled @endif />
          <label for="future-billing" class="switch-label">{{ __('Discount') }}</label>
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch" style="margin-top: 30px;">
          <input type="checkbox" class="form-check-input" id="fijo" wire:model.defer="es_impuesto" {{ $es_impuesto==1
            ? 'checked' : '' }} />
          <label for="future-billing" class="switch-label">{{ __('¿Es Impuesto?') }}</label>
        </div>
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

@php
/*
@script()
<script>
  Livewire.on('reinitControls', postId => {
      jQuery(document).ready(function () {
          $('#bank_id').select2();
          $('#bank_id').on('change', function (e) {
              var data = $('#bank_id').select2("val");
              @this.set('bank_id', data, false);
          });

          $('#honorario_id').select2();
          $('#honorario_id').on('change', function (e) {
              var data = $('#honorario_id').select2("val");
              @this.set('honorario_id', data, false);
          });

          $('#tipo').select2();
          $('#tipo').on('change', function (e) {
              var data = $('#tipo').select2("val");
              @this.set('tipo', data, false);
          });
      });
  });
</script>
@endscript
*/
@endphp
