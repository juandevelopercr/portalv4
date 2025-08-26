<div>
  @if($action == 'list')
    <div class="card">
      <div class="card-body">
        <h5>{{ __('Filtros') }}</h5>

        <!-- FILA 1: Fecha | Botón | Saldo -->
        <div class="row align-items-end mb-4">
          <!-- Filtro de fecha -->
          <div class="col-md-4">
            <label class="form-label" for="filterFecha">{{ __('Fecha') }}</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                <input
                  type="text"
                  wire:model="filterFecha"
                  class="form-control range-picker"
                  id="filterFecha"
                  x-data="rangePickerLivewire({ wireEventName: 'dateRangeSelected' })"
                  x-init="init($el)"
                  wire:ignore>
            </div>
            @error('filterFecha')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <!-- Botón reporte -->
          <div class="col-md-3 text-start">
            @php
            /*
            <button wire:click="exportMovimientos" class="btn btn-primary mt-3">
              <i class="bx bx-download me-1"></i> Generar Reporte
            </button>
            */
            @endphp
          </div>

          <!-- Texto saldo -->
          <div class="col-md-5 text-end">
            <div class="fw-bold mt-3">
              Saldo final:
              <span>₡ {{ $saldo_final_crc }}</span><br>
              <span>$ {{ $saldo_final_usd }}</span>
            </div>
          </div>
        </div>

        <!-- FILA 2: Filtro de cuentas (a todo ancho) -->
        <div class="row">
          <div class="col-md-12 fv-plugins-icon-container"
              x-data="select2LivewireMultipleWithToggle({
                wireModelName: 'filterCuentas',
                postUpdate: true
              })"
              x-init="init($refs.select)"
              wire:ignore>
            <label class="form-label" for="filterCuentas">{{ __('Cuenta') }}</label>
            <select x-ref="select" id="filterCuentas"
                    class="form-select"
                    multiple>
              @foreach ($this->cuentas as $cuenta)
                <option value="{{ $cuenta->id }}">{{ $cuenta->nombre_cuenta }}</option>
              @endforeach
            </select>
            @error('filterCuentas')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <br>

    <!-- DataTable with Buttons -->
    <div class="card">
      <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Revisiones') }}</h4>
      <div class="card-datatable text-nowrap">
        <div class="dataTables_wrapper dt-bootstrap5 no-footer">
          <div class="row">
            <div class="col-md-2">
              <div class="ms-n2">
                @include('livewire.includes.table-paginate')
              </div>
            </div>
            <div class="col-md-10">
              <div
                class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0">
                <div class="dt-buttons btn-group flex-wrap">
                  <!-- Dropdown with icon -->
                  @can("create-movimientos")
                    @include('livewire.includes.button-create')
                  @endcan

                  @can("edit-movimientos")
                    @include('livewire.includes.button-edit')
                  @endcan

                  @can("clonar-movimientos")
                    @include('livewire.includes.button-clonar')
                  @endcan

                  @can("delete-movimientos")
                    @include('livewire.includes.button-anular', ['textButton' => __('Anular')])
                  @endcan

                  @can("export-movimientos")
                    <livewire:movimientos.movimiento-datatable-export
                        :search="$search"
                        :filters="$filters"
                        :selected-ids="$selectedIds"
                        :default-status="$defaultStatus"
                    />
                  @endcan

                  <div class="btn-group" role="group" aria-label="DataTable Actions">
                    <!-- Botón para Reiniciar Filtros -->
                      @include('livewire.includes.button-reset-filters')

                      <!-- Botón para Configurar Columnas -->
                      @include('livewire.includes.button-config-columns')
                  </div>

                  <!-- Renderizar el componente Livewire -->
                  @livewire('components.datatable-settings', [
                  'datatableName' => 'movimientos-datatable',
                  'availableColumns' => $this->columns,
                  'perPage' => $this->perPage,
                  ],
                  key('movimientos-datatable-config'))
                </div>
              </div>


              <div
                class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-15 mb-md-0 mt-n6 mt-md-5">
                <div class="dt-buttons btn-group flex-wrap">
                  <!-- Dropdown with icon -->
                  @can("edit-movimientos")
                    @include('livewire.includes.button-listo-aprobar')
                  @endcan

                  @can("edit-movimientos")
                    @include('livewire.includes.button-rechazar', ['textButton' => __('Rechazar')])
                  @endcan

                  @can("edit-movimientos")
                    @include('livewire.includes.button-aprobar', ['textButton' => __('Enviar aprobaciones / rechazos')])
                  @endcan

                  @can("edit-movimientos")
                    @include('livewire.includes.button-enviar-revision', ['textButton' => __('Enviar a revisión')])
                  @endcan

                </div>
              </div>
              <br>


            </div>
          </div>

          @can("view-movimientos")
            <div class="card-datatable table-responsive">
              <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="movimientos-table" style="width: 100%;">
                <thead>
                  <tr>
                    <th class="control sorting_disabled dtr-hidden" rowspan="1" colspan="1"
                      style="width: 0px; display: none;" aria-label="">
                    </th>
                    <th class="sorting_disabled dt-checkboxes-cell dt-checkboxes-select-all" rowspan="1" colspan="1"
                      style="width: 18px;" data-col="1" aria-label="">
                      <input type="checkbox" class="form-check-input" id="select-all" wire:click="toggleSelectAll">
                    </th>

                    @include('livewire.includes.headers', ['columns' => $this->columns])

                  </tr>
                  <!-- Fila de filtros -->
                  <tr>
                    @include('livewire.includes.filters', ['columns' => $this->columns])
                  </tr>
                </thead>
                <tbody>
                  @foreach ($records as $record)
                  <tr
                      wire:key='{{ $record->id }}'
                      @class([
                          'odd' => $loop->odd,
                          'even' => $loop->even,
                          'table-danger' => $record->bloqueo_fondos == 1,
                          'table-success' => $record->bloqueo_fondos != 1 && $record->tipo_movimiento === 'DEPOSITO',
                      ])
                    >
                    <td class="control" style="display: none;" tabindex="0"></td>
                    <td class="dt-checkboxes-cell">
                      <input type="checkbox" class="form-check-input" wire:model.live="selectedIds"
                        value="{{ $record->id }}">
                    </td>

                    @include('livewire.includes.columns', ['columns' => $this->columns, 'record'=>$record, 'canedit'=>auth()->user()->can('edit-services')])

                    @php
                      /*
                    <td>
                      <div class="action-icons d-flex justify-content-center align-items-center">
                          @can("edit-movimiento")
                              <button wire:click="edit({{ $record->id }})"
                                wire:loading.attr="disabled" wire:target="edit({{ $record->id }})"
                                class="btn btn-icon item-edit" data-bs-toggle="tooltip" data-bs-offset="0,8"
                                data-bs-placement="top" data-bs-custom-class="tooltip-dark"
                                data-bs-original-title="{{ __('Edit') }}">

                                <!-- Ícono normal (visible cuando no está en loading) -->
                                <span wire:loading.remove wire:target="edit({{ $record->id }})">
                                    <i class="bx bx-edit bx-md"></i>
                                </span>

                                <!-- Ícono de carga (visible cuando está en loading) -->
                                <span wire:loading wire:target="edit({{ $record->id }})">
                                    <i class="spinner-border spinner-border-sm me-1" role="status"></i>
                                </span>
                              </button>
                          @endcan

                          @can("edit-movimiento")
                            <button wire:click.prevent="confirmarAccion({{ $record->id }}, 'clonar',
                                                          '{{ __('You are sure you want to clone service with code:') }}  {{ $record->code }}?',
                                                          '{{ __('After confirmation, the service will be cloned') }}',
                                                          '{{ __('Yes, proceed') }}')"
                              class="btn btn-icon item-trash text-warning" data-bs-toggle="tooltip" data-bs-offset="0,8"
                              data-bs-placement="top" data-bs-custom-class="tooltip-dark"
                              data-bs-original-title="{{ __('Clonar Servicio') }}">
                              <i class="bx bx-copy bx-md"></i>
                            </button>
                          @endcan

                          @can("delete-movimiento")
                            <button wire:click.prevent="confirmarAccion(
                                  {{ $record->id }},
                                  'delete',
                                  '{{ __('Are you sure you want to delete this record') }} ?',
                                  '{{ __('After confirmation, the record will be deleted') }}',
                                  '{{ __('Yes, proceed') }}'
                                )"
                                class="btn btn-icon item-trash text-danger"
                                data-bs-toggle="tooltip"
                                data-bs-offset="0,8"
                                data-bs-placement="top"
                                data-bs-custom-class="tooltip-dark"
                                data-bs-original-title="{{ __('Delete') }}"
                              >
                              <i class="bx bx-trash bx-md"></i>
                            </button>
                          @endcan
                      </div>
                    </td>
                    */
                    @endphp
                  </tr>

                  @php
                    $expandColumn = collect($columns)->firstWhere('columnType', 'expand');
                  @endphp

                  @if ($expandColumn && in_array($record->id, $expandedRows ?? []))
                    <tr class="table-row-expanded">
                      <td colspan="{{ count($columns) }}">
                        @includeIf($expandColumn['expand_view'], ['record' => $record])
                      </td>
                    </tr>
                  @endif
                  @php
                  /*
                  @if (in_array($record->id, $expandedRows ?? []))
                    <tr class="table-row-expanded">
                      <td colspan="{{ count($columns) }}">
                        {{-- Aquí tu subtabla o detalles personalizados --}}
                        <table class="table table-sm table-bordered">
                          <thead>
                            <tr><th>Campo</th><th>Valor</th></tr>
                          </thead>
                          <tbody>
                            <tr><td>ID Movimiento</td><td>{{ $record->id }}</td></tr>
                            <tr><td>Detalle</td><td>{{ $record->descripcion }}</td></tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  @endif
                  */
                  @endphp


                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="row overflow-y-scroll" wire:scroll>
              {{ $records->links(data: ['scrollTo' => false]) }}
            </div>
          @endcan
        </div>
      </div>


      <!-- Siempre montamos sumary, pero lo ocultamos si no se muestra -->
      @if($action === 'list')
        <div class="row mt-0 justify-content-end">
          <div class="col-md-4">
            @livewire('movimientos.sumary', [
              'date'=> $this->filterFecha,
              'cuentas'=> $this->filterCuentas,
              'status' => 'REGISTRADO'
            ])
          </div>
        </div>
      @endif


    </div>
  @endif

  @if($action == 'create' || $action == 'edit')
    @include('livewire.movimientos.partials.form')
  @endif
