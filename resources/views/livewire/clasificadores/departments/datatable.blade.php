<div>
  @if($action == 'list')
    <!-- DataTable with Buttons -->
    <div class="card">
      <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Departments') }}</h4>
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
                  @can("create-classifiers")
                    @include('livewire.includes.button-create')
                  @endcan

                  @can("edit-classifiers")
                    @include('livewire.includes.button-edit')
                  @endcan

                  @can("delete-classifiers")
                    @include('livewire.includes.button-delete', ['textButton'=>null])
                  @endcan

                  @can('export-classifiers')
                    <livewire:clasificadores.departments.department-datatable-export />
                  @endcan

                  <div class="btn-group" role="group" aria-label="DataTable Actions">
                      <div class="btn-group" role="group" aria-label="DataTable Actions">
                        <!-- Botón para Reiniciar Filtros -->
                          @include('livewire.includes.button-reset-filters')

                          <!-- Botón para Configurar Columnas -->
                          @include('livewire.includes.button-config-columns')
                      </div>
                  </div>

                  <!-- Renderizar el componente Livewire -->
                  @livewire('components.datatable-settings', [
                    'datatableName' => 'classifier-departments-datatable',
                    'availableColumns' => $this->columns,
                    'perPage' => $this->perPage,
                  ],
                  key('classifier-departments-datatable-config'))

                </div>
              </div>
            </div>
          </div>

          @can("view-classifiers")
            <div class="card-datatable table-responsive">
                <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="departments-table" style="width: 100%;">
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

                    <!-- Fila de filtros -->
                    <tr>
                      @include('livewire.includes.filters', ['columns' => $this->columns])
                    </tr>
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

                    @include('livewire.includes.columns', ['columns' => $this->columns, 'record'=>$record, 'canedit'=>auth()->user()->can('edit-classifiers')])

                  </tr>
                  @endforeach
                </tbody>
              </table>

              <div class="row overflow-y-scroll" wire:scroll>
                {{ $records->links(data: ['scrollTo' => false]) }}
              </div>
            </div>
          @endcan
        </div>
        <div style="width: 1%;"></div>
      </div>
    </div>
  @endif

  @if($action == 'create' || $action == 'edit')
    @include('livewire.clasificadores.departments.partials.form')
  @endif
</div>

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('departments-table').innerHTML;
      const originalContent = document.body.innerHTML;

      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
  }
</script>
@endpush
