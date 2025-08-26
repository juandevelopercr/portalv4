<button wire:click="showPayment" type="button" class="btn btn-sm mx-1 btn-warning d-flex align-items-center gap-1"
  wire:loading.attr="disabled" wire:target="showPayment">
  <span wire:loading.remove wire:target="showPayment">
    <i class="bx bx-dollar-circle"></i>
    <span class="d-none d-sm-inline-block">{{ __('Mostrar pagos') }}</span>
  </span>
  <span wire:loading wire:target="showPayment">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Loading...') }}
  </span>
</button>
