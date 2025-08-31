@php
$btnAction = $btnAction ?? 'sendNotificacion'; // Valor por defecto
$textButton = __('Enviar notificación');
@endphp

<button type="button"
  class="btn btn-info btn-sm mx-1 d-flex align-items-center"
  wire:click.prevent="confirmarAccionNotificaion(null, @js($btnAction), '{{ __("¿Está seguro que desea enviar una notificación al abogado asignado?") }}',
        '{{ __("Después de confirmar, se enviará la notificación") }}', '{{ __("Si, proceder") }}')"
  wire:loading.attr="disabled"
  wire:target="confirmarAccionNotificaion">
  <span wire:loading.remove wire:target="confirmarAccionNotificaion">
    <i class="bx bx-envelope bx-send bx-flip-horizontal me-1"></i>
    <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Enviar notificación') }} @endif
    </span>
  </span>
  <span wire:loading wire:target="confirmarAccionNotificaion">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Cargando...') }}
  </span>
</button>
