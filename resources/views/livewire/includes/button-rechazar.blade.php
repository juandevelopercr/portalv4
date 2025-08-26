@php
    $btnAction = $btnAction ?? 'rechazar'; // Valor por defecto
    $textButton = __('Rechazar');
@endphp

<button type="button"
    class="btn btn-danger btn-sm mx-1 d-flex align-items-center"
    wire:click.prevent="confirmarAccionRechazo(null, @js($btnAction), '{{ __("¿Está seguro que desea rechazar este cheque?") }}', 
    '{{ __("Después de confirmar, el cheque será rechazado") }}', '{{ __("Sí, proceder") }}')"
    wire:loading.attr="disabled"
    wire:target="confirmarAccionRechazo"
    wire:key="boton-rechazar">

    <span wire:loading.remove wire:target="confirmarAccionRechazo" wire:key="rechazar-label">
        <i class="bx bx-block bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">
            {{ $textButton ?? __('Rechazar') }}
        </span>
    </span>

    <span wire:loading wire:target="confirmarAccionRechazo" wire:key="rechazar-loading">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
