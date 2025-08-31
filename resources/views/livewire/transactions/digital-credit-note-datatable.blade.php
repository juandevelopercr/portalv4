@php
  use App\Models\Transaction;
  use App\Models\User;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Session;
@endphp
<div>
  @if($action == 'list')

    <!-- DataTable with Buttons -->
    <div class="card">
      <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Notas de crédito digital') }}</h4>
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
                  <div x-data="{ action: @entangle('action') }">
                    <div x-show="action === 'list'" x-cloak>
                      @can("export-proformas")
                        <livewire:transactions.transaction-datatable-export />
                      @endcan
                    </div>
                  </div>

                  <!-- Dropdown with icon -->
                  <div class="btn-group" role="group" aria-label="DataTable Actions">
                    <!-- Botón para Reiniciar Filtros -->
                      @include('livewire.includes.button-reset-filters')

                      <!-- Botón para Configurar Columnas -->
                      @include('livewire.includes.button-config-columns')
                  </div>

                  <!-- Renderizar el componente Livewire -->
                  @livewire('components.datatable-settings', [
                    'datatableName' => 'nota-credito-digital-datatable',
                    'availableColumns' => $this->columns,
                    'perPage' => $this->perPage,
                  ],
                  key('nota-credito-digital-datatable-config'))

                </div>
              </div>
            </div>
          </div>

          @can("view-proformas")
            <div class="card-datatable table-responsive">
              <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="nota-credito-digital-table" style="width: 100%;">
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
                        'canedit' => auth()->user()->can('edit-proformas') &&
                            ($record->proforma_status == Transaction::PROCESO ||
                            ($record->proforma_status == Transaction::SOLICITADA &&
                            in_array(Session::get('current_role_name'), $allowedRoles)))
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
            'documentType' => 'PROFORMA',
            'canview'   => auth()->user()->can('view-documento-proformas'),
            'cancreate' => auth()->user()->can('create-documento-proformas'),
            'canedit'   => auth()->user()->can('edit-documento-proformas'),
            'candelete' => auth()->user()->can('delete-documento-proformas'),
            'canexport' => auth()->user()->can('export-documento-proformas'),
          ], key('transaction-send-email'))


  @php
  /*
  @if($action == 'create' || $action == 'edit')
    @include('livewire.transactions.partials.form_proforma')
  @endif
  */
  @endphp

  <div x-data="{ action: @entangle('action') }">
    <div x-show="action === 'create' || action === 'edit'" x-cloak>
      @include('livewire.transactions.partials.form_proforma')
    </div>
  </div>

</div>

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('nota-credito-digital-table').innerHTML;
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
