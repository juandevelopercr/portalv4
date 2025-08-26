<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Copia') }}</th>
            <th>{{ __('Enviar Rechazo') }}</th>
            <th>{{ __('Enviar Aprobado') }}</th>
            <th>{{ __('Active') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->nombre }}</td>
            <td>{{ $record->email }}</td>
            <td>{{ $record->copia }}</td>
            <td>{{ $record->enviar_rechazo }}</td>
            <td>{{ $record->enviar_aprobado }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
