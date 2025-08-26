<form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <div class="row g-6">
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
        <div class="col-md-6 fv-plugins-icon-container">
            <label class="form-label" for="name">{{ __('Name') }}</label>
            <div class="input-group input-group-merge has-validation">
                <span id="spanname" class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" wire:model="name" name="name" id="name"
                    class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}"
                    aria-label="{{ __('Name') }}" aria-describedby="spanname">
            </div>
            @error('name')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12 fv-plugins-icon-container"
            x-data="select2LivewireMultipleWithToggle({
              wireModelName: 'banks',
              postUpdate: true
            })"
            x-init="init($refs.select)"
            wire:ignore>
          <label class="form-label" for="banks">{{ __('Bank') }}</label>
          <select x-ref="select" id="banks"
                  class="form-select"
                  multiple>
            @foreach ($this->listbanks as $bank)
              <option value="{{ $bank->id }}"> {{ $bank->name }} </option>
            @endforeach
          </select>
          @error('banks')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3 fv-plugins-icon-container">
            <div class="form-check form-switch" style="margin-top: 30px;">

                <input type="checkbox" class="form-check-input" id="active" wire:model="active" {{ $active==1
                    ? 'checked' : '' }} />

                <label for="future-billing" class="switch-label">{{ __('Active') }}</label>
            </div>
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

@php
/*
@script()
<script>
  console.log(Alpine);

  (function() {
    // Función para inicializar Select2
    const initializeSelect2 = () => {
      const selects = [
        'banks'
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
              const specificIds = []; // Lista de IDs específicos

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
*/
@endphp
