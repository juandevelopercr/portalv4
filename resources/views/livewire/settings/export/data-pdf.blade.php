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
        <h3 class="text-center mb-4">{{ __('Bank Honorarios') }}</h3>

        <!-- Fecha del reporte -->
        <p class="text-end"><strong>{{ __('Date') }}:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>

        <!-- Tabla de usuarios -->
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>{{ __('Bank') }}</th>
                    <th>{{ __('Desde') }}</th>
                    <th>{{ __('Hasta') }}</th>
                    <th>{{ __('Porcentaje') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Order') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->bank_name }}</td>
                    <td>{{ $record->desde ?? '' }}</td>
                    <td>{{ $record->hasta ?? '' }}</td>
                    <td>{{ $record->porcentaje ?? '' }}</td>
                    <td>{{ $record->tipo ?? '' }}</td>
                    <td>{{ $record->orden ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>