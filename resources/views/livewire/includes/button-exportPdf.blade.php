<button wire:click="exportPdf" type="button" class="btn btn-sm mx-1 btn-info d-flex align-items-center gap-1"
  wire:loading.attr="disabled" wire:target="exportPdf">
  <span wire:loading.remove wire:target="exportPdf">
    <i class="bx bxs-file-pdf"></i>
    <span class="d-none d-sm-inline-block">{{ __('Estado de cuenta') }}</span>
  </span>
  <span wire:loading wire:target="exportPdf">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Loading...') }}
  </span>
</button>
