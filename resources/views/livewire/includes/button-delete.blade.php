@php
    $textButton = __('Eliminar');
@endphp

 <button type="button"
        class="btn btn-danger btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="beforedelete"
        wire:loading.attr="disabled"
        wire:target="beforedelete">
    <span wire:loading.remove wire:target="beforedelete">
        <i class="bx bx-trash bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Eliminar') }} @endif
        </span>
    </span>
    <span wire:loading wire:target="beforedelete">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
