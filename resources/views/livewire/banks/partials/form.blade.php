<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="name" name="name"
            class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}">
        </div>
        @error('name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="iniciales">{{ __('Initials') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-font"></i></span>
          <input type="text" wire:model="iniciales" name="iniciales"
            class="form-control @error('iniciales') is-invalid @enderror" placeholder="{{ __('Initials') }}">
        </div>
        @error('iniciales')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="email">{{ __('Email') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-envelope"></i></span>
          <input type="text" wire:model="email" id="email" name="email"
            class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email') }}"
            aria-label="{{ __('Email') }}">
        </div>
        @error('email')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">
          {{ __('You can use letters and numbers') }}
        </div>
      </div>

      <div class="col-md-6 fv-plugins-icon-container"
          x-data="select2LivewireMultipleWithToggle({
            wireModelName: 'departments',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="departments">{{ __('Departments') }}</label>

        <select x-ref="select" id="departments"
                class="form-select"
                multiple>
          @foreach ($this->listdepartments as $department)
            <option value="{{ $department->id }}">{{ $department->name }}</option>
          @endforeach
        </select>

        @error('departments')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 mt-7">
          <input type="checkbox" class="form-check-input" id="desglosar_servicio" wire:model.defer="desglosar_servicio" {{ $desglosar_servicio==1
            ? 'checked' : '' }} />

          <label for="desglosar_servicio" class="switch-label">{{ __('Break down service') }}</label>
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 mt-7">

          <input type="checkbox" class="form-check-input" id="active" wire:model.defer="active" {{ $active==1
            ? 'checked' : '' }} />

          <label for="active" class="switch-label">{{ __('Active') }}</label>
        </div>
      </div>
    </div>

    <div class="row g-6">
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
    </div>
  </form>
</div>

@script()
<script>
  Livewire.on('reinitControls', postId => {
      jQuery(document).ready(function () {
          $('#departments').select2();
          $('#departments').on('change', function (e) {
              var data = $('#departments').select2("val");
              @this.set('departments', data);
          });
      });
  });


</script>
@endscript
