<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .header {
            background-color: #004085;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #888;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Cheques Aprobados</h2>
    </div>

    <div class="content">
        <p>Estimado equipo,</p>

        <p>Se ha completado el proceso de aprobación de los siguientes cheques para su firma:</p>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cuenta</th>
                    <th>Beneficiario</th>
                    <th>Monto</th>
                    <th>Moneda</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movimientos as $index => $mov)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $mov->numero }}</td>
                        <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $mov->cuenta->nombre_cuenta ?? '' }}</td>
                        <td>{{ $mov->beneficiario }}</td>
                        <td>{{ number_format($mov->monto, 2, '.', ',') }}</td>
                        <td>{{ $mov->currency->code ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>Por favor procedan con la firma según corresponda.</p>

        <div class="footer">
            <p>Este correo ha sido enviado automáticamente por el .</p>
        </div>
    </div>

</body>
</html>
