@foreach ($payments as $index => $payment)
    <div class="border p-3 mb-2 rounded" wire:key="payment-{{ $index }}-{{ count($payments) }}">
      <div class="row g-6">
        <div class="d-flex justify-content-between">
            <strong>Pago {{ $index + 1 }} <br></strong>
            @if(count($payments) > 1)
                <button
                    class="btn btn-sm btn-danger"
                    wire:click="removePayment({{ $index }})"
                    wire:loading.attr="disabled"
                    wire:target="removePayment({{ $index }})"
                    type="button"
                >
                    <span wire:loading.remove wire:target="removePayment({{ $index }})">Eliminar</span>
                    <span wire:loading wire:target="removePayment({{ $index }})">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Eliminando...
                    </span>
                </button>
            @endif
        </div>

        <div class="col-md-3 select2-primary fv-plugins-icon-container"
            x-data
            x-init="
                setTimeout(() => {
                    let select = $('#tipo_medio_pago_{{ $index }}');
                    
                    // Inicializa select2
                    select.select2();

                    // Setea el valor desde Livewire (con JS puro)
                    select.val('{{ $payments[$index]['tipo_medio_pago'] ?? '' }}').trigger('change');

                    // Cuando cambia, envía el valor a Livewire
                    select.on('change', function () {
                        let value = $(this).val();
                        @this.set('payments.{{ $index }}.tipo_medio_pago', value);
                        $dispatch('updated', { index: {{ $index }}, value });
                    });
                }, 100);
            ">
            <label class="form-label" for="tipo_medio_pago_{{ $index }}">{{ __('Payment method') }}</label>

            <div wire:ignore>
                <select id="tipo_medio_pago_{{ $index }}"
                        class="select2 form-select @error('payments.'.$index.'.tipo_medio_pago') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->paymentMethods as $paymentMethod)
                        <option value="{{ $paymentMethod->code }}">{{ $paymentMethod->code . '-' . $paymentMethod->name }}</option>
                    @endforeach
                </select>
            </div>

            @error('payments.'.$index.'.tipo_medio_pago')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>


        @if ($payment['tipo_medio_pago'] === '99')
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="medio_pago_otros_{{ $index }}">{{ __('Otro') }}</label>
            <div class="input-group input-group-merge has-validation">
                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                <input type="text" wire:model="payments.{{ $index }}.medio_pago_otros" id="medio_pago_otros_{{ $index }}"
                    class="form-control @error('payments.'.$index.'.medio_pago_otros') is-invalid @enderror"
                    placeholder="{{ __('OTRO') }}">
            </div>
            @error('payments.'.$index.'.medio_pago_otros')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        @endif


        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="total_medio_pago_{{ $index }}">{{ __('Amount') }}</label>
            <div class="input-group input-group-merge has-validation"
                x-data="{
                    rawValue: @js(data_get($this->payments, $index . '.total_medio_pago', '')), 
                    maxLength: 15, 
                    hasError: {{ json_encode($errors->has('payments.' . $index . '.total_medio_pago')) }}
                }"
                x-init="                        
                    let cleaveInstance = new Cleave($refs.cleaveInput, {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        numeralDecimalMark: '.',
                        delimiter: ',',
                        numeralDecimalScale: 2,
                    });

                    // Inicializa el valor formateado visual
                    if (rawValue) {
                        cleaveInstance.setRawValue(rawValue);
                    }

                    $watch('rawValue', (newValue) => {
                        cleaveInstance.setRawValue(newValue);
                    });

                    let timeout;
                    $refs.cleaveInput.addEventListener('input', () => {
                        clearTimeout(timeout);

                        let cleanValue = cleaveInstance.getRawValue();

                        rawValue = cleanValue.length <= maxLength
                            ? cleanValue
                            : cleanValue.slice(0, maxLength);

                        if (cleanValue.length > maxLength) {
                            cleaveInstance.setRawValue(rawValue);
                        }

                        // ✅ Livewire recibe el valor limpio
                        timeout = setTimeout(() => {
                            $wire.set('payments.{{ $index }}.total_medio_pago', rawValue);
                            $wire.call('recalcularVuelto');
                        }, 400);
                    });
                ">

                <!-- Input con máscara (sin wire:model directo) -->
                <input
                    id="total_medio_pago_{{ $index }}"
                    class="form-control numeral-mask"
                    :class="{ 'is-invalid': hasError }"
                    type="text"
                    placeholder="{{ __('Amount') }}"
                    x-ref="cleaveInput"
                    x-model="rawValue"
                />
            </div>

            <!-- Mensaje de error -->
            @error('payments.' . $index . '.total_medio_pago')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>



      </div>
    </div>
@endforeach

@php
    /*
    $sumaPagos = collect($payments)->sum(fn($p) => floatval($p['total_medio_pago']));
    $totalIngresado = collect($payments)->sum(fn($p) => floatval($p['total_medio_pago']));
    */
@endphp

@if($this->payment_status != 'paid')
    <button class="btn btn-outline-secondary mb-3" wire:click="addPayment">Agregar medio de pago</button>
@endif


<div class="text-end mt-3">
    <strong>Total factura:</strong> {{ number_format($this->totalComprobante, 2) }} <br>
    <strong>Total ingresado:</strong> {{ number_format($this->totalPagado, 2) }} <br>
    <strong>Vuelto:</strong> {{ number_format($this->vuelto, 2) }} <br>
    <strong>Pendiente por pagar:</strong> {{ number_format($this->pendientePorPagar, 2) }}
    <p class="mt-2">
    <strong>Estado del pago:</strong>
    @if ($this->payment_status === 'paid')
        <span class="badge bg-success">Pagado</span>
    @elseif ($this->payment_status === 'partial')
        <span class="badge bg-warning text-dark">Parcial</span>
    @else
        <span class="badge bg-danger">Pendiente</span>
    @endif
</p>
</div>
