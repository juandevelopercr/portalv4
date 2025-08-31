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
        <h3 class="text-center mb-4">{{ __('Proformas Report') }}</h3>

        <!-- Fecha del reporte -->
        <p class="text-end"><strong>{{ __('Date') }}:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>

        <!-- Tabla de usuarios -->
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Consecutive') }}</th>
                    <th>{{ __('No. Proforma') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Department') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Transaction Date') }}</th>
                    <th>{{ __('Application Date') }}</th>
                    <th>{{ __('Issuer') }}</th>
                    <th>{{ __('Accounting code') }}</th>
                    <th>{{ __('Bank') }}</th>
                    <th>{{ __('Currency') }}</th>
                    <th>{{ __('Proforma Type') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->consecutive }}</td>
                    <td>{{ $record->customer_name }}</td>
                    <td>{{ $record->department_name }}</td>
                    <td>{{ $record->user_name }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($record->transaction_date)
                        ->locale('es')
                        ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    </td>
                    <td>
                        @if (!is_null($record->fecha_solicitud_factura))
                        {{ \Carbon\Carbon::parse($record->fecha_solicitud_factura)
                        ->locale('es')
                        ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                        @endif
                    </td>
                    <td>{{ $record->issuer_name ?? '' }}</td>
                    <td>{{ $record->codigo_contable_code ?? '' }}</td>
                    <td>{{ $record->bank_name ?? '' }}</td>
                    <td>{{ $record->currency_name ?? '' }}</td>
                    <td>{{ $record->proforma_type }}</td>
                    <td>
                        @if ($record->type == 'PR')
                        {{ $record->proforma_status }}
                        @else
                        {{ $record->status }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
