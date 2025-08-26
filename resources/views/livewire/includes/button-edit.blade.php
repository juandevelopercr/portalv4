<button wire:click="edit(null)" type="button"
  class="btn btn-warning btn-sm mx-1 d-flex align-items-center"
  wire:loading.attr="disabled" wire:target="edit(null)">
  <span wire:loading.remove wire:target="edit(null)">
    <i class="bx bx-edit-alt bx-flip-horizontal"></i>
    <span class="d-none d-sm-inline-block">{{ __('Edit') }}</span>
  </span>
  <span wire:loading wire:target="edit(null)">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Loading...') }}
  </span>
</button>
