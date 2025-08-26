@php
    $btnAction = $btnAction ?? 'anular'; // Valor por defecto
    $textButton = __('Anular');
@endphp

 <button type="button"
        class="btn btn-danger btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="confirmarAccion(null, @js($btnAction), '{{ __("¿Está seguro que desea anular este reqgistro?") }}', 
        '{{ __("Después de confirmar el registro será anulado") }}', '{{ __("Yes, proceed") }}')"
        wire:loading.attr="disabled"
        wire:target="confirmarAccion">
    <span wire:loading.remove wire:target="confirmarAccion">
        <i class="bx bx-x-circle bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Anular') }} @endif
        </span>
    </span>
    <span wire:loading wire:target="confirmarAccion">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
