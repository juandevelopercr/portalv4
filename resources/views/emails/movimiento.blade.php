<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Factura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #0d6efd;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .table-container {
            margin-top: 20px;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        table {
            border-collapse: collapse;
            width: 60%;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
            font-size: 14px;
        }

        th {
            background-color: #f1f3f5;
            font-weight: bold;
        }

        .note {
            margin-top: 20px;
            font-size: 13px;
            text-align: center;
            color: #555;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Consortium - {{ now()->format('d/m/Y') }}</h2>
        <p>Agradecemos emitir y enviarnos su factura electrónica con los siguientes detalles</p>
    </div>

    <div class="content">
        <div class="table-container">
            <table>
                <tr>
                    <th>A nombre de</th>
                    <td>{{ $data['movimiento']->cuenta->perosna_sociedad }}</td>
                </tr>
                <tr>
                    <th>Monto</th>
                    <td>{{ $data['movimiento']->currency->symbol }} {{ \Helper::formatDecimal($data['movimiento']->monto) }}</td>
                </tr>
                <tr>
                    <th>IVA</th>
                    <td>{{ $data['movimiento']->currency->symbol }} {{ \Helper::formatDecimal($data['movimiento']->impuesto) }}</td>
                </tr>
                <tr>
                    <th>Total general</th>
                    <td>{{ $data['movimiento']->currency->symbol }} {{ \Helper::formatDecimal($data['movimiento']->total_general) }}</td>
                </tr>
                <tr>
                    <th>Concepto o detalle</th>
                    <td>{{ $data['concepto'] }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        Por favor enviar los 3 archivos (1 PDF y 2 XML) a:<br>
                        <strong>facturae.cr@consortiumlegal.com</strong>
                    </td>
                </tr>
            </table>
        </div>

        <div class="note">
            Si tiene alguna duda sobre esta solicitud, no dude en contactarnos.
        </div>
    </div>

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

</body>
</html>
