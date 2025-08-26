<button type="button" class="btn btn-secondary btn-sm mx-1 d-flex align-items-center" wire:click="resetFilters"
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
