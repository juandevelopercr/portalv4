<div id="customer-modal" class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" tabindex="-1">
  <form wire:submit.prevent="save" class="card-body">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Payments') }}</h5>
          <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Formulario para agregar/editar pagos -->
          <div class="border p-3 mb-3 rounded">
            <h5>
              @if(is_null($editingIndex))
                {{ __('Adicionar nuevo pago') }}
              @else
                {{ __('Editar pago') }} #{{ $editingIndex + 1 }}
              @endif
            </h5>

            <div class="row g-3">
              <!-- Medio de Pago -->
              <div class="col-md-3">
                <label class="form-label">{{ __('Payment method') }}</label>
                <select wire:model="paymentMethod"
                        class="form-select @error('paymentMethod') is-invalid @enderror">
                  <option value="">{{ __('Select...') }}</option>
                  @foreach ($paymentMethods as $method)
                    <option value="{{ $method->code }}">
                      {{ $method->name }}
                    </option>
                  @endforeach
                </select>
                @error('paymentMethod')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Otro Medio (solo cuando se selecciona 99) -->
              @if($paymentMethod === '99')
                <div class="col-md-3">
                  <label class="form-label">{{ __('Other method') }}</label>
                  <input type="text" wire:model="otherMethod"
                         class="form-control @error('otherMethod') is-invalid @enderror"
                         placeholder="{{ __('Specify method') }}">
                  @error('otherMethod')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              @endif

              <!-- Monto -->
              <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="paymentAmount">{{ __('Amount') }}</label>
                <div class="input-group input-group-merge has-validation"
                    x-data="{
                        rawValue: @js($paymentAmount ?? ''),
                        maxLength: 15,
                        hasError: {{ json_encode($errors->has('paymentAmount')) }}
                    }"
                    x-init="
                        let cleaveInstance = new Cleave($refs.cleaveInput, {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand',
                            numeralDecimalMark: '.',
                            delimiter: ',',
                            numeralDecimalScale: 2,
                        });

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
                        });
                    ">
                    <input
                        id="paymentAmount"
                        class="form-control numeral-mask"
                        :class="{ 'is-invalid': hasError }"
                        wire:model.live="paymentAmount"
                        type="text"
                        placeholder="{{ __('Amount') }}"
                        x-ref="cleaveInput"
                        x-model="rawValue"
                    />
                </div>
                <small class="text-muted">
                    Máximo: {{ number_format($maxPaymentAmount, 2) }}
                </small>
                @error('paymentAmount')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

              @php
              /*
              <div class="col-md-3">
                <label class="form-label">{{ __('Amount') }}</label>
                <input type="text"
                       wire:model.live="paymentAmount"
                       wire:input.debounce.500ms="updatePaymentAmount($event.target.value)"
                       class="form-control @error('paymentAmount') is-invalid @enderror"
                       placeholder="0.00">
                <small class="text-muted">
                  Máximo: {{ number_format($maxPaymentAmount, 2) }}
                </small>
                @error('paymentAmount')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              */
              @endphp


              <!-- Fecha -->
              <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="paymentDate">{{ __('Date') }}</label>
                <div class="input-group input-group-merge has-validation">
                  <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                  <input type="text" id="paymentDate"
                    wire:model="paymentDate"
                    x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
                    x-init="init($el)"
                    wire:ignore
                    class="form-control date-picke @error('paymentDate') is-invalid @enderror"
                    placeholder="dd-mm-aaaa">
                </div>
                @error('paymentDate')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
              </div>

              <!-- Banco -->
              <div class="col-md-3">
                <label class="form-label">{{ __('Bank') }}</label>
                <input type="text" wire:model="bank"
                       class="form-control"
                       placeholder="{{ __('Bank name') }}">
              </div>

              <!-- Referencia -->
              <div class="col-md-3">
                <label class="form-label">{{ __('Reference') }}</label>
                <input type="text" wire:model="reference"
                       class="form-control"
                       placeholder="{{ __('Reference number') }}">
              </div>

              <!-- Detalles -->
              <div class="col-md-6">
                <label class="form-label">{{ __('Details') }}</label>
                <input type="text" wire:model="details"
                       class="form-control"
                       placeholder="{{ __('Additional details') }}">
              </div>

              <!-- Botones de acción -->
              <div class="col-md-12 text-end mt-3">
                @if(is_null($editingIndex))
                  <button type="button" class="btn btn-primary"
                          wire:click="addPayment"
                          wire:loading.attr="disabled">
                    <i class="bx bx-plus me-2"></i>
                    {{ __('Adicionar pago') }}
                  </button>
                @else
                  <button type="button" class="btn btn-secondary me-2"
                          wire:click="cancelEdit">
                    {{ __('Cancel') }}
                  </button>
                  <button type="button" class="btn btn-primary"
                          wire:click="updatePayment"
                          wire:loading.attr="disabled">
                    <i class="bx bx-save me-2"></i>
                    {{ __('Update Payment') }}
                  </button>
                @endif
              </div>
            </div>
          </div>

          <!-- Resumen de pagos en tiempo real -->
          <div class="bg-light p-3 rounded border">
            <div class="row">
              <div class="col-md-3">
                <strong>{{ __('Total a pagar') }}:</strong>
                {{ number_format($totalComprobante, 2) }}
              </div>
              <div class="col-md-3">
                <strong>{{ __('Monto pagado') }}:</strong>
                {{ number_format($totalPagado, 2) }}
              </div>
              <div class="col-md-2">
                <strong>{{ __('Vuelto') }}:</strong>
                {{ number_format($vuelto, 2) }}
              </div>
              <div class="col-md-2">
                <strong>{{ __('Pendiente') }}:</strong>
                {{ number_format($pendientePorPagar, 2) }}
              </div>
              <div class="col-md-2">
                <strong>{{ __('Status') }}:</strong>
                @if ($payment_status === 'paid')
                  <span class="badge bg-success">{{ __('Pagado') }}</span>
                @elseif ($payment_status === 'partial')
                  <span class="badge bg-warning text-dark">{{ __('Parcial') }}</span>
                @else
                  <span class="badge bg-danger">{{ __('Pendiente') }}</span>
                @endif
              </div>
            </div>

            @if($vuelto > 0)
            <div class="alert alert-warning mt-2">
              <i class="bx bx-info-circle"></i>
              {{ __('El monto pagado ha sido ajustado al total por pagar.') }}
              {{ __('Change') }}: {{ number_format($vuelto, 2) }}
            </div>
            @endif
          </div>
        </div>


