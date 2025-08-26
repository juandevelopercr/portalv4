<?php
use App\Models\Currency;
?>
<div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th class="registro-bg-warning text-center" colspan="5"><strong>Monto cobrado en la factura</strong></th>
          <th class="text-center" colspan="15"><strong>Como se envia a Pagar</strong></th>
        </tr>
        <tr>
          <th scope="col">
            <div class="registro-form-check">
              <label class="vertical-text">Normal</label>
              <input type="checkbox" wire:click="toggleSelectAllNormal" class="select-on-check-all-normal">
            </div>
          </th>
          <th scope="col">
            <div class="registro-form-check">
              <label class="vertical-text">Solo Impuesto</label>
              <input type="checkbox" wire:click="toggleSelectAllIva" class="select-on-check-all-iva">
            </div>
          </th>
          <th scope="col">
            <div class="registro-form-check">
              <label class="vertical-text">Sin Impuesto</label>
              <input type="checkbox" wire:click="toggleSelectAllNoIva" class="select-on-check-all-no-iva">
            </div>
          </th>
          <th class="registro-bg-danger text-center"><strong>Detalle</strong></th>
          <th class="registro-bg-danger text-center"><strong>Moneda</strong></th>
          <th class="registro-bg-danger text-center"><strong>Monto Acto</strong></th>
          <th class="registro-bg-danger text-center" style="word-wrap: break-word;  min-width:100px; max-width:100px; white-space: normal;"><strong>Monto Unitario</strong></th>
          <th class="registro-bg-danger text-center"><strong>Cantidad</strong></th>
          <th class="registro-bg-danger text-center" style="word-wrap: break-word;  min-width:100px; max-width:100px; white-space: normal;"><strong>Monto Timbre</strong></th>
          <th class="registro-bg-danger text-center" style="word-wrap: break-word;  min-width:105px; max-width:105px; white-space: normal;"><strong>Fecha Elaboración </strong></th>

          <th class="registro-bg-success text-center"><strong>Moneda</strong></th>
          <th class="registro-bg-success text-center"><strong>Tipo de Cambio</strong></th>
          <th class="registro-bg-success text-center"><strong style="word-wrap: break-word;  min-width:300px; max-width:300px; white-space: normal;">Monto Original Valor de escritura</strong></th>
          <th class="registro-bg-success text-center"><strong>Cantidad</strong></th>
          <th class="registro-bg-success text-center" style="word-wrap: break-word;  min-width:150px; max-width:150px; white-space: normal;"><strong>Valor escritura en colones</strong></th>

          <th class="registro-bg-success text-center" style="word-wrap: break-word;  min-width:120px; max-width:120px; white-space: normal;"><strong>Timbre Pagado - 6%</strong></th>
          <th class="registro-bg-success text-center" style="word-wrap: break-word;  min-width:150px; max-width:150px; white-space: normal;"><strong>Valor fiscal</strong></th>
          <th class="registro-bg-success text-center"><strong>Monto EDDI</strong></th>
          <th class="registro-bg-success text-center"><strong>Estado</strong></th>
          <th class="registro-bg-success text-center"><strong>Fecha de pago</strong></th>
          <th class="registro-bg-success text-center" style="word-wrap: break-word;  min-width:160px; max-width:160px; white-space: normal;"><strong>Número ck</strong></th>
          <th class="registro-bg-success text-center" style="word-wrap: break-word;  min-width:120px; max-width:120px; white-space: normal;"><strong>Timbres Cobrado Vs Pagado</strong></th>
          <th class="registro-bg-success text-center" style="word-wrap: break-word;  min-width:120px; max-width:120px; white-space: normal;"><strong>Alerta</strong></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($this->lines as $index => $line)
          <tr wire:key="registro-{{ $line['id'] }}-{{ $refreshCounter }}">
            @php
              if (is_null($line['registro_cantidad']) || empty($line['registro_cantidad']))
                    $line['registro_cantidad'] = 1;
              // Hacer esta llamada primero porque se usa el monto_timbre en los otros metodos
            @endphp
            <th scope="row" style="{{ $line['calculo_registro_normal'] == 1 ? 'background-color: #66c732;' : '' }}">
              <div class="registro-form-check vertical-align-top">
                <input type="checkbox"
                      value="{{ $line['id'] }}"
                      wire:click="toggleLineCheckbox({{ $line['id'] }}, 'normal')"
                      @checked(in_array($line['id'], $this->selectedNormal))
                      class="chk-normal">
              </div>
            </th>
            <th scope="row" style="{{ $line['calculo_registro_iva'] == 1 ? 'background-color: #66c732;' : '' }}">
              <div class="registro-form-check vertical-align-top">
                @if ($line['impuesto_and_timbres_separados'])
                  <input type="checkbox"
                        value="{{ $line['id'] }}"
                        wire:click="toggleLineCheckbox({{ $line['id'] }}, 'iva')"
                        @checked(in_array($line['id'], $this->selectedIva))
                        class="chk-iva">
                @endif
              </div>
            </th>
            <th scope="row" style="{{ $line['calculo_registro_no_iva'] == 1 ? 'background-color: #66c732;' : '' }}">
              <div class="registro-form-check vertical-align-top">
                @if ($line['impuesto_and_timbres_separados'])
                  <input type="checkbox"
                        value="{{ $line['id'] }}"
                        wire:click="toggleLineCheckbox({{ $line['id'] }}, 'noiva')"
                        @checked(in_array($line['id'], $this->selectedNoIva))
                        class="chk-no-iva">
                @endif
              </div>
            </th>
            <td class="registro-bg-warning" style="word-wrap: break-word;  min-width:300px; max-width:300px; white-space: normal;">
              {{ $line['detail'] }}
            </td>
            <td class="registro-bg-warning">{{ $this->invoice->currency?->code }}</td>
            <td class="registro-bg-warning" align="right">
              {{ number_format($line['price'], 2, '.', ',') }}
            </td>
            <td class="registro-bg-warning" align="right">
              @php
              $moneda = $this->invoice->currency_id == Currency::COLONES ? 'COLONES' : 'DOLARES';
              $monto_unitario = $line['timbres'] / $line['quantity'];
              @endphp
              {{ $line['enable_quantity'] ? number_format($monto_unitario, 2, '.', ',') : 'No Aplica' }}
            </td>
            <td class="registro-bg-warning" align="center">
              {{ (int) $line['quantity'] }}
            </td>
            <td class="registro-bg-warning" align="right">
              {{ number_format($line['timbres'], 2, '.', ',') }}
            </td>
            <td class="registro-bg-warning">
              @if(!is_null($line['fecha_reporte_gasto']))
                <span class="badge rounded-pill bg-danger">
                      {{ \Carbon\Carbon::parse($line['fecha_reporte_gasto'])->format('d-m-Y') }}
                </span>
              @endif
            </td>
            <td>
              <div style="width:100px">
                <select wire:model.defer="lines.{{ $index }}.registro_currency_id" class="form-select">
                    <option value="">Seleccione...</option>
                    @foreach($this->monedas as $moneda)
                        <option value="{{ $moneda->id }}">
                            {{ $moneda->code }}
                        </option>
                    @endforeach
                </select>
              </div>
            </td>
            <td>
              <div class="has-validation"
                      x-data="{
                          rawValue: @js(data_get($line['registro_change_type'], $index . '.registro_change_type', '')),
                          maxLength: 15,
                          hasError: {{ json_encode($errors->has('lines.' . $index . '.registro_change_type')) }}
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
                  <!-- Input con máscara -->
                  <input wire:model="lines.{{ $index }}.registro_change_type" id="registro_change_type_{{ $index }}"
                      class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                      x-ref="cleaveInput" />
                </div>

                <!-- Mensaje de error -->
                @error('lines.'.$index.'.registro_change_type')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
              </div>
            </td>
            <td style="min-width:150px; max-width:150px; white-space: normal;">
                <div class="has-validation"
                      x-data="{
                          rawValue: @js(data_get($line['registro_monto_escritura'], $index . '.registro_monto_escritura', '')),
                          maxLength: 15,
                          hasError: {{ json_encode($errors->has('lines.' . $index . '.registro_monto_escritura')) }}
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
                  <!-- Input con máscara -->
                  <input wire:model="lines.{{ $index }}.registro_monto_escritura" id="registro_monto_escritura_{{ $index }}"
                      class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                      x-ref="cleaveInput"
                      :disabled={{ $line['enable_registration_calculation'] ? 'false' : 'true' }}
                      />
              </div>
              <!-- Mensaje de error -->
              @error('lines.'.$index.'.registro_monto_escritura')
                <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </td>
            <td align="right">
              @if ($line['enable_quantity'] == 1)
                <div class="has-validation"
                      x-data="{
                          rawValue: @js(data_get($line['registro_cantidad'], $index . '.registro_cantidad', '')),
                          maxLength: 15,
                          hasError: {{ json_encode($errors->has('lines.' . $index . '.registro_cantidad')) }}
                      }" x-init="
                          let cleaveInstance = new Cleave($refs.cleaveInput, {
                              numeral: true,
                              numeralThousandsGroupStyle: 'thousand',
                              numeralDecimalMark: '.',
                              delimiter: '',
                              numeralDecimalScale: 0,
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
                  <!-- Input con máscara -->
                  <input wire:model="lines.{{ $index }}.registro_cantidad" id="registro_cantidad_{{ $index }}"
                      class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                      x-ref="cleaveInput"
                      :disabled={{ $line['registro_cantidad'] ? 'false' : 'true' }}
                      />
                </div>
              @endif
            </td>
            <td align="right">
              {{ number_format($line['monto_escritura_colones_grid'], 2, '.', ',');  }}
            </td>
            <td>
              {{ number_format($line['monto_timbre_escritura'], 2, '.', ',');  }}
            </td>
            <td align="right">
              <div class="has-validation"
                      x-data="{
                          rawValue: @js(data_get($line['registro_valor_fiscal'], $index . '.registro_valor_fiscal', '')),
                          maxLength: 15,
                          hasError: {{ json_encode($errors->has('lines.' . $index . '.registro_valor_fiscal')) }}
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
                  <!-- Input con máscara -->
                  <input wire:model="lines.{{ $index }}.registro_valor_fiscal" id="registro_valor_fiscal_{{ $index }}"
                      class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                      x-ref="cleaveInput"
                      :disabled={{ $line['enable_registration_calculation'] ? 'false' : 'true' }}
                      />
              </div>
            </td>
            <td>
              {{ number_format($line['monto_eddi'], 2, '.', ','); }}
            </td>
            <td>
              @if ($line['estado_escritura'] == 'PAGADO')
                <span class="badge rounded-pill bg-danger">{{ $line['estado_escritura'] }}</span>
              @else
                <span class="badge rounded-pill bg-secondary">{{ $line['estado_escritura'] }}</span>
              @endif
            </td>
            <td>
              <input type="date" wire:model="lines.{{ $index }}.fecha_pago_registro" id="fecha_pago_registro_{{ $index }}"
                    class="form-control @error('lines.{{ $index }}.fecha_pago_registro') is-invalid @enderror">
            </td>
            <td>
              <div class="has-validation"
                      x-data="{
                          rawValue: @js(data_get($line['numero_pago_registro'], $index . '.numero_pago_registro', '')),
                          maxLength: 15,
                          hasError: {{ json_encode($errors->has('lines.' . $index . '.numero_pago_registro')) }}
                      }" x-init="
                          let cleaveInstance = new Cleave($refs.cleaveInput, {
                              numeral: true,
                              numeralThousandsGroupStyle: 'thousand',
                              numeralDecimalMark: '.',
                              delimiter: '',
                              numeralDecimalScale: 0,
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
                  <!-- Input con máscara -->
                  <input wire:model="lines.{{ $index }}.numero_pago_registro" id="numero_pago_registro_{{ $index }}"
                      class="form-control numeral-mask" :class="{ 'is-invalid': hasError }" type="text"
                      x-ref="cleaveInput"/>
              </div>
            </td>
            <td align="right">
              <?php
              $monto_cobrado_factura = $line['timbres'];
              $monto_cobrado_factura = round($monto_cobrado_factura, 2);

              $monto_registro_cobrar = $line['monto_timbre_escritura'];

              if ($this->invoice->currency_id == 1){

                if ($line['registro_change_type'] > 0)
                  $tipo_cambio = $line['registro_change_type'];
                else
                  $tipo_cambio = $this->invoice->proforma_change_type;

                $monto_registro_cobrar = $monto_registro_cobrar / $tipo_cambio;
              }
              else{
                $monto_registro_cobrar = round($monto_registro_cobrar, 2);
              }
              ?>

              <?= number_format($monto_registro_cobrar, 2, '.', ',');  ?>
            </td>
            <td align="center">
              @if ($monto_registro_cobrar > 0)
                @if ($monto_registro_cobrar > $monto_cobrado_factura)
                  <span class="badge rounded-pill bg-danger">PAGADO INCORRECTAMENTE</span>
                @else
                  <span class="badge rounded-pill bg-success">PAGADO CORRECTAMENTE</span>
                @endif
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="row g-6">
      <div class="pt-6 pb-6">
        <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1 mt-5" wire:loading.attr="disabled"
          wire:target="update">
          <span wire:loading.remove wire:target="update">
            <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Save') }}
          </span>
          <span wire:loading wire:target="update">
            <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Saving...') }}
          </span>
        </button>

        <!-- Botón back -->
        <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="back"
          wire:loading.attr="disabled" wire:target="back">
          <span wire:loading.remove wire:target="back">
            <span class="fa fa-remove bx-18px me-2"></span>{{ __('Regresar') }}
          </span>
          <span wire:loading wire:target="back">
            <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Regresando...') }}
          </span>
        </button>
      </div>
    </div>
</div>

@php
/*
@script()
<script>
  (function () {
        Livewire.on('exportReady', (dataArray) => {

          const data = Array.isArray(dataArray) ? dataArray[0] : dataArray;
          const prepareUrl = data.prepareUrl;
          const downloadBase = data.downloadBase;

          Livewire.dispatch('showLoading', [{ message: 'Generando reporte. Por favor espere...' }]);

          setTimeout(() => {
            fetch(prepareUrl)
              .then(res => {
                if (!res.ok) throw new Error('Respuesta inválida (prepare export)');
                return res.json();
              })
              .then(response => {
                const downloadUrl = `${downloadBase}/${response.filename}`;
                //window.location.assign(downloadUrl);
                console.log("Se ejecutó refresh-grid");
                Livewire.dispatch('refresh-grid');
                setTimeout(() => Livewire.dispatch('hideLoading'), 1000);
              })
              .catch(err => {
                console.error(err);
                Livewire.dispatch('hideLoading');
                //alert('Error al generar el archivo');
              });
          }, 100);
        });
    })();
</script>
@endscript
*/
@endphp
