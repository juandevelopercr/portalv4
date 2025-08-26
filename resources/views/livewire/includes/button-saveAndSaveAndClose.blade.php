@php
    $submitMethod = $action === 'edit' ? 'update' : 'store';
    $submitAndCloseMethod = $action === 'edit' ? 'updateAndClose' : 'storeAndClose';
    $loadingLabel = $action === 'edit' ? __('Updating...') : __('Saving...');
@endphp

<!-- Botón Submit (versión corregida) -->
<button type="button"
        class="btn btn-primary data-submit me-sm-4 me-1 mt-5"
        wire:click="{{ $action === 'edit' ? 'update' : 'store' }}"
        wire:loading.attr="disabled"
        wire:target="update,store">
    <span wire:loading.remove wire:target="update,store">
        <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Save') }}
    </span>
    <span wire:loading wire:target="update,store">
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Saving...') }}
    </span>
</button>

<!-- Botón Guardar y Cerrar -->
<button type="button"
        class="btn btn-success data-submit me-sm-4 me-1 mt-5"
        wire:click="{{ $action === 'edit' ? 'updateAndClose' : 'storeAndClose' }}"
        wire:loading.attr="disabled"
        wire:target="updateAndClose,storeAndClose">
    <span wire:loading.remove wire:target="updateAndClose,storeAndClose">
        <i class="tf-icons bx bx-save bx-18px me-2"></i> {{ __('Save and Close') }}
    </span>
    <span wire:loading wire:target="updateAndClose,storeAndClose">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i> {{ $action === 'edit' ? __('Updating...') : __('Saving...') }}
    </span>
</button>
