<!-- Add Role Modal -->
<div class="modal fade show d-block" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Roles') }}</h5>
        <button type="button" class="btn-close" wire:click="cancel" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Add role form -->
        <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="row g-6">
          <div class="col-12">
            <label class="form-label">{{ __('Role Name') }}</label>
            <input type="text" wire:model="name" class="form-control" placeholder="{{ __('Role Name') }}">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
          </div>
          <div class="col-12">
            <h5 class="mb-6">{{ __('Role Permissions by module') }}</h5>
            <!-- Permission table -->
            <div class="table-responsive">
              <table class="table table-flush-spacing mb-0">
                <tbody>
                  @foreach($groupedPermissions as $module => $modulePermissions)
                  <tr>
                    <td class="fw-bold">{{ __(ucfirst($module)) }}</td>
                    <td>
                      <div class="d-flex flex-wrap">
                        @foreach($modulePermissions as $permission)
                        <div class="form-check me-3">
                          <input class="form-check-input" type="checkbox" wire:model="rolePermissions"
                            value="{{ $permission->name }}" id="perm-{{ $permission->id }}">
                          <label class="form-check-label" for="perm-{{ $permission->id }}">
                            {{ __(ucfirst(str_replace('-' . $module, '', $permission->name))) }}
                          </label>
                        </div>
                        @endforeach
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <!-- Permission table -->
          </div>
          <div class="modal-footer">

            {{-- Incluye botones de guardar y guardar y cerrar --}}
            @include('livewire.includes.button-saveAndSaveAndClose')

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
        <!--/ Add role form -->
      </div>
    </div>
  </div>
</div>
<!--/ Add Role Modal -->
