<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

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

    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="codigo">{{ __('Code') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="codigo" id="codigo"
            class="form-control @error('codigo') is-invalid @enderror" placeholder="{{ __('Code') }}">
        </div>
        @error('codigo')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="descrip">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-font"></i></span>
          <input type="text" wire:model="descrip" id="descrip"
            class="form-control @error('descrip') is-invalid @enderror" placeholder="{{ __('Name') }}">
        </div>
        @error('descrip')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 my-8">
          <input type="checkbox" class="form-check-input" id="favorite" wire:model.live="favorite" {{ $favorite==1
            ? 'checked' : '' }} />

          <label for="future-billing" class="switch-label">{{ __('Favorite') }}</label>
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
  (function() {
    // Función para inicializar Select2
    const initializeSelect2 = () => {
      const selects = [
        'moneda_id', 'selected_banks', 'selected_locations', 'selected_departments', 'banco_id'
      ];

      selects.forEach((id) => {
        $(`#${id}`).select2();

        $(`#${id}`).on('change', function() {
          const newValue = $(this).val();
          const livewireValue = @this.get(id);

          if (newValue !== livewireValue) {
            // Actualiza Livewire solo si es el select2 de `condition_sale`
            // Hay que poner wire:ignore en el select2 para que todo vaya bien
            //const specificIds = ['condition_sale', 'location_id', 'department_id']; // Lista de IDs específicos
            const specificIds = []; // Lista de IDs específicos

            if (specificIds.includes(id)) {
              @this.set(id, newValue);
            } else {
              // Para los demás select2, actualiza localmente sin llamar al `updated`
              @this.set(id, newValue, false);
            }
          }
        });

        // Sincroniza el valor actual desde Livewire al Select2
        const currentValue = @this.get(id);
        $(`#${id}`).val(currentValue).trigger('change');
      });
    };

    /*
    // Inicialización inicial de Select2
    document.addEventListener('livewire:load', () => {
        console.log('Inicializando Select2 al cargar el componente');
        initializeSelect2();
    });

    // Re-inicialización de Select2 tras cada actualización del DOM
    document.addEventListener('livewire:update', () => {
        console.log('Reinicializando Select2 tras Livewire update');
        initializeSelect2();
    });
    */

    // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
    Livewire.on('reinitFormCatalogoCuentaControls', () => {
      console.log('Reinicializando controles después de Livewire update reinitFormCatalogoCuentaControls');
      setTimeout(() => {
        initializeSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });
  })();
</script>
@endscript
