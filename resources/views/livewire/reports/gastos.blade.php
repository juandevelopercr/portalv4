<div class="card">
  <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Reporte de Gastos') }}</h4>
  <div class="card-datatable text-nowrap">
    <div class="dataTables_wrapper dt-bootstrap5 no-footer">
      <form wire:submit.prevent="exportExcel">
        <div class="row g-6">
          <div class="col-md-3 select2-primary fv-plugins-icon-container">
            <label class="form-label" for="filter_emisor">{{ __('Emisor') }}</label>
            <div wire:ignore>
              <select id="filter_emisor" class="form-select select2-ajax" data-placeholder="Buscar emisor">
              </select>
            </div>
          </div>

          <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label" for="filter_date">{{ __('Fecha de emisión del comprobante') }}</label>
            <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-calendar"></i></span>
              <input type="text" id="filter_date"
                wire:model="filter_date"
                x-data="rangePickerLivewire({ wireEventName: 'dateRangeSelected' })"
                x-init="init($el)"
                wire:ignore
                class="form-control range-picker @error('filter_date') is-invalid @enderror"
                placeholder="dd-mm-aaaa">
            </div>
            @error('filter_date')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-3 select2-primary fv-plugins-icon-container">
            <label class="form-label" for="filter_currency">{{ __('Currency') }}</label>
            <div wire:ignore>
              <select wire:model="filter_currency" id="filter_currency" class="select2 form-select @error('filter_currency') is-invalid @enderror">
                <option value="">{{ __('Seleccione...') }}</option>
                @foreach ($this->currencies as $currency)
                  <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                @endforeach
              </select>
            </div>
            @error('filter_currency')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-3 select2-primary fv-plugins-icon-container">
            <label class="form-label" for="filter_type">{{ __('Tipo de Facturación') }}</label>
            <div wire:ignore>
              <select wire:model="filter_type" id="filter_type" class="select2 form-select @error('filter_type') is-invalid @enderror">
                <option value="">{{ __('Seleccione...') }}</option>
                  <option value="01">FACTURA</option>
                  <option value="02">NOTA DE DEBITO</option>
                  <option value="03">NOTA DE CREDITO</option>
                  <option value="04">TIQUETE</option>
                  <option value="08">FACTURA DE COMPRA</option>
                  <option value="10">RECIBO DE PAGO</option>
              </select>
            </div>
            @error('filter_type')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-3 select2-primary fv-plugins-icon-container">
            <label class="form-label" for="filter_tax_type">{{ __('Tipo de Impuesto') }}</label>
            <div wire:ignore>
              <select wire:model="filter_tax_type" id="filter_tax_type" class="select2 form-select @error('filter_tax_type') is-invalid @enderror">
                <option value="">{{ __('Seleccione...') }}</option>
                @foreach ($this->tax_types as $tax_type)
                  <option value="{{ $tax_type['id'] }}">{{ $tax_type['name'] }}</option>
                @endforeach
              </select>
            </div>
            @error('filter_tax_type')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-3 select2-primary fv-plugins-icon-container">
            <label class="form-label" for="filter_status">{{ __('Status') }}</label>
            <div wire:ignore>
              <select wire:model="filter_status" id="filter_status" class="select2 form-select @error('filter_status') is-invalid @enderror">
                <option value="">{{ __('Seleccione...') }}</option>
                @foreach ($this->status as $statu)
                  <option value="{{ $statu['id'] }}">{{ $statu['name'] }}</option>
                @endforeach
              </select>
            </div>
            @error('filter_status')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <!-- Botones de acción -->
          <div class="col-md-3 d-flex align-items-end">
            {{-- Incluye botones de guardar y guardar y cerrar --}}
            <button type="button"
                    class="btn btn-primary data-submit me-sm-4 me-1 mt-5"
                    wire:click="exportExcel"
                    wire:loading.attr="disabled"
                    wire:target="exportExcel">
                <span wire:loading.remove wire:target="exportExcel">
                    <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Export') }}
                </span>
                <span wire:loading wire:target="exportExcel">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Generando reporte...') }}
                </span>
            </button>
            <!-- Spinner de carga -->
            <div wire:loading>
                Generando reporte... ⏳
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@script()
<script>
  (function() {
    // Función para inicializar Select2
    const initializeSelect2 = () => {
      const selects = [
        'filter_type',
        'filter_currency',
        'filter_status',
        'filter_tax_type'
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
              const specificIds = ['filter_department','filter_status']; // Lista de IDs específicos

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



      $('#filter_emisor').select2({
        placeholder: $('#filter_emisor').data('placeholder'),
        minimumInputLength: 2,
        ajax: {
          url: '/api/emisor/search',
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
      $('#filter_emisor').on('change', function () {
        const val = $(this).val();
        if (typeof $wire !== 'undefined') {
          $wire.set('filter_emisor', val);
        }
      });

    };

    // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
    Livewire.on('reinitFormControls', () => {
      console.log('Reinicializando controles después de Livewire update reinitFormControls');
      setTimeout(() => {
        initializeSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });

  })();
</script>
@endscript
