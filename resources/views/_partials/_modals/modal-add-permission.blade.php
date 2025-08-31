<!-- Add Permission Modal -->
<div class="modal fade show d-block" id="addPermissionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-simple">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close btn-pinned" wire:click="cancel"></button>
        <div class="text-center mb-6">
          <h4 class="mb-2">{{ __('Add New Permission') }}</h4>
          <p>{{ __('Permissions you may use and assign to your users') }}</p>
        </div>
        <form id="addPermissionForm" class="row" onsubmit="return false">
          <div class="col-12 mb-4">
            <label class="form-label" for="modalPermissionName">{{ __('Permission Name') }}</label>
            <input type="text" wire:model="action" class="form-control" placeholder="Enter action (view, edit, delete)"
              autofocus />
            @error('action') <span class="text-danger">{{ $message }}</span> @enderror
          </div>
          <div class="col-12 text-center demo-vertical-spacing">

            <button wire:click="createPermission" class="btn create-new btn-sm btn-primary mx-1"
              wire:loading.attr="disabled" wire:target="createPermission" type="button">
              <span wire:loading.remove wire:target="createPermission">
                <i class="bx bx-save"></i>
                <span class="d-none d-sm-inline-block">{{ __('Save') }}</span>
              </span>
              <span wire:loading wire:target="createPermission">
                <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                {{ __('Loading...') }}
              </span>
            </button>

            <!-- Botón Cancel -->
            <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="cancel"
              wire:loading.attr="disabled" wire:target="cancel">
              <span wire:loading.remove wire:target="cancel">
                <span class="fa fa-remove bx-18px me-2"></span>{{ __('Cancel') }}
              </span>
              <span wire:loading wire:target="cancel">
                <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Cancelling...') }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add Permission Modal -->
