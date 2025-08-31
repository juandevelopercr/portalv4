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
        <h3 class="text-center mb-4">{{ __('Honorarios / Timbres') }}</h3>

        <!-- Fecha del reporte -->
        <p class="text-end"><strong>{{ __('Date') }}:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>

        <!-- Tabla de usuarios -->
        <table class="table table-bordered">
            <thead class="table-light">
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
    </div>
</body>

</html>
