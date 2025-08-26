<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Description') }}</th>
            <th>{{ __('Active') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->code }}</td>
            <td>{{ $record->name ?? '' }}</td>
            <td>{{ $record->description ?? '' }}</td>
            <td>{{ $record->active ? __('Yes') : __('No') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>