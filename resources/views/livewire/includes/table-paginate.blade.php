<div class="dataTables_length d-flex align-items-center gap-2" id="DataTables_Table_0_length">
    <label class="mb-0">{{ __('Show') }}</label>

    <div class="fv-plugins-icon-container">
        <select id="perPage" class="form-select" wire:model.live="perPage">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="75">75</option>
            <option value="100">100</option>
            <option value="150">150</option>
            <option value="200">200</option>
        </select>
    </div>

    <label class="mb-0">{{ __('entries') }}</label>
</div>
