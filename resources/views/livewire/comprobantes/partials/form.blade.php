
   <form wire:submit.prevent="{{ $action === 'create' ? 'store' : 'update' }}" enctype="multipart/form-data">

   @if ($errors->any())
    <div class="alert alert-danger mb-4">
        <strong>{{ __('Please fix the following errors:') }}</strong>
        <ul class="mt-2">
            @foreach ($errors->getMessages() as $field => $messages)
                @foreach ($messages as $message)
                    <li>
                        <strong>{{ $field }}:</strong> {{ $message }}
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Mensaje de campos no editables -->
    @if($action === 'edit')
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i>
        Al editar un comprobante, solo los archivos de respuesta y PDF pueden ser reemplazados.
        El XML principal no puede modificarse.
    </div>
    @endif

    <!-- Sección de archivos -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        <!-- XML Comprobante (solo creación) -->
        @if($action === 'create')
        <div>
            <label class="form-label" for="xmlFile">{{ __('XML Comprobante') }} *</label>
            <div class="input-group">
                <input type="file" wire:model="xmlFile" id="xmlFile"
                    class="form-control @error('xmlFile') is-invalid @enderror"
                    accept=".xml">
            </div>
            @error('xmlFile')<div class="text-danger mt-1">{{ $message }}</div>@enderror
            <!-- ... (indicadores de carga) ... -->
        </div>
        @else
        <!-- Visualización XML existente (edición) -->
        <div>
            <label class="form-label">{{ __('XML Comprobante') }}</label>
            <div class="p-3 bg-gray-100 rounded flex items-center">
                <i class="fas fa-file-code text-blue-500 text-xl mr-3"></i>
                <div>
                    <p class="font-medium">Comprobante original</p>
                    <button type="button" wire:click="downloadXml({{ $recordId }})"
                            class="text-blue-600 hover:underline text-sm">
                        <i class="fas fa-download mr-1"></i> Descargar XML
                    </button>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                El XML principal no puede modificarse. Para corregir errores, elimine y vuelva a cargar.
            </p>
        </div>
        @endif

        <!-- XML Respuesta (editable) -->
        <div>
            <label class="form-label" for="xml_respuestaFile">
                {{ __('XML Respuesta') }}
                @if($action === 'edit' && $currentXmlRespuestaPath)
                <span class="text-green-600 text-sm">(Existente)</span>
                @endif
            </label>
            <div class="input-group">
                <input type="file" wire:model="xml_respuestaFile" id="xml_respuestaFile"
                    class="form-control @error('xml_respuestaFile') is-invalid @enderror"
                    accept=".xml">
            </div>
            @error('xml_respuestaFile')<div class="text-danger mt-1">{{ $message }}</div>@enderror

            <!-- Mostrar archivo actual en edición -->
            @if($action === 'edit' && $currentXmlRespuestaPath && !$xml_respuestaFile)
            <div class="mt-2">
                <button type="button" wire:click="downloadXmlRespuesta({{ $recordId }})"
                        class="text-blue-600 hover:underline text-sm">
                    <i class="fas fa-download mr-1"></i> Descargar actual
                </button>
            </div>
            @endif
        </div>

        <!-- XML Confirmación (Mensaje Receptor) -->
        @php
        /*
        <div>
            <label class="form-label" for="xml_confirmacionFile">
                {{ __('XML Confirmación') }}
            </label>
            <input type="file" wire:model="xml_confirmacionFile"
                  class="form-control @error('xml_confirmacionFile') is-invalid @enderror"
                  accept=".xml">
            @error('xml_confirmacionFile')<div class="text-danger mt-1">{{ $message }}</div>@enderror
            <!-- Mostrar archivo existente en edición -->
        </div>
        */
        @endphp

        <!-- PDF (editable) -->
        <div>
            <label class="form-label" for="pdfFile">
                {{ __('PDF Comprobante') }}
                @if($action === 'edit' && $currentPdfPath)
                <span class="text-green-600 text-sm">(Existente)</span>
                @endif
            </label>
            <div class="input-group">
                <input type="file" wire:model="pdfFile" id="pdfFile"
                    class="form-control @error('pdfFile') is-invalid @enderror"
                    accept=".pdf">
            </div>
            @error('pdfFile')<div class="text-danger mt-1">{{ $message }}</div>@enderror

            <!-- Mostrar archivo actual en edición -->
            @if($action === 'edit' && $currentPdfPath && !$pdfFile)
            <div class="mt-2">
                <button type="button" wire:click="downloadPdf({{ $recordId }})"
                        class="text-blue-600 hover:underline text-sm">
                    <i class="fas fa-download mr-1"></i> Descargar actual
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Campos editables (solo para edición) -->
    @if($action === 'edit')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="form-label">Estado Hacienda</label>
            <select wire:model="estado_hacienda" class="form-control" disabled>
                @foreach($listEstadosHacienda as $estado)
                <option value="{{ $estado['id'] }}">{{ $estado['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Respuesta Hacienda</label>
            <textarea wire:model="respuesta_hacienda"
                      class="form-control"
                      rows="3"
                      placeholder="Detalles de respuesta de Hacienda"></textarea>
        </div>
    </div>
    @endif

    <div class="col-md-3 select2-primary fv-plugins-icon-container">
      <label class="form-label" for="mensajeConfirmacion">{{ __('Mensaje de confirmación') }}</label>
      <div wire:ignore>
        <select wire:model="mensajeConfirmacion" id="mensajeConfirmacion" class="select2 form-select @error('mensajeConfirmacion') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->listaMensajeconfirmacion as $mensaje)
            <option value="{{ $mensaje['id'] }}">{{ $mensaje['name'] }}</option>
          @endforeach
        </select>
      </div>
      @error('mensajeConfirmacion')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-3 fv-plugins-icon-container">
      <label class="form-label" for="detalle">{{ __('Detalle') }}</label>
      <div class="input-group input-group-merge has-validation">
        <span class="input-group-text"><i class="bx bx-receipt"></i></span>
        <input type="text" wire:model="detalle" name="detalle" id="detalle"
          class="form-control @error('detalle') is-invalid @enderror" placeholder="{{ __('Detalle') }}"
          aria-label="{{ __('Detalle') }}" aria-describedby="spandetalle">
      </div>
      @error('detalle')
      <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <!-- Botones de acción -->
    <div class="flex justify-between items-center mt-6">
        <button type="button" wire:click="cancel" class="btn btn-secondary">
            <i class="fas fa-times mr-2"></i> Cancelar
        </button>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            @if($action === 'create')
            <i class="fas fa-save mr-2"></i> Guardar Comprobante
            @else
            <i class="fas fa-sync-alt mr-2"></i> Actualizar Comprobante
            @endif

            <span wire:loading>
                <i class="fas fa-spinner fa-spin ml-2"></i>
            </span>
        </button>
    </div>
</form>


@script()
<script>
  $(document).ready(function() {
    // Para la busqueda del caso
    // Configuración AJAX para caso_id
    window.select2Config = {
      mensajeConfirmacion: {fireEvent: false},
    };

    //**************************************************************
    //*****Para todos los demás select2****************
    //**************************************************************
    Object.entries(select2Config).forEach(([id, config]) => {
      const $select = $('#' + id);
      if (!$select.length) return;

      $select.select2();

      // Default values
      const fireEvent = config.fireEvent ?? false;
      //const allowClear = config.allowClear ?? false;
      //const placeholder = config.placeholder ?? 'Seleccione una opción';

      $select.on('change', function() {
        let data = $(this).val();
        $wire.set(id, data, fireEvent);
        $wire.id = data;
        //@this.department_id = data;
        console.log(data);
      });
    });
  })

  Livewire.on('setSelect2Value', ({ id, value, text }) => {
    const option = new Option(text, value, true, true);
    console.log("Entró al setSelect2Value con option: " + option);
    $('#' + id).append(option).trigger('change');
  });

  Livewire.on('updateSelect2Options', ({ id, options }) => {
    const $select = $('#' + id);
    $select.empty(); // Limpiar opciones

    console.log("Se limpia el select2 " + id);

    options.forEach(opt => {
        const option = new Option(opt.text, opt.id, false, false);
        $select.append(option);
        console.log("Se adiciona el valor " + option);
    });

    $select.trigger('change');
    console.log("Se dispara el change");
  });

</script>
@endscript
