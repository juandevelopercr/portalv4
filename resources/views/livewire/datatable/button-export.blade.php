<button type="button"
        class="btn btn-label-primary btn-sm mx-1 d-flex align-items-center"
        wire:click="prepareExportExcel"
        wire:loading.attr="disabled"
        wire:target="prepareExportExcel">
  <span wire:loading.remove wire:target="prepareExportExcel">
    <i class="bx bx-export bx-flip-horizontal"></i>
    <span class="d-none d-sm-inline-block">{{ __('Exportar') }}</span>
  </span>
  <span wire:loading wire:target="prepareExportExcel">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Cargando...') }}
  </span>
</button>
