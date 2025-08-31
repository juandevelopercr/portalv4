<button wire:click="create" type="button" class="btn btn-sm mx-1 btn-success d-flex align-items-center gap-1"
  wire:loading.attr="disabled" wire:target="create">
  <span wire:loading.remove wire:target="create">
    <i class="bx bx-plus"></i>
    <span class="d-none d-sm-inline-block">{{ __('Add New') }}</span>
  </span>
  <span wire:loading wire:target="create">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Loading...') }}
  </span>
</button>
