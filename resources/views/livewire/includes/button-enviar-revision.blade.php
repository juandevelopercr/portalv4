@php
    $btnAction = $btnAction ?? 'revision'; // Valor por defecto
    $textButton = __('Enviar a revisión');
@endphp

<button type="button"
    class="btn btn-warning btn-sm mx-1 d-flex align-items-center"
    wire:click.prevent="confirmarAccionRevision(null, @js($btnAction), '{{ __("¿Está seguro que desea enviar este cheque a revisión?") }}', 
    '{{ __("Después de confirmar, el cheque será enviado a revisión") }}', '{{ __("Sí, proceder") }}')"
    wire:loading.attr="disabled"
    wire:target="confirmarAccionRevision"
    wire:key="boton-revision">

    <span wire:loading.remove wire:target="confirmarAccionRevision" wire:key="revision-label">
        <i class="bx bx-search-alt-2 bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">
            {{ $textButton ?? __('Enviar a revisión') }}
        </span>
    </span>

    <span wire:loading wire:target="confirmarAccionRevision" wire:key="revision-loading">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
