<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Active') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->name }}</td>
            <td>{{ $record->email ?? '' }}</td>
            <td>{{ $record->active ? __('Yes') : __('No') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>