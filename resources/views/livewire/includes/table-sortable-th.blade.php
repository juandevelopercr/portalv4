<th wire:key="sort-@if(isset($index)) {{ $index }} @endif"  scope="col" class="px-4 py-3 icon-hover text-nowrap" @if(!empty($name)) wire:click="setSortBy('{{ $name }}')" @endif
    @isset($width) {{ 'style=width:' . $width . ';' }} @endisset>
    <div class="d-flex justify-content-between align-items-center">
        <span>{{ $displayName }}</span>
        @if (!empty($name))
        <i class="d-none d-md-inline d-print-none ms-2">
            @if ($sortBy !== $name)
            <i class="bx bx-collapse-vertical sort-icon"></i>
            @elseif($sortDir === 'ASC')
            <i class="bx bx-chevron-up sort-icon"></i>
            @else
            <i class="bx bx-chevron-down sort-icon"></i>
            @endif
        </i>
        @endif
    </div>
</th>
