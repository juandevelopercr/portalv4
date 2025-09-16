<div class="col-md-3 select2-primary fv-plugins-icon-container"
     x-data
     x-init="
        setTimeout(() => {
            let select = $('#exoneration_type_id_{{ $index }}');

            // Inicializa select2
            select.select2();

            // Establece el valor actual desde Livewire
            select.val('{{ $taxes[$index]['exoneration_type_id'] ?? '' }}').trigger('change');

            // Cuando cambia, actualiza el modelo y lanza evento personalizado
            select.on('change', function () {
                let value = $(this).val();
                @this.set('taxes.{{ $index }}.exoneration_type_id', value);
                $dispatch('updated', { index: {{ $index }}, value });
            });
        }, 100);
     ">
    <label class="form-label" for="exoneration_type_id_{{ $index }}">{{ __('Exhoneration') }}</label>

    <div wire:ignore>
        <select id="exoneration_type_id_{{ $index }}"
                class="select2 form-select @error('taxes.'.$index.'.exoneration_type_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->exhonerations as $exhoneration)
                <option value="{{ $exhoneration->id }}">{{ $exhoneration->code . '-' . $exhoneration->name }}</option>
            @endforeach
        </select>
    </div>

    @error('taxes.'.$index.'.exoneration_type_id')
        <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>


<!-- exoneration_doc_other -->
@if($tax['exoneration_type_id'] == '99')
  <div class="col-md-3 fv-plugins-icon-container">
      <label class="form-label" for="exoneration_doc_other_{{ $index }}">{{ __('Document No.') }} {{ __('Other') }}</label>
      <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-file"></i></span>
          <input type="text" wire:model="taxes.{{ $index }}.exoneration_doc_other" id="exoneration_doc_other_{{ $index }}"
              class="form-control @error('taxes.'.$index.'.exoneration_doc_other') is-invalid @enderror"
              placeholder="{{ __('Document No.') }} {{ __('Other') }}">
      </div>
      @error('taxes.'.$index.'.exoneration_doc_other')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
  </div>
@endif

<div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="exoneration_doc_{{ $index }}">{{ __('Document No.') }}</label>
    <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-file"></i></span>
        <input type="text" wire:model="taxes.{{ $index }}.exoneration_doc" id="exoneration_doc_{{ $index }}"
            class="form-control @error('taxes.'.$index.'.exoneration_doc') is-invalid @enderror"
            placeholder="{{ __('Document No.') }}">
    </div>
    @error('taxes.'.$index.'.exoneration_doc')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>

<!-- exoneration_article -->
@if(in_array($tax['exoneration_type_id'], ['2', '3', '6', '7', '8']))
<div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="exoneration_article_{{ $index }}">{{ __('Article') }}</label>
    <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-file"></i></span>
        <input type="text" wire:model="taxes.{{ $index }}.exoneration_article" id="exoneration_article_{{ $index }}"
            class="form-control @error('taxes.'.$index.'.exoneration_article') is-invalid @enderror"
            placeholder="{{ __('Article') }}">
    </div>
    @error('taxes.'.$index.'.exoneration_article')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>
@endif

<!-- exoneration_inciso -->
@if(in_array($tax['exoneration_type_id'], ['2', '3', '6', '7', '8']))
<div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="exoneration_inciso_{{ $index }}">{{ __('Inciso') }}</label>
    <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-file"></i></span>
        <input type="text" wire:model="taxes.{{ $index }}.exoneration_inciso" id="exoneration_inciso_{{ $index }}"
            class="form-control @error('taxes.'.$index.'.exoneration_inciso') is-invalid @enderror"
            placeholder="{{ __('Inciso') }}">
    </div>
    @error('taxes.'.$index.'.exoneration_inciso')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>
@endif

<!-- exoneration_institution_id -->
<div class="col-md-3 select2-primary fv-plugins-icon-container"
     x-data
     x-init="
        setTimeout(() => {
            let select = $('#exoneration_institution_id_{{ $index }}');

            // Inicializar select2
            select.select2();

            // Setear valor inicial desde Livewire
            select.val('{{ $taxes[$index]['exoneration_institution_id'] ?? '' }}').trigger('change');

            // Al cambiar, actualizar modelo y disparar evento
            select.on('change', function () {
                let value = $(this).val();
                @this.set('taxes.{{ $index }}.exoneration_institution_id', value);
                $dispatch('updated', { index: {{ $index }}, value });
            });
        }, 100);
     ">
    <label class="form-label" for="exoneration_institution_id_{{ $index }}">{{ __('Institute Name') }}</label>

    <div wire:ignore>
        <select id="exoneration_institution_id_{{ $index }}"
                class="select2 form-select @error('taxes.'.$index.'.exoneration_institution_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->institutes as $institute)
                <option value="{{ $institute->id }}">{{ $institute->code . '-' . $institute->name }}</option>
            @endforeach
        </select>
    </div>

    @error('taxes.'.$index.'.exoneration_institution_id')
        <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>

