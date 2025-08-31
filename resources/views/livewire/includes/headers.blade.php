@foreach ($columns as $index => $column)
    @if ($column['visible'])
        @include('livewire.includes.table-sortable-th', [
            'index' => $index,
            'name' => $column['orderName'],
            'displayName' => $column['label'],
            'width' => $column['width'],
            'columnClass' => $column['columnClass'],
        ])
    @endif
@endforeach
