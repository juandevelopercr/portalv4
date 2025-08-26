<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Initials') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Break down service') }}</th>
            <th>{{ __('Active') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->name }}</td>
            <td>{{ $record->iniciales }}</td>
            <td>{{ $record->email }}</td>
            <td>{{ $record->desglosar_servicio }}</td>
            <td>{{ $record->active }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