<!-- Lista de pagos existentes -->
          <div class="table-responsive mb-4">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Medio de Pago</th>
                  <th>Fecha</th>
                  <th>Monto</th>
                  <th>Detalles</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($payments as $index => $payment)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                      @php
                        $method = collect($paymentMethods)->firstWhere('code', $payment['tipo_medio_pago']);
                      @endphp
                      {{ $method ? $method->name : 'Desconocido' }}
                      @if($payment['tipo_medio_pago'] === '99' && $payment['medio_pago_otros'])
                        <div class="text-muted small">{{ $payment['medio_pago_otros'] }}</div>
                      @endif
                    </td>
                    <td>{{ $payment['created_at'] }}</td>
                    <td>{{ number_format($payment['total_medio_pago'], 2) }}</td>
                    <td>
                      @if($payment['banco']) <strong>Banco:</strong> {{ $payment['banco'] }}<br> @endif
                      @if($payment['referencia']) <strong>Ref:</strong> {{ $payment['referencia'] }}<br> @endif
                      @if($payment['detalle']) {{ $payment['detalle'] }} @endif
                    </td>
                    <td>
                      <button type="button" class="btn btn-sm btn-icon btn-primary"
                              wire:click="editPayment({{ $index }})">
                        <i class="bx bx-edit"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-icon btn-danger"
                              wire:click="removePayment({{ $index }})">
                        <i class="bx bx-trash"></i>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>


        <!-- Pie del modal -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1 mt-5"
                  wire:loading.attr="disabled"
                  wire:target="save">
              <span wire:loading.remove wire:target="save">
                  <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Save') }}
              </span>
              <span wire:loading wire:target="save">
                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Saving...') }}
              </span>
          </button>

          <button type="button" class="btn btn-success data-submit me-sm-4 me-1 mt-5"
                  wire:click="updateAndClose"
                  wire:loading.attr="disabled"
                  wire:target="updateAndClose">
              <span wire:loading.remove wire:target="updateAndClose">
                  <i class="tf-icons bx bx-save bx-18px me-2"></i> {{ __('Save and Close') }}
              </span>
              <span wire:loading wire:target="updateAndClose">
                  <i class="spinner-border spinner-border-sm me-2" role="status"></i> {{ __('Saving...') }}
              </span>
          </button>

          <button type="button" class="btn btn-secondary me-sm-4 me-1 mt-5"
                  wire:click="closeModal"
                  wire:loading.attr="disabled"
                  wire:target="closeModal">
              <span wire:loading.remove wire:target="closeModal">
                  <i class="bx bx-exit bx-sm me-2"></i>{{ __('Close') }}
              </span>
              <span wire:loading wire:target="closeModal">
                  <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  {{ __('Closing...') }}
              </span>
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
