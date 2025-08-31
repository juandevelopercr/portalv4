<div class="pt-6 pb-0">
    <h6>3. {{ __('Discounts') }}</h6>
</div>

<div class="row g-3">
    @forelse ($discounts as $index => $discount)
    <div class="row g-3">
        <!-- Discount Type ID -->
        <div class="col-md-3 select2-primary fv-plugins-icon-container" x-data>
            <label class="form-label" for="discount_type_id_{{ $index }}">{{ __('Type') }}</label>
            <select id="discount_type_id_{{ $index }}"
                class="select2 form-select @error('discounts.'.$index.'.discount_type_id') is-invalid @enderror" x-init="$nextTick(() => {
            let select = $('#discount_type_id_{{ $index }}').select2();
            select.on('change', (e) => {
                $wire.set('discounts.{{ $index }}.discount_type_id', select.val());
                $dispatch('discount-type-changed', { index: {{ $index }}, value: select.val() });
            });
        })" wire:model.live="discounts.{{ $index }}.discount_type_id">
                <option value="">{{ __('Seleccione...') }}</option>
                @foreach ($this->discountTypes as $discountType)
                <option value="{{ $discountType->id }}">{{ $discountType->code.'-'.$discountType->name }}</option>
                @endforeach
            </select>
            @error('discounts.'.$index.'.discount_type_id')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- discount_type_other -->
        @if($discount['discount_type_id'] == '99')
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="discount_type_other_{{ $index }}">{{ __('Descuento OTRO') }}</label>
            <div class="input-group input-group-merge has-validation">
                <span id="spandiscount_type_other_{{ $index }}" class="input-group-text"><i
                        class="bx bx-barcode"></i></span>
                <input type="text" wire:model="discounts.{{ $index }}.discount_type_other"
                    id="discount_type_other_{{ $index }}"
                    class="form-control @error('discounts.'.$index.'.discount_type_other') is-invalid @enderror"
                    placeholder="{{ __('Código de impuesto OTRO') }}">
            </div>
            @error('discounts.'.$index.'.discount_type_other')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="nature_discount_{{ $index }}">{{ __('Naturaleza del Descuento')
                }}</label>
            <div class="input-group input-group-merge has-validation">
                <span id="spannature_discount_{{ $index }}" class="input-group-text"><i
                        class="bx bx-barcode"></i></span>
                <input type="text" wire:model="discounts.{{ $index }}.nature_discount" id="nature_discount_{{ $index }}"
                    class="form-control @error('discounts.'.$index.'.nature_discount') is-invalid @enderror"
                    placeholder="{{ __('Naturaleza del Descuento') }}">
            </div>
            @error('discounts.'.$index.'.nature_discount')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- Percent -->
        <div class="col-md-2 fv-plugins-icon-container">
          <label class="form-label" for="discounts_{{ $index }}">{{ __('Percent') }}</label>
          <div class="input-group has-validation">
              <!-- Ícono alineado a la izquierda -->
              <span class="input-group-text" id="spandiscount_percent_{{ $index }}">
                  <i class="bx bx-calculator"></i>
              </span>
              <!-- Input con Alpine.js -->
              <input id="discount_percent_{{ $index }}" class="form-control numeral-mask"
                  :class="{ 'is-invalid': hasError }" type="text" placeholder="{{ __('Percent') }}"
                  x-data="{ 
                     rawValue: @entangle("discounts.{$index}.discount_percent"), 
                     maxLength: 15, 
                     hasError: @json($errors->has("discounts.{$index}.discount_percent")), 
                     timeout: null 
                  }"
                  x-init="
                      let cleaveInstance = new Cleave($el, {
                          numeral: true,
                          numeralThousandsGroupStyle: 'thousand',
                          numeralDecimalMark: '.',
                          delimiter: ',',
                          numeralDecimalScale: 2,
                      });
      
                      // Establece el valor inicial formateado
                      $el.value = rawValue || '';
                      
                      // Escucha los cambios en el input y dispara un evento después de un tiempo
                      $el.addEventListener('input', (e) => {
                          let cleanValue = cleaveInstance.getRawValue();
      
                          if (cleanValue.length <= maxLength) {
                              rawValue = cleanValue;
                          } else {
                              // Evita exceder el límite
                              rawValue = cleanValue.slice(0, maxLength);
                              cleaveInstance.setRawValue(rawValue);
                          }
      
                          clearTimeout(timeout);  // Cancela cualquier temporizador existente
                          timeout = setTimeout(() => {
                              $dispatch('percent-changed', { index: @json($index), value: rawValue });
                          }, 500); // Dispara el evento después de 500 ms de inactividad
                      });
                  "
                  x-ref="cleaveInput"
              />
          </div>
          <!-- Mostrar mensaje de error -->
          @error("discounts.{$index}.discount_percent")
              <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>

        <!-- Amount -->
        @php
        // Lo quité porque no funciona en el edit
        /*
        <div class="col-md-2 fv-plugins-icon-container">
            <label class="form-label" for="discounts_{{ $index }}">{{ __('Amount') }}</label>
            <div class="input-group input-group-merge has-validation">
                <span id="spandiscount_amount_{{ $index }}" class="input-group-text"><i
                        class="bx bx-calculator"></i></span>
                <input type="number" wire:model="discounts.{{ $index }}.discount_amount"
                    id="discount_amount_{{ $index }}" readonly
                    class="form-control @error('discounts.'.$index.'.discount_amount') is-invalid @enderror"
                    placeholder=" {{ __('Amount') }}" step="0.01">
            </div>
            @error('discounts.'.$index.'.discount_amount')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
            <div class="form-text">
                {{ __('The amount is calculated by the system') }}
            </div>
        </div>
        */
        @endphp

        <div class="col-md-1 pt-6">
            <button type="button" class="btn btn-label-danger" wire:click="removeDiscount({{ $index }})">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <hr>
    </div>
    @empty
    <div class="col-md-12 text-left">
        <p>{{ __('No discount added yet.') }}</p>
    </div>
    @endforelse
</div>

<!-- Botón para agregar impuestos (siempre visible) -->
<div class="row g-6">
    <div class="col-md-2">
        <button type="button" class="btn btn-secondary mt-2" wire:click="addDiscount">+ {{ __('Discount')
            }}</button>
    </div>
</div>