<!-- exoneration_institute_other -->
@if($tax['exoneration_institution_id'] == '99')
<div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="exoneration_institute_other_{{ $index }}">{{ __('Institute Name') }} {{ __('Other') }}</label>
    <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-file"></i></span>
        <input type="text" wire:model="taxes.{{ $index }}.exoneration_institute_other" id="exoneration_institute_other_{{ $index }}"
            class="form-control @error('taxes.'.$index.'.exoneration_institute_other') is-invalid @enderror"
            placeholder="{{ __('Institute Name') }} {{ __('Other') }}">
    </div>
    @error('taxes.'.$index.'.exoneration_institute_other')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>
@endif

@php
/*
<div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="exoneration_date_{{ $index }}">{{ __('Date') }}</label>
    <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
        <input type="date" wire:model="taxes.{{ $index }}.exoneration_date" id="exoneration_date_{{ $index }}"
            class="form-control @error('taxes.{{ $index }}.exoneration_date') is-invalid @enderror" placeholder="{{ __('Date') }}"
            aria-label="{{ __('Date') }}">
    </div>
    @error('taxes.{{ $index }}.exoneration_date')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>
*/
@endphp
<div class="col-md-3 fv-plugins-icon-container">
  <label class="form-label" for="exoneration_date_{{ $index }}">{{ __('Date') }}</label>
  <div class="input-group input-group-merge has-validation">
    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
    <input type="text" id="exoneration_date_{{ $index }}" @if (!$recordId) readonly @endif
      wire:model="taxes.{{ $index }}.exoneration_date"
      x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
      x-init="init($el)"
      wire:ignore
      class="form-control date-picke @error('taxes.{{ $index }}.exoneration_date') is-invalid @enderror"
      placeholder="dd-mm-aaaa">
  </div>
  @error('taxes.{{ $index }}.exoneration_date')
  <div class="text-danger mt-1">{{ $message }}</div>
  @enderror
</div>

<div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="exoneration_percent_{{ $index }}">{{ __('Exoneration Percent') }}</label>
    <div class="input-group input-group-merge has-validation" x-data="{
                rawValue: @js(data_get($this->taxes, $index . '.exoneration_percent', '')),
                maxLength: 15,
                hasError: {{ json_encode($errors->has('taxes.' . $index . '.exoneration_percent')) }}
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
        <input wire:model="taxes.{{ $index }}.exoneration_percent" id="tax_{{ $index }}"
            class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
            placeholder="{{ __('Exoneration Percent') }}" x-ref="cleaveInput" />
    </div>

    <!-- Mensaje de error -->
    @error('taxes.'.$index.'.exoneration_percent')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>

@php
/*
<div class="col-md-3 fv-plugins-icon-container">
  <label class="form-label" for="exoneration_percent_{{ $index }}">{{ __('Exoneration Percent') }}</label>
  <div
    x-data="cleaveLivewire({
      initialValue: '{{ $taxes[$index]['exoneration_percent'] ?? '' }}',
      wireModelName: 'taxes.{{ $index }}.exoneration_percent',
      //watchProperty: 'taxes[{{ $index }}].exoneration_percent',
      postUpdate: false,
      decimalScale: 2,
      allowNegative: false,
      rawValueCallback: (val) => {
        const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]')?.getAttribute('wire:id'));
        if (component) {
          component.set('taxes.{{ $index }}.exoneration_percent', val);
        }
      }
    })"
    x-init="init($refs.cleaveInput)"
  >
    <input type="text"
           id="exoneration_percent_{{ $index }}"
           x-ref="cleaveInput"
           wire:ignore
           class="form-control numeral-mask js-input-exoneration" />
  </div>
  @error("taxes.$index.exoneration_percent")
    <div class="text-danger mt-1">{{ $message }}</div>
  @enderror
</div>
*/
@endphp
