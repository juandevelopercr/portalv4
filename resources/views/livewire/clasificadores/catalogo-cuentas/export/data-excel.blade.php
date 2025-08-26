<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Favorite') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->codigo }}</td>
            <td>{{ $record->descrip }}</td>
            <td>{{ $record->favorite }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
