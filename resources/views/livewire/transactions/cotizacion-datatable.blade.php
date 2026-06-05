@php
  use App\Models\Transaction;
  use App\Models\User;
  use Illuminate\Support\Facades\Auth;
@endphp

<div wire:key="cotizacion-main-container">
<div x-data="{ action: @entangle('action') }">
    <div :class="{ 'd-none': action !== 'list' }" wire:key="cotizacion-list-container">
        <!-- DataTable with Buttons -->
        <div class="card">
          <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Cotizaciones') }}</h4>
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

                      @can("export-cotizaciones")
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

              @can("view-cotizaciones")
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

                      <tr wire:key='cotizacion-row-{{ $record->id }}' class="{{ $loop->odd ? 'odd' : 'even' }} {{ $record->department_id != 1 ? 'table-info' : '' }}">
                        <td class="control" style="display: none;" tabindex="0"></td>
                        <td class="dt-checkboxes-cell">
                          <input type="checkbox" class="form-check-input" wire:model.live="selectedIds"
                            value="{{ $record->id }}">
                        </td>

                        @include('livewire.includes.columns', [
                          'columns' => $this->columns,
                          'record'=>$record,
                          'canedit'=>auth()->user()->can('edit-cotizaciones')])

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

    <div :class="{ 'd-none': action !== 'create' && action !== 'edit' }" wire:key="cotizacion-form-container">
        @include('livewire.transactions.partials.form_cotizacion')
    </div>

    {{-- Área de Componentes Auxiliares Fijos para Estabilidad del DOM --}}
    <div wire:key="cotizacion-auxiliary-components">
        <livewire:modals.caby-modal wire:key="cotizacion-caby-modal" />

        @livewire('transactions.send-email-modal', [
            'documentType' => 'COTIZACION',
            'canview'   => auth()->user()->can('view-documento-cotizaciones'),
            'cancreate' => auth()->user()->can('create-documento-cotizaciones'),
            'canedit'   => auth()->user()->can('edit-documento-cotizaciones'),
            'candelete' => auth()->user()->can('delete-documento-cotizaciones'),
            'canexport' => auth()->user()->can('export-documento-cotizaciones'),
        ], 'cotizacion-send-email-modal')

        @livewire('components.datatable-settings', [
            'datatableName' => 'cotizacion-datatable',
            'availableColumns' => $this->columns,
            'perPage' => $this->perPage,
        ], 'cotizacion-datatable-config')

        @can("export-cotizaciones")
            <div class="d-none" wire:key="cotizacion-export-container">
                <livewire:transactions.transaction-datatable-export exportType="cotizacion" wire:key="cotizacion-export-component" />
            </div>
        @endcan
    </div>
</div>
</div>

  <!-- Modal de resultados -->
@if ($showModalCaso)
    <div class="modal fade show" id="searchCaso" tabindex="-1" aria-labelledby="exampleModalLabel"
         style="display: block; z-index: 10060;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-simple">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Seleccione el caso</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModalCaso', false)"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="row g-6">
                          <input type="hidden" id="livewire_bank_id" value="{{ $bankCotizacionId }}">
                          <div class="col-md-12 select2-primary">
                            <label class="form-label" for="caso_id">{{ __('Caso') }}</label>
                            <div wire:ignore>
                              <select id="caso_id" class="form-select select2-ajax" required
                                      data-placeholder="Buscar caso por número o deudor">
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="row g-6 pt-4">
                          <div class="col-md-12">
                            <button type="button" class="btn btn-primary me-2"
                                    wire:click="asignarCaso">
                              <span>{{ __('Asignar Caso') }}</span>
                            </button>
                            <button type="button" class="btn btn-secondary"
                                    wire:click="$set('showModalCaso', false)">
                              <span>{{ __('Cancelar') }}</span>
                            </button>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

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

@script()
<script>
  $(document).ready(function() {

    // Función para inicializar Select2 en el modal
    function initCasoSelect2() {
        // Destruir si ya existe
        if ($('#caso_id').data('select2')) {
            $('#caso_id').select2('destroy');
        }

        // Inicializar con configuración
        $('#caso_id').select2({
            placeholder: $('#caso_id').data('placeholder'),
            minimumInputLength: 2,
            dropdownParent: $('#searchCaso'), // Referencia al modal
            ajax: {
                url: '/api/casos/search',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        bank_id: $("#livewire_bank_id").val()
                    };
                },
                processResults: function(data) {
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

        // Evento change
        $('#caso_id').on('change', function() {
            const val = $(this).val();
            if (typeof $wire !== 'undefined') {
              $wire.set('caso_id', val);
            }
        });
    }

    // Inicializar cuando el modal se muestra
    $wire.on('showModalCaso', () => {
        // Pequeño retraso para asegurar que el modal está visible
        setTimeout(() => {
            initCasoSelect2();
        }, 100);
    });

    // Resetear al cerrar el modal
    $wire.on('hideModalCaso', () => {
        if ($('#caso_id').data('select2')) {
            $('#caso_id').select2('destroy');
        }
    });

    // Inicializar si el modal ya está visible al cargar
    document.addEventListener('livewire:init', () => {
        if (document.getElementById('searchCaso')) {
            initCasoSelect2();
        }
    });

  })
</script>
@endscript
