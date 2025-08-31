<button type="button"
        class="btn btn-info btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="beforeclonar"
        wire:loading.attr="disabled"
        wire:target="beforeclonar">
    <span wire:loading.remove wire:target="beforeclonar">
        <i class="bx bx-copy bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">{{ __('Clonar') }}</span>
    </span>
    <span wire:loading wire:target="beforeclonar">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
