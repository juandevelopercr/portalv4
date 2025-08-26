<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Tipo') }}</th>
            <th>{{ __('Estado') }}</th>
            <th>{{ __('Compañia') }}</th>
            <th>{{ __('Fecha') }}</th>
            <th>{{ __('Ciudad') }}</th>
            <th>{{ __('Lugar de recogida') }}</th>
            <th>{{ __('Lugar de entrega') }}</th>
            <th>{{ __('# de Factura') }}</th>
            <th>{{ __('No. Pasajeros') }}</th>
            <th>{{ __('Nombre de cliente') }}</th>
            <th>{{ __('Precio Rack') }}</th>
            <th>{{ __('Costo Neto') }}</th>
            <th>{{ __('Comentarios') }}</th>
            <th>{{ __('Consecutivo') }}</th>
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
