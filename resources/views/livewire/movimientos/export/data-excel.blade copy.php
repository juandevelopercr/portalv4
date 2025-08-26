<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:x='urn:schemas-microsoft-com:office:excel'
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
  <meta http-equiv='Content-Type' content='text/html;charset=utf-8' />
</head>

<body class="kv-wrap">
  <table class="kv-grid-table table table-bordered kv-table-wrap">
    <thead>
      <tr>
        <th style="width: 30px;">#</th>
        <th style="width: 250px;">Cuenta</th>
        <th style="width: 120px;">Número</th>
        <th style="width: 80px;">Fecha</th>
        <th style="width: 150px;">Beneficiario</th>
        <th style="width: 60px;">Moneda</th>
        <th style="width: 120px;">Subtotal</th>
        <th style="width: 120px;">Impuesto</th>
        <th style="width: 120px;">Monto</th>
        <th style="width: 120px;">Tipo Movimiento</th>
        <th style="width: 600px; white-space: normal; word-wrap: break-word;">Descripción</th>
        <th style="width: 400px; white-space: normal; word-wrap: break-word;">Código Contable</th>
        <th style="width: 200px; white-space: normal; word-wrap: break-word;">Centro De Costo</th>
        <th style="width: 100px;">Bloqueo de fondos</th>
        <th style="width: 100px;">Comprobante Pendiente</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($query->cursor() as $key => $dato)
        @php
          $detalles = [];
          if ($dato->status !== 'ANULADO') {
              $detalles = \App\Models\MovimientoCentroCosto::where('movimiento_id', $dato->id)->orderBy('id', 'ASC')->get();
          }
          $subtotal = $dato->monto;
          $impuesto = $dato->impuesto ?? 0;
          $monto = $dato->tipo_movimiento === 'DEPOSITO' ? $dato->monto : $dato->total_general;
        @endphp
        <tr>
          <td>{{ $key + 1 }}</td>
          <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ $dato->cuenta->nombre_cuenta ?? '' }}</td>
          <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ $dato->numero }}</td>
          <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ \Carbon\Carbon::parse($dato->fecha)->format('d-m-Y') }}</td>
          <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ $dato->beneficiario }}</td>
          <td style="mso-number-format: \@;text-align:center;vertical-align:middle;">{{ $dato->currency->code ?? '' }}</td>
          <td style="mso-number-format: \#\,\#\#0\.00;text-align:center;text-align:right;vertical-align:middle;">{{ number_format($subtotal, 2) }}</td>
          <td style="mso-number-format: \#\,\#\#0\.00;text-align:center;text-align:right;vertical-align:middle;">{{ number_format($impuesto, 2) }}</td>
          <td style="mso-number-format: \#\,\#\#0\.00;text-align:center;text-align:right;vertical-align:middle;">{{ number_format($monto, 2) }}</td>
          <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ $dato->tipo_movimiento }}</td>
          <td style="width:600px; mso-width-source:auto; mso-width-alt:10000; word-wrap:break-word; white-space:normal;">
            {{ $dato->descripcion }}
          </td>
          <td style="width:400px; mso-width-source:auto; mso-width-alt:10000; word-wrap:break-word; white-space:normal;">
            @if (count($detalles) <= 1 && method_exists($dato, 'getCentroCosto'))
              {{ $dato->getCentroCosto()['str_codigo_contable'] ?? '' }}
            @endif
          </td>
          <td style="width:200px; mso-width-source:auto; mso-width-alt:10000; word-wrap:break-word; white-space:normal;">
            @if (count($detalles) <= 1 && method_exists($dato, 'getCentroCosto'))
              {{ $dato->getCentroCosto()['str_centro_costo'] ?? '' }}
            @endif
          </td>
          <td style="mso-number-format: \@;text-align:center;vertical-align:middle;">{{ $dato->bloqueo_fondos ? 'SI' : 'NO' }}</td>
          <td style="mso-number-format: \@;text-align:center;vertical-align:middle;">{{ $dato->comprobante_pendiente ? 'SI' : 'NO' }}</td>
        </tr>


        @if (count($detalles) > 1)
          @foreach ($detalles as $key1 => $detalle)
            <tr>
              <td>{{ $key1 + 1 }}</td>
              <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ $dato->cuenta->nombre_cuenta ?? '' }}</td>
              <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ $dato->numero }}</td>
              <td></td>
              <td></td>
              <td style="mso-number-format: \@;text-align:center;vertical-align:middle;">{{ $dato->moneda->codigo ?? '' }}</td>
              <td></td>
              <td></td>
              <td style="mso-number-format: \#\,\#\#0\.00;text-align:center;text-align:right;vertical-align:middle;">{{ number_format($detalle->amount ?? 0, 2) }}</td>
              <td style="mso-number-format: \@;text-align:center;text-align:left;vertical-align:middle;">{{ $dato->tipo_movimiento }}</td>
              <td style="width:600px; mso-width-source:auto; mso-width-alt:10000; word-wrap:break-word; white-space:normal;">{{ $dato->descripcion }}</td>
              <td style="width:400px; mso-width-source:auto; mso-width-alt:10000; word-wrap:break-word; white-space:normal;">{{ optional($detalle->codigoContable)->codigo . ' ' . optional($detalle->codigoContable)->descrip }}</td>
              <td style="width:400px; mso-width-source:auto; mso-width-alt:10000; word-wrap:break-word; white-space:normal;">{{ optional($detalle->centroCosto)->codigo . ' ' . optional($detalle->centroCosto)->descrip }}</td>
              <td></td>
              <td></td>
            </tr>
          @endforeach
        @endif
      @endforeach
    </tbody>
  </table>
</body>
</html>
