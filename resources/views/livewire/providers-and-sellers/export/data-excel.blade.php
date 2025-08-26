<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Vendedor') }}</th>
            <th>{{ __('Fecha de venta') }}</th>
            <th>{{ __('Cliente') }}</th>
            <th>{{ __('Lugar de recogida') }}</th>
            <th>{{ __('Hora de recogida') }}</th>
            <th>{{ __('Número de pasajeros') }}</th>
            <th>{{ __('Cliente') }}</th>
            <th>{{ __('Fecha de servicio') }}</th>
            <th>{{ __('Número de factura') }}</th>
            <th>{{ __('Precio Rack') }}</th>
            <th>{{ __('Precio Neto') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->type }}</td>
            <td>{{ $record->status }}</td>
            <td>{{ $record->company_name }}</td>
            <td>{{ $record->date_service }}</td>
            <td>{{ $record->town_name }}</td>
            <td>{{ $record->pick_up }}</td>
            <td>{{ $record->destination }}</td>
            <td>{{ $record->bill_number }}</td>
            <td>{{ $record->pax }}</td>
            <td>{{ $record->customer_name }}</td>
            <td>{{ $record->rack_price }}</td>
            <td>{{ $record->net_cost }}</td>
            <td>{{ $record->others }}</td>
            <td>{{ $record->consecutive }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
