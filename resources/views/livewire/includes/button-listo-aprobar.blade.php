@php
    $btnAction = $btnAction ?? 'listoAprobar'; // Valor por defecto
    $textButton = __('Listo para aprobar');
@endphp

<button type="button"
    class="btn btn-info btn-sm mx-1 d-flex align-items-center"
    wire:click.prevent="confirmarAccionListoAprobar(null, @js($btnAction), '{{ __("¿Está seguro que desea establecer el cheque como listo para aprobar?") }}', 
    '{{ __("Después de confirmar, el cheque será marcado como listo para aprobar") }}', '{{ __("Sí, proceder") }}')"
    wire:loading.attr="disabled"
    wire:target="confirmarAccionListoAprobar"
    wire:key="boton-listo-aprobar">

    <span wire:loading.remove wire:target="confirmarAccionListoAprobar" wire:key="listo-aprobar-label">
        <i class="bx bx-check"></i>
        <span class="d-none d-sm-inline-block">
            {{ $textButton ?? __('Listo para aprobar') }}
        </span>
    </span>

    <span wire:loading wire:target="confirmarAccionListoAprobar" wire:key="listo-aprobar-loading">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
