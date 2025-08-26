@php
    $textButton = __('Nota de débito');
@endphp

 <button type="button"
        class="btn btn-warning btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="beforeDebitNote"
        wire:loading.attr="disabled"
        wire:target="beforeDebitNote">
    <span wire:loading.remove wire:target="beforeDebitNote">
        <i class="bx bx-notepad bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Nota de débito') }} @endif
        </span>
    </span>
    <span wire:loading wire:target="beforeDebitNote">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
