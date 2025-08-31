<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->code }}</td>
            <td>{{ $record->name }}</td>
            <td>{{ $record->email }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
