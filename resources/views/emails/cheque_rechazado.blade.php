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
            background-color: #dc3545;
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
            background-color: #f8d7da;
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
        <h2>Cheques Rechazados</h2>
    </div>

    <div class="content">
        <p>Estimado equipo,</p>

        <p>Se ha identificado que los siguientes cheques han sido rechazados y requieren atención:</p>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cuenta</th>
                    <th>Beneficiario</th>
                    <th>Motivo de rechazo</th>
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
                        <td>{{ $mov->motivo_rechazo ?? 'N/D' }}</td>
                        <td>{{ number_format($mov->monto, 2, '.', ',') }}</td>
                        <td>{{ $mov->currency->code ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>Por favor, verifiquen y realicen las acciones necesarias.</p>

        <div class="footer">
          <p style="text-align:center">
						Este correo electrónico y cualquier anexo al mismo, contiene información de caracter confidencial
						exclusivamente dirigida a su destinatario o destinatarios. En el caso de haber recibido este correo electrónico
						por error, se ruega la destrucción del mismo.
					</p>
					<p style="text-align:center">
						Copyright © 2019 facturaelectronicacrc.com Powered By <a href="http://www.softwaresolutions.co.cr">softwaresolutions S.A</a><br />
						Todos los derechos reservados
					</p>
        </div>
    </div>

</body>
</html>
