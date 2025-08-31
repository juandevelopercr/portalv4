<div>
  <!-- Modal -->
  <div wire:ignore.self class="modal fade" style="background-color: rgba(0, 0, 0, 0.5);" id="datatableSettingsModal"
    tabindex="-1" aria-labelledby="datatableSettingsLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="datatableSettingsLabel">Configurar Columnas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="col-md-3 fv-plugins-icon-container">
              <label class="form-label" for="perPage">{{ __('Registros por página') }}</label>
              <div class="select2-primary fv-plugins-icon-container"
                  x-data="select2Livewire({
                      wireModelName: 'perPage',
                      postUpdate: true,
                      dropdownParent: '.modal'
                  })"
                  x-init="init($refs.select)"
                  wire:ignore>
                  <select x-ref="select" id="perPage" class="select2 form-select">
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="75">75</option>
                      <option value="100">100</option>
                      <option value="150">150</option>
                      <option value="200">200</option>
                  </select>
              </div>

              @error('perPage')
                <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
          </div>
          <br>

          <ul class="list-group list-group-flush" id="handle-list-2">
            @foreach ($columns as $column)
            <li class="list-group-item d-flex justify-content-between align-items-center"
              data-key="{{ $column['field'] }}" s>
              <span class="d-flex justify-content-between align-items-center">
                <i class="drag-handle cursor-move bx bx-menu align-text-bottom me-2"></i>
                <span>{{ $column['label'] }}</span>
              </span>
              <input type="checkbox" wire:model="columns.{{ $loop->index }}.visible">
            </li>
            @endforeach
          </ul>
        </div>
        <div class="modal-footer">
          <!-- Botón para Cerrar Modal -->
          <button type="button" data-bs-dismiss="modal" wire:loading.attr="disabled"
            class="btn btn-outline-secondary btn-label-danger me-sm-4 me-1 mt-5" data-bs-toggle="tooltip"
            data-bs-offset="0,8" data-bs-placement="top" data-bs-custom-class="tooltip-dark"
            data-bs-original-title="{{ __('Cerrar') }}">

            <!-- Icono de cerrar cuando no está cargando -->
            <span wire:loading.remove wire:target="data-bs-dismiss">
              <i class="bx bx-x"></i> {{ __('Cerrar') }}
            </span>

            <!-- Icono de carga cuando el modal se está cerrando -->
            <span wire:loading wire:target="data-bs-dismiss">
              <i class="spinner-border spinner-border-sm me-1" role="status"></i>
              {{ __('Cerrando...') }}
            </span>
          </button>

          <!-- Botón para Guardar -->
          <button type="button" wire:click="save" wire:loading.attr="disabled"
            class="btn btn-primary data-submit me-sm-4 me-1 mt-5" data-bs-toggle="tooltip" data-bs-offset="0,8"
            data-bs-placement="top" data-bs-custom-class="tooltip-dark" data-bs-original-title="{{ __('Guardar') }}">

            <!-- Icono de guardar cuando no está cargando -->
            <span wire:loading.remove wire:target="save">
              <i class="bx bx-save"></i> {{ __('Guardar') }}
            </span>

            <!-- Icono de carga cuando se está guardando -->
            <span wire:loading wire:target="save">
              <i class="spinner-border spinner-border-sm me-1" role="status"></i>
              {{ __('Guardando...') }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@script()
<script>
  const handleList2 = document.getElementById('handle-list-2');

  if (handleList2) {
      Sortable.create(handleList2, {
          animation: 150,
          group: 'handleList',
          handle: '.drag-handle',
          onEnd: (event) => {
              const orderedKeys = Array.from(event.to.children).map(item => item.getAttribute('data-key'));

              console.log("Ordered Keys (Before Sending):", orderedKeys);
              console.log("Is Array:", Array.isArray(orderedKeys));

              // Envía el nuevo orden a Livewire
              Livewire.dispatch('updateOrder', { orderedKeys: orderedKeys });
          }
      });
  }

  Livewire.on('closeModal', () => {
      /*
      console.log("Cerrar Modal");
      const modalElement = document.getElementById('datatableSettingsModal');
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance.hide();
      */
      console.log("Cerrar Modal");

      const modalElement = document.getElementById('datatableSettingsModal');
      if (!modalElement) {
        console.warn("Modal no encontrado en el DOM");
        return;
      }

      // Usa getOrCreateInstance para evitar null
      const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
      modalInstance.hide();
  });

</script>
@endscript
