@php
    use App\Models\User;
@endphp
<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <h5 class="card-header">{{ __('User Information') }}</h5>
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
    <h6>3. {{ __('Permisos') }}</h6>
    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container"
          x-data="select2LivewireMultipleWithToggle({
            wireModelName: 'roles',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="roles">{{ __('Role') }}</label>
        <select x-ref="select" id="roles"
                class="form-select"
                multiple>
            @foreach ($this->listroles as $rol)
              <option value="{{ $rol->name }}" wire:key="role-{{ $rol->name }}">{{ $rol->name }}</option>
            @endforeach
        </select>
        @error('roles')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      @if (auth()->user()->hasRole(User::SUPERADMIN))
      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="tenant_id">{{ __('Cliente') }}</label>
        <div wire:ignore>
          <select wire:model="tenant_id" id="tenant_id" class="select2 form-select @error('tenant_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->tenants as $tenant)
              <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
            @endforeach
          </select>
        </div>
        @error('tenant_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      @endif

    </div>

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


@script()
<script>
  (function() {
    // Función para inicializar Select2
    const initializeSelect2 = () => {
      const selects = [
        'tenant_id',
      ];

      selects.forEach((id) => {
        const element = document.getElementById(id);
        if (element) {
          //console.log(`Inicializando Select2 para: ${id}`);

          $(`#${id}`).select2();

          $(`#${id}`).on('change', function() {
            const newValue = $(this).val();
            const livewireValue = @this.get(id);

            if (newValue !== livewireValue) {
              // Actualiza Livewire solo si es el select2 de `condition_sale`
              // Hay que poner wire:ignore en el select2 para que todo vaya bien
              const specificIds = ['tenant_id']; // Lista de IDs específicos

              if (specificIds.includes(id)) {
                @this.set(id, newValue);
              } else {
                // Para los demás select2, actualiza localmente sin llamar al `updated`
                @this.set(id, newValue, false);
              }
            }
          });
        }

        // Sincroniza el valor actual desde Livewire al Select2
        const currentValue = @this.get(id);
        $(`#${id}`).val(currentValue).trigger('change');
      });

    };

    // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
    Livewire.on('reinitFormControls', () => {
      console.log('Reinicializando controles después de Livewire update reinitFormControls');
      setTimeout(() => {
        initializeSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });

  })();
</script>
@endscript
