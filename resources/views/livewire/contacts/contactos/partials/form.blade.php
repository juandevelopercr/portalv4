<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }} Cliente: {{ $this->contactName }}</h6>

    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-detail"></i></span>
          <input type="text" wire:model="name" name="name" id="name"
            class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('name') }}">
        </div>
        @error('name')
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
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="telefono">{{ __('Phone') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-phone"></i></span>
          <input type="text" wire:model="telefono" class="form-control @error('telefono') is-invalid @enderror">
        </div>
        @error('telefono')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-1 fv-plugins-icon-container">
        <label class="form-label" for="ext">{{ __('Ext') }}</label>
        <div class="input-group input-group-merge has-validation">
          <input type="text" wire:model="ext" class="form-control @error('ext') is-invalid @enderror">
        </div>
        @error('ext')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="celular">{{ __('Celular') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-phone"></i></span>
          <input type="text" wire:model="celular" class="form-control @error('celular') is-invalid @enderror">
        </div>
        @error('celular')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="department_id">{{ __('Department') }}</label>
        <div wire:ignore>
          <select wire:model="department_id" id="department_id" class="select2 form-select @error('department_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->departments as $department)
              <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
          </select>
        </div>
        @error('department_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>
    <br>

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
