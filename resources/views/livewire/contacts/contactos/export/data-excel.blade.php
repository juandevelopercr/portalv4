<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Teléfono') }}</th>
            <th>{{ __('Ext') }}</th>
            <th>{{ __('Celular') }}</th>
            <th>{{ __('Grupo empresarial') }}</th>
            <th>{{ __('Sector') }}</th>
            <th>{{ __('Área de práctica') }}</th>
            <th>{{ __('Clasificación') }}</th>
            <th>{{ __('Tipo de cliente') }}</th>
            <th>{{ __('Fecha de nacimiento') }}</th>
            <th>{{ __('Año de ingreso') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->name }}</td>
            <td>{{ $record->email ?? '' }}</td>
            <td>{{ $record->telefono ?? '' }}</td>
            <td>{{ $record->ext ?? '' }}</td>
            <td>{{ $record->celular ? 'Si': 'No' }}</td>
            <td>{{ $record->grupoEmpresarial ? $record->grupoEmpresarial->name: '-' }}</td>
            <td>{{ $record->sector ? $record->sector->name: '-' }}</td>
            <td>{{ $record->areaPractica ? $record->areaPractica->name: '-' }}</td>
            <td>{{ $record->clasificacion }}</td>
            <td>{{ $record->tipo_cliente }}</td>
            <td>{{ $record->fecha_nacimiento }}</td>
            <td>{{ $record->anno_ingreso }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
