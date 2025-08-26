<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Numero de cuenta') }}</th>
            <th>{{ __('Cuenta') }}</th>
            <th>{{ __('Moneda') }}</th>
            <th>{{ __('Balance') }}</th>
            <th>{{ __('Saldo') }}</th>
            <th>{{ __('Último cheque') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->numero_cuenta }}</td>
            <td>{{ $record->nombre_cuenta }}</td>
            <td>{{ $record->currency->code }}</td>
            <td>{{ $record->balance }}</td>
            <td>{{ $record->saldo }}</td>
            <td>{{ $record->ultimo_cheque }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
