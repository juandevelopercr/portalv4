@php
    $btnAction = $btnAction ?? 'aprobar'; // Valor por defecto
    $textButton = __('Enviar aprobaciones / rechazos');
@endphp

 <button type="button"
        class="btn btn-success btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="confirmarAccionAprobar(null, @js($btnAction), '{{ __("¿Está seguro que desea enviar el listado de cheques aprobados y rechazados?") }}', 
        '{{ __("Después de confirmar, se enviará el listado de cheques aprobados y rechazados") }}', '{{ __("Si, proceder") }}')"
        wire:loading.attr="disabled"
        wire:target="confirmarAccionAprobar">
    <span wire:loading.remove wire:target="confirmarAccionAprobar">
        <i class="bx bx-envelope bx-send bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Enviar aprobaciones / rechazos') }} @endif
        </span>
    </span>
    <span wire:loading wire:target="confirmarAccionAprobar">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
