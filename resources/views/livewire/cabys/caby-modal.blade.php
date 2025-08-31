<div>
    @if($modalCabysOpen)
    <div class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Select Caby Code') }}</h5>
                    <button type="button" class="btn-close" wire:click="closeCabysModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('livewire.cabys.datatable')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeCabysModal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>