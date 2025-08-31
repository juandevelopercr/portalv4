<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Código del impuesto') }}</th>
            <th>{{ __('Código de la tarifa del Impuesto al Valor Agregado') }}</th>
            <th>{{ __('Tarifa del impuesto') }}</th>
            <th>{{ __('Código de impuesto OTRO') }}</th>
            <th>{{ __('Factor para Calculo IVA') }}</th>
            <th>{{ __('Cantidad de la unidad de medida a utilizar') }}</th>
            <th>{{ __('Percent') }}</th>
            <th>{{ __('Proporción') }}</th>
            <th>{{ __('Volumen por Unidad de Consumo') }}</th>
            <th>{{ __('Impuesto por Unidad') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->taxType->code . '-'. $record->taxType->name}}</td>
            <td>{{ $record->taxRate->code . '-'. $record->taxRate->name}}</td>
            <td>{{ $record->tax ?? '' }}</td>
            <td>{{ $record->factor_calculo_tax ?? '' }}</td>
            <td>{{ $record->count_unit_type ?? '' }}</td>
            <td>{{ $record->percent ?? '' }}</td>
            <td>{{ $record->proporcion ?? '' }}</td>
            <td>{{ $record->volumen_unidad_consumo ?? '' }}</td>
            <td>{{ $record->impuesto_unidad ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>