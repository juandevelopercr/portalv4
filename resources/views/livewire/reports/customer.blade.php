<div class="card">
  <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Clientes') }}</h4>
  <div class="card-datatable text-nowrap">
    <div class="dataTables_wrapper dt-bootstrap5 no-footer">
      <form wire:submit.prevent="exportExcel">
        <div class="row g-6">
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
</div
