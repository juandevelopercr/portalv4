@php
    $textButton = __('Anular con nota');
@endphp

 <button type="button"
        class="btn btn-danger btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="beforeCreditNote"
        wire:loading.attr="disabled"
        wire:target="beforeCreditNote">
    <span wire:loading.remove wire:target="beforeCreditNote">
        <i class="bx bx-x-circle bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Anular con nota') }} @endif
        </span>
    </span>
    <span wire:loading wire:target="beforeCreditNote">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
