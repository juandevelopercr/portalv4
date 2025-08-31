<div>
  <div class="container">
    <div class="row">
      @foreach($permissions as $module => $modulePermissions)
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __(ucfirst($module)) }}</h5>
            @role('SuperAdmin')
              <button
                  wire:click="showAddPermissionForm('{{ $module }}')"
                  wire:key="add-permission-btn-{{ $module }}"
                  class="btn create-new btn-sm btn-primary mx-1"
                  wire:loading.attr="disabled"
                  wire:target="showAddPermissionForm('{{ $module }}')"
                  type="button">
                <span wire:loading.remove wire:target="showAddPermissionForm('{{ $module }}')">
                  <i class="bx bx-plus"></i>
                  <span class="d-none d-sm-inline-block">{{ __('Add New') }}</span>
                </span>
                <span wire:loading wire:target="showAddPermissionForm('{{ $module }}')">
                  <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                  {{ __('Loading...') }}
                </span>
              </button>
            @endrole
          </div>
          <div class="card-body">
            <ul class="list-group">
              @foreach($modulePermissions as $permission)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ __(ucfirst(str_replace('-' . $module, '', $permission->name))) }}</span>
                <div>
                  <!-- Botón para Editar con Loading -->
                  <button wire:click="editPermission({{ $permission->id }})" wire:loading.attr="disabled"
                    class="btn btn-icon item-edit" data-bs-toggle="tooltip" data-bs-offset="0,8" data-bs-placement="top"
                    data-bs-custom-class="tooltip-dark" data-bs-original-title="{{ __('Edit') }}">

                    <!-- Ícono normal (visible cuando no está en loading) -->
                    <span wire:loading.remove wire:target="editPermission({{ $permission->id }})">
                      <i class="bx bx-edit bx-md"></i>
                    </span>

                    <!-- Ícono de carga (visible cuando está en loading) -->
                    <span wire:loading wire:target="editPermission({{ $permission->id }})">
                      <i class="spinner-border spinner-border-sm me-1" role="status"></i>
                    </span>
                  </button>

                  <button wire:click.prevent="confirmarAccion(
                        {{ $permission->id }},
                        'delete',
                        '{{ __('Are you sure you want to delete this record') }} ?',
                        '{{ __('After confirmation, the record will be deleted') }}',
                        '{{ __('Yes, proceed') }}'
                      )"
                      class="btn btn-icon item-trash text-danger"
                      data-bs-toggle="tooltip"
                      data-bs-offset="0,8"
                      data-bs-placement="top"
                      data-bs-custom-class="tooltip-dark"
                      data-bs-original-title="{{ __('Delete') }}"
                    >
                    <i class="bx bx-trash bx-md"></i>
                  </button>
                </div>
              </li>
              @endforeach
            </ul>

            @if($showFormForModule === $module)
            @include('_partials/_modals/modal-add-permission')
            @endif

            @if($editingPermissionId)
            @include('_partials/_modals/modal-edit-permission')
            @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

</div>
