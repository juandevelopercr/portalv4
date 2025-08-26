<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Description') }}</th>
            <th>{{ __('Base') }}</th>
            <th>{{ __('Bank') }}</th>
            <th>{{ __('Por Cada') }}</th>
            <th>{{ __('Timbre Abogados Bienes Inmuebles') }}</th>
            <th>{{ __('Timbre Abogados Bienes Muebles') }}</th>
            <th>{{ __('Tabla Honorarios') }}</th>
            <th>{{ __('Fijo') }}</th>
            <th>{{ __('Porcentaje') }}</th>
            <th>{{ __('Descuento') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->description }}</td>
            <td>{{ $record->base ?? '' }}</td>
            <td>{{ $record->bank_name ?? '' }}</td>
            <td>{{ $record->porcada ?? '' }}</td>
            <td>{{ $record->tabla_abogado_inscripciones ? 'Si': 'No' }}</td>
            <td>{{ $record->tabla_abogado_traspasos ? 'Si': 'No' }}</td>
            <td>{{ $record->honorario_id ? 'Si': 'No' }}</td>
            <td>{{ $record->fijo ? 'Si': 'No' }}</td>
            <td>{{ $record->porciento ? 'Si': 'No' }}</td>
            <td>{{ $record->descuento_timbre ? 'Si': 'No' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>