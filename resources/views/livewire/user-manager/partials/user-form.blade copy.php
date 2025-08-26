@php
    use App\Models\User;
    use Spatie\Permission\Models\Role;
@endphp
<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <h5 class="card-header">{{ __('User Information') }}</h5>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('Please fix the following errors:') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('Personal Information') }}</h6>
    <div class="row">
      <div class="col-md-12">
        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">

            {{-- Mostrar la imagen temporal si se ha subido una nueva --}}
            @if ($profile_photo_path && method_exists($profile_photo_path, 'temporaryUrl'))
            <img class="d-block w-px-100 h-px-100 rounded" src="{{ $profile_photo_path->temporaryUrl() }}"
              alt="{{ __('Photo') }}" id="uploadedAvatar">
            @elseif ($oldProfile_photo_path)
            <img class="d-block w-px-100 h-px-100 rounded"
              src="{{ asset('storage/assets/img/avatars/' . $oldProfile_photo_path) }}" alt="{{ __('Photo') }}"
              id="uploadedAvatar">
            @else
            <img class="d-block w-px-100 h-px-100 rounded" src="{{ asset('storage/assets/default-image.png') }}"
              alt="{{ __('Photo') }}" id="uploadedAvatar">
            @endif

            <div class="button-wrapper">
              <label for="profile_photo_path" class="btn btn-primary me-3 mb-4" tabindex="0">
                <span class="d-none d-sm-block">{{ __('Upload Photo') }}</span>
                <i class="bx bx-upload d-block d-sm-none"></i>
                <input wire:model.live='profile_photo_path' id="profile_photo_path" hidden class="account-file-input"
                  accept="image/png, image/jpeg" type="file" />
                @error('profile_photo_path')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
              </label>

              <button type="button" class="btn btn-label-secondary account-image-reset mb-4" wire:click="resetPhoto">
                <i class="bx bx-reset d-block d-sm-none"></i>
                <span class="d-none d-sm-block">{{ __('Reset') }}</span>
              </button>
            </div>

            <div class="col" wire:loading.delay wire:target="profile_photo_path">
              <!-- Grid -->
              <div class="sk-grid sk-primary">
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
              </div>
              <span>{{ __('Loading, please wait...') }}</span>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="row g-6">
      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="name" name="name" id="name"
            class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="{{ __('Name') }}"
            aria-label="{{ __('Name') }}" aria-describedby="spanname">
        </div>
        @error('name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="email">{{ __('Email') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-envelope"></i></span>
          <input type="text" wire:model="email" id="email" name="email"
            class="form-control dt-email {{ $errors->has('email') ? 'is-invalid' : '' }}"
            placeholder="{{ __('Email') }}" aria-label="{{ __('Email') }}">
        </div>
        @error('email')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">
          {{ __('You can use letters and numbers') }}
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="initials">{{ __('Initials') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-pen"></i></span>
          <input type="text" wire:model="initials" id="initials" name="initials"
            class="form-control {{ $errors->has('initials') ? 'is-invalid' : '' }}" placeholder="{{ __('Initials') }}"
            aria-label="{{ __('Initials') }}">
        </div>
        @error('initials')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6 fv-plugins-icon-container">
        <div class="form-password-toggle">
          <label class="form-label" for="password">{{ __('Password') }}</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-key"></i></span>
            <input type="password" wire:model="password" name="password" id="password" autocomplete="new-password"
              class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
              aria-describedby="multicol-password2" />
            <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="bx bx-hide"></i></span>
          </div>
          @error('password')
          <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">{{ $message
            }}
          </div>
          @enderror
        </div>
      </div>
      <div class="col-md-6 fv-plugins-icon-container">
        <div class="form-password-toggle">
          <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-key"></i></span>
            <input type="password" wire:model="password_confirmation" name="password_confirmation" id="confirm-password"
              autocomplete="new-password"
              class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
              aria-describedby="multicol-confirm-password2" />
            <span class="input-group-text cursor-pointer" id="confirm-password2"><i class="bx bx-hide"></i></span>
          </div>
          @error('password_confirmation')
          <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">{{ $message
            }}
          </div>
          @enderror
        </div>
      </div>
    </div>
    <br>
    <div class="row g-6">
      <div class="col-md-6 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 my-2">
          <input type="checkbox" class="form-check-input" id="active" wire:model.defer="active" {{ $active==1
            ? 'checked' : '' }} />

          <label for="future-billing" class="switch-label">{{ __('Active') }}</label>
        </div>
      </div>
    </div>


    <br>
    <!-- Sección de Permisos -->
    <h6>3. {{ __('Permisos') }}</h6>
    @foreach ($roleAssignments as $index => $assignment)
    @php
        $roleId = $assignment['role_id'] ?? null;
        $role = $roleId ? Role::find($roleId) : null;
        $isFullAccess = $role ? in_array($role->name, [App\Models\User::SUPERADMIN, App\Models\User::ADMINISTRADOR]) : false;
    @endphp

    <div class="role-assignment border rounded p-3 mb-3" wire:key="assignment-{{ $index }}">
        <!-- Encabezado con botón de eliminar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Asignación #{{ $index + 1 }}</h6>
            <button type="button" class="btn btn-sm btn-danger"
                    wire:click="removeRoleAssignment({{ $index }})"
                    @if(count($roleAssignments) <= 1) disabled @endif>
                <i class="fas fa-trash me-1"></i> Eliminar
            </button>
        </div>

        <div class="row g-3">
            <!-- Selección de Rol -->
            <div class="col-md-4">
                <label class="form-label">{{ __('Role') }}</label>
                <div wire:ignore>
                    <select class="form-select role-select select2" data-index="{{ $index }}">
                        <option value="">{{ __('Select Role') }}</option>
                        @foreach ($availableRoles as $id => $name)
                            <option value="{{ $id }}"
                                {{ $assignment['role_id'] == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error("roleAssignments.{$index}.role_id")
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Departamento (solo para roles sin acceso total) -->
            @if (!$isFullAccess)
                <div class="col-md-4">
                    <label class="form-label">{{ __('Department') }}</label>
                    <div wire:ignore>
                        <select class="form-select department-select select2" data-index="{{ $index }}">
                            <option value="">{{ __('Select Department') }}</option>
                            @foreach ($this->departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ $assignment['department_id'] == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error("roleAssignments.{$index}.department_id")
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Bancos (solo si hay departamento seleccionado) -->
                @if ($assignment['department_id'])
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Banks') }}</label>
                        <div wire:ignore>
                            <select class="form-select bank-select select2" multiple data-index="{{ $index }}">
                                @php
                                    $banks = $this->getBanksForDepartment($assignment['department_id']);
                                @endphp
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}"
                                        {{ in_array($bank->id, $assignment['banks'] ?? []) ? 'selected' : '' }}>
                                        {{ $bank->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error("roleAssignments.{$index}.banks")
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            @else
                <div class="col-md-8 d-flex align-items-center">
                    <div class="alert alert-primary mb-0 w-100">
                        <i class="fas fa-info-circle me-2"></i>
                        Este rol tiene acceso completo a todos los departamentos y bancos.
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endforeach

    <!-- Botón para agregar más asignaciones -->
    <button type="button" class="btn btn-primary mb-3" wire:click="addRoleAssignment">
        <i class="fas fa-plus me-2"></i> {{ __('Add Assignment') }}
    </button>


    <div class="pt-6">
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

  {{-- @livewire('users.user-activity-timeline') --}}
</div>

@php
/*
@script
<script>
  document.addEventListener('livewire:init', () => {
    console.log("livewire:init");
    // Función para inicializar Select2
    function initSelect2() {
      $('.select2').select2({
        width: '100%',
        dropdownParent: $('.role-assignment')
      });

      // Manejar cambios en los selects
      $('.select2').on('change', function(e) {
        const id = $(this).attr('id');
        const value = $(this).val();
        const parts = id.split('-');
        const type = parts[0];
        const index = parts[1];

        if (type === 'role') {
          @this.set(`roleAssignments.${index}.role_id`, value);
        }
        else if (type === 'dept') {
          @this.set(`roleAssignments.${index}.department_id`, value);
        }
        else if (type === 'bank') {
          @this.set(`roleAssignments.${index}.banks`, value);
        }
      });
    }

    // Inicializar al cargar
    initSelect2();

    // Re-inicializar cuando Livewire emita el evento
    Livewire.on('reinitFormControls', () => {
      $('.select2').select2('destroy');
      initSelect2();
    });

    // Actualizar Select2 cuando cambien los datos
    Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
      console.log("Entró al Livewire.hook('commit')");
      succeed(() => {
        if (component.name === 'users.user-manager') {
          setTimeout(() => {
            $('.select2').select2('destroy');
            initSelect2();
          }, 10);
        }
      });
    });
  });
</script>
@endscript
*/
@endphp
