<!-- Edit Permission Modal -->
<div class="modal fade show d-block" id="editPermissionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-simple">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close btn-pinned" wire:click="cancel"></button>
        <div class="text-center mb-6">
          <h4 class="mb-2">{{ __('Edit') }} {{ __('Permission') }}</h4>
          <p>{{ __('Edit permission as per your requirements') }}</p>
        </div>
        <div class="alert alert-warning" role="alert">
          <span>
            <span class="alert-heading mb-1 h5">{{ __('Warning') }}</span><br>
            <span class="mb-0 p">
              {{ __('By editing the permission name, you might break the system permissions functionality') }}.
              {{ __('Please ensure you are absolutely certain before proceeding') }}.</span>
          </span>
        </div>
        <form id="editPermissionForm" class="row pt-2 row-gap-2 gx-4">
          <div class="col-sm-8">
            <label class="form-label" for="editPermissionName">{{ __('Permission Name') }}</label>
            <input type="text" wire:model="editingPermissionName" class="form-control"
              placeholder="{{ __('Permission Name') }}" tabindex="-1" />
          </div>
          <div class="col-sm-4 mb-4">
            <label class="form-label invisible d-none d-sm-inline-block">Button</label>
            <button wire:click="updatePermission" class="btn create-new btn-sm btn-primary mx-1"
              wire:loading.attr="disabled" wire:target="updatePermission" type="button">
              <span wire:loading.remove wire:target="updatePermission">
                <i class="bx bx-save"></i>
                <span class="d-none d-sm-inline-block">{{ __('Save') }}</span>
              </span>
              <span wire:loading wire:target="updatePermission">
                <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                {{ __('Loading...') }}
              </span>
            </button>

          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit Permission Modal -->
