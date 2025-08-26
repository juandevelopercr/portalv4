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
        <h1>CONSORTIUM LEGAL - COSTA RICA</h1>
        <h2>Notificación de caso asignado</h2>
    </div>

    <div class="content">
        <p>Estimado(a),</p>

        <p>Se le notifica que se la ha asignado un nuevo caso:</p>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Número</th>
                    <th>Número de gestión</th>
                    <th>Deudor</th>
                    <th>Banco</th>
                    <th>Tipo de garantía</th>
                    <th>Moneda</th>
                </tr>
            </thead>
            <tbody>
              <tr>
                  <td>1</td>
                  <td>{{ $caso->numero }}</td>
                  <td>{{ $caso->numero_gestion }}</td>
                  <td>{{ $caso->deudor ?? '' }}</td>
                  <td>{{ optional($caso->bank)?->name ?? '-'}}</td>
                  <td>{{ optional($caso->garantia)?->name ?? '-' }}</td>
                  <td>{{ optional($caso->currency)?->code ?? '-' }}</td>
              </tr>
            </tbody>
        </table>

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