</div>

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('movimientos-table').innerHTML;
      const originalContent = document.body.innerHTML;

      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
  }
</script>
@endpush


@script()
<script>
  (function () {

        // Función para sincronizar con Livewire usando evento personalizado
        function enviarEventoACuentaActualizada() {
          const cuentasSeleccionadas = $select.val();
          Livewire.dispatch('cuentasActualizadas', { cuentas: cuentasSeleccionadas });
        }

        // Función para inicializar Select2
        const initializeSelect2 = () => {
            const selects = [
                'filter_currency',
                'filter_type',
                'filter_status',
                'filter_bloqueo_fondos',
                'filter_clonando',
                'filter_comprobante_pendiente'
            ];

            selects.forEach((id) => {
                const element = document.getElementById(id);
                if (element) {
                    // Verifica si Select2 ya está inicializado
                    if (!$(element).data('select2')) {
                        console.log(`Inicializando Select2 para: ${id}`);
                        $(`#${id}`).select2();

                        $(`#${id}`).on('change', function () {
                            const newValue = $(this).val();
                            const livewireValue = @this.get(`filters.${id}`); // Cambiar acceso a filters

                            if (newValue !== livewireValue) {
                                @this.set(`filters.${id}`, newValue); // Actualizar filters correctamente
                            }
                        });
                    }
                }

                // Sincroniza el valor actual desde Livewire al Select2
                const currentValue = @this.get(`filters.${id}`); // Cambiar acceso a filters
                $(`#${id}`).val(currentValue).trigger('change');
            });

            const $select = $('#filterCuentas');

            $select.select2({
              placeholder: 'Selecciona cuentas',
              width: '100%',
              closeOnSelect: false // lo controlamos manualmente
            });

            // Mostrar botón "Seleccionar todas / Eliminar todas" al abrir
            $select.on('select2:open', function () {
              $('.select2-custom-option').remove(); // Evita duplicados

              const totalOptions = $select.find('option').length;
              const selectedOptions = $select.val() ? $select.val().length : 0;

              let label = 'Seleccionar todas';
              let icon = '<span class="me-2"><i class="bx bx-check-double text-success"></i></span>';
              let isClear = false;

              if (selectedOptions === totalOptions) {
                label = 'Eliminar todas';
                icon = '<span class="me-2"><i class="bx bx-x text-danger"></i></span>';
                isClear = true;
              }

              const $btn = $(`
                <div class="select2-custom-option d-flex align-items-center"
                    style="padding: 6px 12px; cursor: pointer; font-weight: 500; border-bottom: 1px solid #ddd;">
                  ${icon}${label}
                </div>
              `);

              $('.select2-results').prepend($btn);

              $btn.on('click', function () {
                if (isClear) {
                  $select.val(null).trigger('change');
                } else {
                  const allValues = $select.find('option').map(function () {
                    return this.value;
                  }).get();
                  $select.val(allValues).trigger('change');
                }

                // Sincroniza con Livewire
                document.getElementById('filterCuentas').dispatchEvent(new Event('input', { bubbles: true }));

                // Cierra el dropdown
                $select.select2('close');
              });
            });

            // Detecta selección individual
            $select.on('select2:select', function () {
              const componentId = document.getElementById('filterCuentas').closest('[wire\\:id]').getAttribute('wire:id');
              const selectedValues = $select.val();
              Livewire.find(componentId).set('filterCuentas', selectedValues);

              Livewire.dispatch('cuentasActualizadas', { cuentas: selectedValues });

              $select.select2('close');
            });

            $select.on('change', function () {
              const componentId = document.getElementById('filterCuentas').closest('[wire\\:id]').getAttribute('wire:id');
              const selectedValues = $select.val();
              Livewire.find(componentId).set('filterCuentas', selectedValues);

              Livewire.dispatch('cuentasActualizadas', { cuentas: selectedValues });
            });

        };

        const initializeRangePicker = () => {
            document.querySelectorAll('.range-picker').forEach((rangePicker) => {
                if (typeof rangePicker !== 'undefined' && !rangePicker.flatpickrInstance) {
                    // Asegurarnos de que no esté inicializado previamente
                    rangePicker.flatpickrInstance = rangePicker.flatpickr({
                        mode: 'range',
                        allowInput: false,
                        dateFormat: 'd-m-Y', // Formato de fecha para garantizar uniformidad
                        onClose: function (selectedDates, dateStr) {
                            // Asegurarnos de manejar un rango válido
                            if (selectedDates.length === 2) {
                                const rangePickerId = rangePicker.getAttribute('id');
                                console.log('dateRangeSelected', { id: rangePickerId, range: dateStr });
                                Livewire.dispatch('dateRangeSelected', { id: rangePickerId, range: dateStr });
                            }
                        },
                    });

                    // Captura también cuando se borra el input manualmente
                    rangePicker.addEventListener('input', function () {
                        const rangePickerId = rangePicker.getAttribute('id');

                        if (rangePicker.value === '') {
                            Livewire.dispatch('dateRangeSelected', {
                                id: rangePickerId,
                                range: ''
                            });
                        }
                    });
                }
            });
        };

        Livewire.on('exportReady', (url) => {
          // Mostrar spinner con un pequeño delay para permitir render
          Livewire.dispatch('showLoading', [{ message: 'Generando reporte. Por favor espere...' }]);

          setTimeout(() => {
            fetch(url)
              .then(res => {
                if (!res.ok) throw new Error('Respuesta inválida');
                return res.json();
              })
              .then(data => {
                // Iniciar descarga
                window.location.assign(`/descargar-exportacion-movimientos/${data.filename}`);
                Livewire.dispatch('hideLoading');
              })
              .catch(err => {
                console.error(err);
                Livewire.dispatch('hideLoading');
                //alert('Error al generar el archivo');
              });
          }, 100); // ✅ Este pequeño delay permite que el DOM pinte el spinner
        });

        // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
        Livewire.on('reinitMovimientoControls', () => {
            console.log('Reinicializando controles después de Livewire update reinitMovimientoControls');
            setTimeout(() => {
                initializeSelect2();
                initializeRangePicker();
            }, 200); // Retraso para permitir que el DOM se estabilice
        });

        Livewire.on('actualizarSumary', () => {
            console.log('Se ejecuta actualizarSumary');
            setTimeout(() => {
                Livewire.dispatch('hideLoading');
            }, 200); // Retraso para permitir que el DOM se estabilice
        });

        Livewire.on('trigger-print-cheque', (html) => {
            printCheque(html);
        });

        function printCheque(content) {
            const printWindow = window.open('', '', 'height=800,width=1000');
            printWindow.document.write('<html><head><title>Imprimir Cheque</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
            printWindow.document.write('</head><body class="kv-wrap">');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();

            printWindow.onload = function () {
                printWindow.print();
                printWindow.close();
            };
        }
    })();

</script>
@endscript
