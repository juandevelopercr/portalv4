<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cheque</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        .kv-wrap { padding: 20px; }

        #cajalugar, #cajabeneficiario, #cajamonto, #cajadetalles, #cajamontoletras {
            position: absolute;
        }

        .container {
            position: relative;
            height: 100vh;
        }

        .center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .text-block {
            color: rgba(128, 128, 128, 0.7);
            background-color: rgba(0, 0, 0, 0.5);
            font-weight: bold;
            font-size: 48px;
            padding: 5px;
            transform: rotate(-45deg);
        }

        .sencillo {
            line-height: 1.3;
        }
    </style>
</head>
<body class="kv-wrap">
    @php
        $simbolo = $movimiento->moneda_id == 16 ? '₡' : '$';
    @endphp

    @if ($movimiento->status === 'ANULADO')
        <div class="container">
            <div class="center-text">
                <div class="text-block">ANULADO</div>
            </div>
        </div>
    @endif

    <div id="cajalugar" style="top: {{ $cuenta->lugar_fecha_y }}px; left: {{ $cuenta->lugar_fecha_x }}px">
        <p>
            @if($cuenta->mostrar_lugar)
                {{ $movimiento->lugar }}
            @endif
            {{ \Carbon\Carbon::parse($movimiento->fecha)->translatedFormat('d \d\e F \d\e Y') }}
        </p>
    </div>

    <div id="cajabeneficiario" style="top: {{ $cuenta->beneficiario_y }}px; left: {{ $cuenta->beneficiario_x }}px">
        <p>{{ $movimiento->beneficiario }}</p>
    </div>

    <div id="cajamonto" style="top: {{ $cuenta->monto_y }}px; left: {{ $cuenta->monto_x }}px">
        <p>{{ number_format($movimiento->total_general, 2) }}</p>
    </div>

    <div id="cajamontoletras" style="top: {{ $cuenta->monto_letras_y }}px; left: {{ $cuenta->monto_letras_x }}px; font-size: 12px;">
        <p>{{ $movimiento->monto_letras }}</p>
    </div>

    <div id="cajadetalles" style="top: {{ $cuenta->detalles_y }}px; left: {{ $cuenta->detalles_x }}px">
        <p class="sencillo">
            Cheque: {{ $movimiento->numero }}<br />
            Cuenta: {{ $cuenta->nombre_cuenta }} <br />
            Beneficiario: {{ $movimiento->beneficiario }} <br />

            @php
                $info = $movimiento->getCentroCosto();
            @endphp

            @if (count($movimiento->movimientosCentrosCostos ?? []) == 1)
                Código Contable: {{ $info['str_codigo_contable'] }}<br />
                Centro de Costo: {{ $info['str_centro_costo'] }}<br />
            @else
                Código Contable: Ver detalle<br />
                Centro de Costo: Ver detalle<br />
            @endif

            Lugar: {{ $movimiento->lugar }}<br />
            Fecha: {{ \Carbon\Carbon::parse($movimiento->fecha)->format('d-m-Y') }}<br />
            Monto: {{ $simbolo }}{{ number_format($movimiento->total_general, 2) }}<br />
            {{ $movimiento->descripcion }}
        </p>
    </div>
</body>
</html>
