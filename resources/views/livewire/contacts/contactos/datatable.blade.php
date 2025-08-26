<div>
  <!-- DataTable with Buttons -->
  @if($action == 'list')

  <!-- DataTable with Buttons -->
  <div class="card">
    <div class="card-datatable table-responsive">
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
                  <!--/ Dropdown with icon -->
                  @can("create-classifiers")
                    @include('livewire.includes.button-create')
                  @endcan

                  @can("edit-classifiers")
                    @include('livewire.includes.button-edit')
                  @endcan

                  @can("delete-classifiers")
                    @include('livewire.includes.button-delete', ['textButton'=>null])
                  @endcan

                  <livewire:contacts.contactos.contacto-datatable-export />

                  <div class="btn-group" role="group" aria-label="DataTable Actions">
                    <!-- Botón para Reiniciar Filtros -->
                      @include('livewire.includes.button-reset-filters')

                      <!-- Botón para Configurar Columnas -->
                      {{-- @include('livewire.includes.button-config-columns') --}}
                  </div>
                  {{--
                  <!-- Renderizar el componente Livewire -->
                  @livewire('components.datatable-settings', [
                  'datatableName' => 'product-honorarios-timbres-datatable',
                  'availableColumns' => $this->columns,
                  ],
                  key('product-honorarios-timbres-datatable-config'))
                  --}}

                </div>
              </div>
            </div>
          </div>

          <div class="card-datatable table-responsive">
            <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="contactos-table" style="width: 100%;">
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
                <tr wire:key='{{ $record->id }}' class="{{ $loop->odd ? 'odd' : 'even' }}">
                  <td class="control" style="display: none;" tabindex="0"></td>
                  <td class="dt-checkboxes-cell">
                    <input type="checkbox" class="form-check-input" wire:model.live="selectedIds"
                      value="{{ $record->id }}">
                  </td>

                  @include('livewire.includes.columns', ['columns' => $this->columns, 'record'=>$record, 'canedit'=>auth()->user()->can('edit-services')])

                </tr>
                @endforeach
              </tbody>
            </table>

            <div class="row overflow-y-scroll" wire:scroll>
              {{ $records->links(data: ['scrollTo' => false]) }}
            </div>
          </div>
        </div>
        <div style="width: 1%;"></div>
      </div>
    </div>
  </div>
</div>
<!--/ DataTable with Buttons -->
@endif

@if($action == 'create' || $action == 'edit')
@include('livewire.contacts.contactos.partials.form')
@endif

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('contactos-table').innerHTML;
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
        // Función para inicializar Select2
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
                }
            });
        };

        // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
        Livewire.on('reinitContactContactSelec2Form', () => {
            console.log('Reinicializando controles después de Livewire update reinitContactContactSelec2Form');
            setTimeout(() => {
                initializeSelect2Listado();
                initializeRangePicker();
            }, 200); // Retraso para permitir que el DOM se estabilice
        });

        // Para el formulario wire:
        const initializeSelect2Form = () => {
            const selects = [
                'grupo_empresarial_id',
                'tipo_cliente',
                'clasificacion',
                'areasPracticas',
                'sectoresIndustriales'
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
                            const livewireValue = @this.get(`${id}`); // Cambiar acceso a filters

                            if (newValue !== livewireValue) {
                                @this.set(`${id}`, newValue); // Actualizar filters correctamente
                            }
                        });
                    }
                }

                // Sincroniza el valor actual desde Livewire al Select2
                const currentValue = @this.get(`${id}`); // Cambiar acceso a filters
                $(`#${id}`).val(currentValue).trigger('change');
            });
        };


        // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
        Livewire.on('reinitContactContactSelec2Form', () => {
            console.log('Reinicializando controles select2 del formulario');
            setTimeout(() => {
                initializeSelect2Form();
            }, 200); // Retraso para permitir que el DOM se estabilice
        });
    })();
</script>
@endscript
