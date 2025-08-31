<div>
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
                  <div class="dt-buttons btn-sm btn-group flex-wrap mt-5">
                    <!--/ Dropdown with icon -->
                    @if($cancreate)
                      @include('livewire.includes.button-create')
                    @endif

                    @if($canedit)
                      @include('livewire.includes.button-edit')
                    @endif

                    @if($candelete)
                      @include('livewire.includes.button-delete', ['textButton' => __('Eliminar')])
                    @endif

                    @if($canexport)
                      <livewire:transactions-lines.transaction-line-datatable-export />
                    @endcan

                    <div class="btn-group" role="group" aria-label="DataTable Actions">
                      <!-- Botón para Reiniciar Filtros -->
                        @include('livewire.includes.button-reset-filters')

                        <!-- Botón para Configurar Columnas -->
                        {{-- @include('livewire.includes.button-config-columns') --}}
                    </div>
                  </div>

                  <!-- Renderizar el componente Livewire -->
                  {{--
                  @livewire('components.datatable-settings', [
                  'datatableName' => 'proformas-lines-datatable',
                  'availableColumns' => $this->columns,
                  'perPage' => $this->perPage,
                  ],
                  key('proformas-lines-datatable-config'))
                  --}}
                </div>
              </div>
            </div>

            @if($canview)
              <div class="card-datatable table-responsive">
                <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="transaction-line-table" style="width: 100%;">
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

                      @include('livewire.includes.columns', ['columns' => $this->columns, 'record'=>$record, 'canedit'=>$canedit])

                      @php
                      /*
                      <td>
                        <div class="action-icons d-flex justify-content-center align-items-center">
                          @if($canedit)
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
                          @endif

                          @if($candelete)
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
                          @endif
                        </div>
                      </td>
                      */
                      @endphp
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="100%" style="position: relative; padding: 0;">
                        <div
                          style="display: flex; justify-content: flex-end; align-items: center; width: 100%; padding: 10px;">
                        </div>
                      </td>
                    </tr>
                  </tfoot>
                </table>
                <div class="row overflow-y-scroll" wire:scroll>
                  {{ $records->links(data: ['scrollTo' => false]) }}
                </div>
              </div>
            @endif
          </div>
          <div style="width: 1%;"></div>
        </div>
      </div>
    </div>
    <!--/ DataTable with Buttons -->
  @endif

  @if ($action == 'create' || $action == 'edit')
    @include('livewire.transactions-lines.partials.form')
  @endif

  @php
    /*
  <div x-data="{ action: @entangle('action') }">
    <div x-show="action === 'create' || action === 'edit'" x-cloak>
        @include('livewire.transactions-lines.partials.form')
    </div>
  </div>
  */
  @endphp

  @livewire('transactions.transaction-totals', ['transaction_id' => $this->transaction_id], key('transaction-totals-' . $this->transaction_id))

</div>

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('transaction-line-table').innerHTML;
      const originalContent = document.body.innerHTML;

      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
  }
</script>
@endpush
