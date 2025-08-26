<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Base') }}</th>
            <th>{{ __('Por Cada') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Order') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->base }}</td>
            <td>{{ $record->tipo }}</td>
            <td>{{ $record->orden }}</td>
        </tr>
        @endforeach
    </tbody>
</table>