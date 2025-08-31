<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Bank') }}</th>
            <th>{{ __('Desde') }}</th>
            <th>{{ __('Hasta') }}</th>
            <th>{{ __('Porcentaje') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Order') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->bank_name }}</td>
            <td>{{ $record->desde ?? '' }}</td>
            <td>{{ $record->hasta ?? '' }}</td>
            <td>{{ $record->porcentaje ?? '' }}</td>
            <td>{{ $record->tipo ?? '' }}</td>
            <td>{{ $record->orden ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>