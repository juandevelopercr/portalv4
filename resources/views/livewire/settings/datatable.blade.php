<div>
  @if($action == 'list')
    <!-- DataTable with Buttons -->
    <div class="card">
      <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Issuers') }}</h4>
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
                <div class="dt-buttons btn-sm btn-group flex-wrap mt-5">
                  @php
                  /*
                  @can("create-settings")
                    @include('livewire.includes.button-create')
                  @endcan
                  */
                  @endphp

                  @can("edit-settings")
                    @include('livewire.includes.button-edit')
                  @endcan

                  @php
                  /*
                  @can("delete-settings")
                    @include('livewire.includes.button-delete', ['textButton' => __('Eliminar')])
                  @endcan
                  */
                  @endphp
                  @php
                  /*
                  <livewire:settings.setting-datatable-export />
                  */
                  @endphp
                  <!-- Dropdown with icon -->

                  <div class="btn-group" role="group" aria-label="DataTable Actions">
                    <!-- Botón para Reiniciar Filtros -->
                      @include('livewire.includes.button-reset-filters')

                      <!-- Botón para Configurar Columnas -->
                      {{-- @include('livewire.includes.button-config-columns') --}}
                  </div>

                  {{--
                  <!-- Renderizar el componente Livewire -->
                  @livewire('components.datatable-settings', [
                  'datatableName' => 'business-location-datatable',
                  'availableColumns' => $this->columns,
                  ],
                  key('business-location-datatable-config'))
                   --}}

                </div>
              </div>
            </div>
          </div>

          <div class="card-datatable table-responsive">
            <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="business-location-table" style="width: 100%;">
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
                @php
                //  dd($this->columns);
                @endphp
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

                  @include('livewire.includes.columns', ['columns' => $this->columns, 'record'=>$record, 'canedit'=>auth()->user()->can('edit-settings')])

                  @php
                    /*
                  <td>
                    <div class="action-icons d-flex justify-content-center align-items-center">
                      <!-- Botón para Editar con Loading -->
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

                    </div>
                  </td>
                  */
                  @endphp
                </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <td></td>
                  @foreach ($this->columns as $index => $column)
                    @if ($column['visible'])
                      @php
                        $value = !empty($column['sumary']) ? (${$column['sumary']} ?? '') : '';
                      @endphp
                      <td><strong>{{ Helper::formatDecimal($value) }}</strong></td>
                    @endif
                  @endforeach
                </tr>
              </tfoot>
            </table>

            <div class="row overflow-y-scroll" wire:scroll>
              {{ $records->links(data: ['scrollTo' => false]) }}
            </div>

          </div>
        </div>
        <div style="width: 1%;"></div>
      </div>
    </div>
  @endif

  @if($action == 'create' || $action == 'edit')
    @include('livewire.settings.partials._form-business-location')
  @endif
</div>

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('transaction-table').innerHTML;
      const originalContent = document.body.innerHTML;

      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
  }
</script>
@endpush
