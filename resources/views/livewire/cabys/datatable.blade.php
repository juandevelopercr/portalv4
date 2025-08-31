<div>
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive">
            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                <div class="row">
                    <div class="col-md-2">
                        <div class="ms-n2">
                            @include('livewire.includes.table-paginate')                
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div
                            class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0">
                            <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                <label>{{ __('Search') }}:
                                    <input type="search" wire:model.live.debounce.300ms="search" id="textsearch"
                                        class="form-control" placeholder="" aria-controls="DataTables_Table_0">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="caby-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="control sorting_disabled dtr-hidden" rowspan="1" colspan="1"
                                style="width: 0px; display: none;" aria-label="">
                            </th>
                            @include('livewire.includes.table-sortable-th',[
                            'name' => 'code',
                            'displayName' => __('Code')
                            ])
                            @include('livewire.includes.table-sortable-th',[
                            'name' => 'description_service',
                            'displayName' => __('Name')
                            ])
                            @include('livewire.includes.table-sortable-th',[
                            'name' => 'tax',
                            'displayName' => __('Tax')
                            ])
                            @include('livewire.includes.table-sortable-th',[
                            'name' => 'health_systems.active',
                            'displayName' => __('Active')
                            ])
                            <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 102px;"
                                aria-label="Actions">{{
                                __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cabys as $record)
                        <tr wire:key='{{ $record->id }}' class="{{ $loop->odd ? 'odd' : 'even' }}">
                            <td class="control" style="display: none;" tabindex="0"></td>
                            <td>{{ isset($record->code) ? $record->code: '' }}</td>
                            <td>{{ isset($record->description_service) ? $record->description_service: '' }}</td>
                            <td>{{ isset($record->tax) ? $record->tax * 100 . '%': '' }}</td>
                            <td>
                                @if($record->active)
                                <i class="bx fs-4 bx-check-shield text-success" title="Activo"></i>
                                @else
                                <i class="bx fs-4 bx-shield-x text-danger" title="Inactivo"></i>
                                @endif
                            </td>
                            <td>
                                <div class="action-icons d-flex justify-content-center align-items-center">
                                    <button wire:click="selectCabyCode('{{ $record->code }}')"
                                        class="btn btn-primary btn-sm">
                                        {{ __('Select') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row overflow-y-scroll" wire:scroll>
                    {{ $cabys->links(data: ['scrollTo' => false]) }}
                </div>

            </div>
            <div style="width: 1%;"></div>
        </div>
    </div>
</div>
<!--/ DataTable with Buttons -->
