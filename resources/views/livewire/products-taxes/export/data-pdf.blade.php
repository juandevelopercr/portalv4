<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Health System Report') }}</title>
    <!-- Vincula Bootstrap 5 para el formato de PDF -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .img-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div class="container my-4">
        <!-- Título del reporte -->
        <h3 class="text-center mb-4">{{ __('Taxs') }}</h3>

        <!-- Fecha del reporte -->
        <p class="text-end"><strong>{{ __('Date') }}:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>

        <!-- Tabla de usuarios -->
        <table class="table table-bordered">
            <thead class="table-light">
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
    </div>
</body>

</html>