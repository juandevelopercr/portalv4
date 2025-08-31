<table>
    <thead>
        <tr>
            <th>{{ __('Clave') }}</th>
            <th>{{ __('Consecutivo') }}</th>
            <th>{{ __('Fecha') }}</th>
            <th>{{ __('Emisor') }}</th>
            <th>{{ __('Receptor') }}</th>
            <th>{{ __('Tipo') }}</th>
            <th>{{ __('Impuesto') }}</th>
            <th>{{ __('Descuento') }}</th>
            <th>{{ __('Total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->clave }}</td>
            <td>{{ $record->consecutivo }}</td>
            <td>
                {{ \Carbon\Carbon::parse($record->fecha_emision)
                ->locale('es')
                ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </td>
            <td>{{ $record->emisor_nombre }}</td>
            <td>{{ $record->receptor_nombre }}</td>
            <td>{{ $record->tipo_documento_description }}</td>
            <td>{{ Helper::formatDecimal($record->total_impuestos) }}</td>
            <td>{{ Helper::formatDecimal($record->total_descuentos) }}</td>
            <td>{{ Helper::formatDecimal($record->total_comprobante) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
