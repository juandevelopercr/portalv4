@php
  use App\Models\Transaction;
  use App\Models\User;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Session;
@endphp

<div wire:key="proforma-main-container">
<div x-data="{ action: @entangle('action') }">
    <div :class="{ 'd-none': action !== 'list' }" wire:key="proforma-list-container">
        <div class="card mb-6">
          <div class="card-widget-separator-wrapper">
            <div class="card-body card-widget-separator">
              <div class="row gy-4 gy-sm-1">
                <div class="col-sm-6 col-lg-3">
                  <div class="d-flex justify-content-between align-items-center card-widget-1 border-end pb-4 pb-sm-0">
                    <div>
                      <h4 class="mb-0">{{ (int)$this->totalProceso }}</h4>
                      <p class="mb-0">{{ __("En Proceso") }}</p>
                    </div>
                    <div class="avatar me-sm-6">
                      <span class="avatar-initial rounded bg-label-secondary text-heading">
                        <i class="bx bx-bar-chart-alt bx-26px"></i>
                      </span>
                    </div>
                  </div>
                  <hr class="d-none d-sm-block d-lg-none me-6">
                </div>
                <div class="col-sm-6 col-lg-3">
                  <div class="d-flex justify-content-between align-items-center card-widget-2 border-end pb-4 pb-sm-0">
                    <div>
                      <h4 class="mb-0">{{ (int)$this->totalPorAprobar }}</h4>
                      <p class="mb-0">{{ __("Por Aprobar") }}
                      </p>
                    </div>
                    <div class="avatar me-lg-6">
                      <span class="avatar-initial rounded bg-label-secondary text-heading">
                        <i class="bx bx-time bx-26px"></i>
                      </span>
                    </div>
                  </div>
                  <hr class="d-none d-sm-block d-lg-none">
                </div>
                <div class="col-sm-6 col-lg-3">
                  <div class="d-flex justify-content-between align-items-center border-end pb-4 pb-sm-0 card-widget-3">
                    <div>
                      <h6 class="mb-0">
                        $ {{ Helper::formatDecimal($this->totalUsdHonorario) }} Honorarios<br>
                        $ {{ Helper::formatDecimal($this->totalUsdGasto) }} Gastos
                      </h6>
                      <p class="mb-0">{{ __('Total') }} Dólares</p>
                    </div>
                    <div class="avatar me-sm-6">
                      <span class="avatar-initial rounded bg-label-secondary text-heading">
                        <i class="bx bx-dollar bx-26px"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-0">
                        ₡ {{ Helper::formatDecimal($this->totalCrcHonorario) }} Honorarios<br>
                        ₡ {{ Helper::formatDecimal($this->totalCrcGasto) }} Gastos
                      </h6>
                      <p class="mb-0">{{ __('Total') }} Colones</p>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-secondary text-heading">
                        <i class="bx bx-money bx-26px"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- DataTable with Buttons -->
        <div class="card">
          <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Proformas') }}</h4>
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
                      @can("create-proformas")
                        @include('livewire.includes.button-create')
                      @endcan

                      @can("edit-proformas")
                        @include('livewire.includes.button-edit')
                      @endcan

                      @can("clonar-proformas")
                        @include('livewire.includes.button-clonar')
                      @endcan

                      @can("delete-proformas")
                        @include('livewire.includes.button-delete', ['textButton' => __('Eliminar')])
                      @endcan

                      @can("export-proformas")
                        <button type="button" class="btn btn-label-secondary" wire:click="$dispatchTo('transactions.transaction-datatable-export', 'prepareExportExcel')">
                            <i class="bx bx-export me-1"></i> {{ __('Exportar') }}
                        </button>
                      @endcan

                      <!-- Dropdown with icon -->
                      <div class="btn-group" role="group" aria-label="DataTable Actions">
                        <!-- Botón para Reiniciar Filtros -->
                          @include('livewire.includes.button-reset-filters')

                          <!-- Botón para Configurar Columnas -->
                          @include('livewire.includes.button-config-columns')
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              @can("view-proformas")
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

                      <tr wire:key='proforma-row-{{ $record->id }}' class="{{ $loop->odd ? 'odd' : 'even' }} {{ $record->department_id != 1 ? 'table-info' : '' }}">
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
    </div>

    <div :class="{ 'd-none': action !== 'create' && action !== 'edit' }" wire:key="proforma-form-container">
        @include('livewire.transactions.partials.form_proforma')
    </div>

    {{-- Área de Componentes Auxiliares Fijos para Estabilidad del DOM --}}
    <div wire:key="proforma-auxiliary-components">
        <livewire:modals.caby-modal wire:key="proforma-caby-modal" />

        @livewire('transactions.send-email-modal', [
            'documentType' => 'PROFORMA',
            'canview'   => auth()->user()->can('view-documento-proformas'),
            'cancreate' => auth()->user()->can('create-documento-proformas'),
            'canedit'   => auth()->user()->can('edit-documento-proformas'),
            'candelete' => auth()->user()->can('delete-documento-proformas'),
            'canexport' => auth()->user()->can('export-documento-proformas'),
        ], 'proforma-send-email-modal')

        @livewire('components.datatable-settings', [
            'datatableName' => 'proforma-datatable',
            'availableColumns' => $this->columns,
            'perPage' => $this->perPage,
        ], 'proforma-datatable-config')

        @can("export-proformas")
            <div class="d-none" wire:key="proforma-export-container">
                <livewire:transactions.transaction-datatable-export exportType="proforma" wire:key="proforma-export-component" />
            </div>
        @endcan
    </div>
</div>
</div>

@push('scripts')
<script>
  function printTable() {
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
        $wire.on('exportReady', (dataArray) => {

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
              });
          }, 100);
        });
    })();
</script>
@endscript
