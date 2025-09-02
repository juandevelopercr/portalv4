@php
  use App\Models\Transaction;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Session;
  use App\Models\User;
@endphp
<div>
  @if($action == 'list')
    <!-- DataTable with Buttons -->
    <div class="card">
      <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Electronicinvoices') }}</h4>
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
                  <!-- Dropdown with icon -->
                  @can("create-electronicinvoices")
                    @include('livewire.includes.button-create')
                  @endcan

                  @can("edit-electronicinvoices")
                    @include('livewire.includes.button-edit')
                  @endcan

                  @can("anular-electronicinvoices")
                    @include('livewire.includes.button-opciones')
                  @endcan

                  @can("anular-electronicinvoices")
                    @include('livewire.includes.button-opciones')
                  @endcan

                  @can("delete-electronicinvoices")
                    @include('livewire.includes.button-delete', ['textButton' => __('Eliminar')])
                  @endcan



                  <div x-data="{ action: @entangle('action') }">
                    <div x-show="action === 'list'" x-cloak>
                      @can("export-electronicinvoices")
                        <livewire:transactions.transaction-datatable-export />
                      @endcan
                    </div>
                  </div>

                  <!-- Dropdown with icon -->
                  <div class="btn-group" role="group" aria-label="DataTable Actions">
                    <!-- Botón para Reiniciar Filtros -->
                    <button type="button" class="btn btn-secondary btn-sm mx-1" wire:click="resetFilters"
                      wire:loading.attr="disabled" wire:target="resetFilters">
                      <span wire:loading.remove wire:target="resetFilters">
                        <i class="bx bx-reset bx-flip-horizontal"></i>
                        <span class="d-none d-sm-inline-block"></span>
                      </span>
                      <span wire:loading wire:target="resetFilters">
                        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                        {{ __('Loading...') }}
                      </span>
                    </button>

                    <!-- Botón para Configurar Columnas -->
                    <button type="button" class="btn btn-secondary btn-sm mx-1" data-bs-toggle="modal"
                      data-bs-target="#datatableSettingsModal">
                      <i class="tf-icons bx bxs-wrench"></i>
                    </button>
                  </div>

                  <!-- Renderizar el componente Livewire -->
                  @livewire('components.datatable-settings', [
                    'datatableName' => 'invoice-datatable',
                    'availableColumns' => $this->columns,
                    'perPage' => $this->perPage,
                  ],
                  key('invoice-datatable-config'))

                </div>
              </div>
            </div>
          </div>

          @can("view-electronicinvoices")
            <div class="card-datatable table-responsive">
              <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="transaction-table" style="width: 100%;">
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

                    @php
                    /*
                    <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 120px;" aria-label="Actions">{{
                      __('Actions') }}
                    </th>
                    */
                    @endphp
                  </tr>
                  <!-- Fila de filtros -->
                  <tr>
                    @include('livewire.includes.filters', ['columns' => $this->columns])
                  </tr>
                </thead>
                <tbody>
                  @php
                  $tComprobante = 0;
                  $tComprobanteUsd = 0;
                  $tComprobanteCrc = 0;
                  $allowedRoles = User::ROLES_ALL_DEPARTMENTS;
                  @endphp

                  @foreach ($records as $record)
                  @php
                  $totalComprobante = $record->totalComprobante;
                  $totalComprobanteUsd = $record->getTotalComprobante('USD');
                  $totalComprobanteCrc = $record->getTotalComprobante('CRC');

                  $tComprobante += $totalComprobante;
                  $tComprobanteUsd += $totalComprobanteUsd;
                  $tComprobanteCrc += $totalComprobanteCrc;
                  @endphp

                  <tr wire:key='{{ $record->id }}' class="{{ $loop->odd ? 'odd' : 'even' }}">
                    <td class="control" style="display: none;" tabindex="0"></td>
                    <td class="dt-checkboxes-cell">
                      <input type="checkbox" class="form-check-input" wire:model.live="selectedIds"
                        value="{{ $record->id }}">
                    </td>

                    @include('livewire.includes.columns', [
                        'columns' => $this->columns,
                        'record' => $record,
                        'canedit' => auth()->user()->can('edit-electronicinvoices')
                    ])

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
                      <td>
                        <strong>{{ Helper::formatDecimal($value) }}</strong>
                      </td>
                      @endif
                    @endforeach
                  </tr>
                </tfoot>
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

  <livewire:modals.caby-modal />

  @livewire('transactions.send-email-modal', [
            'documentType' => 'FE',
            'canview'   => auth()->user()->can('view-documento-electronicinvoices'),
            'cancreate' => auth()->user()->can('create-documento-electronicinvoices'),
            'canedit'   => auth()->user()->can('edit-documento-electronicinvoices'),
            'candelete' => auth()->user()->can('delete-documento-electronicinvoices'),
            'canexport' => auth()->user()->can('export-documento-electronicinvoices'),
          ], key('transaction-send-email'))

  <div x-data="{ action: @entangle('action') }">
    <div x-show="action === 'create' || action === 'edit'" x-cloak>
      @include('livewire.transactions.partials.form_invoice')
    </div>
  </div>

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
                if (!res.ok) throw new Error('Respuesta inválida');
                return res.json();
              })
              .then(response => {
                const downloadUrl = `${downloadBase}/${response.filename}`;
                window.location.assign(downloadUrl);
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
