<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:x='urn:schemas-microsoft-com:office:excel'
      xmlns='http://www.w3.org/TR/REC-html40'>

<head>
  <meta http-equiv='Content-Type' content='text/html;charset=utf-8' />
    <style>
      .number {
        mso-number-format: "#,##0.00";
      }
    </style>

  {{--
  <!--[if gte mso 9]>
  <xml>
    <x:ExcelWorkbook>
      <x:ExcelWorksheets>
        <x:ExcelWorksheet>
          <x:Name>Exportar Hoja de Trabajo</x:Name>
          <x:WorksheetOptions>
            <x:DisplayGridlines/>
          </x:WorksheetOptions>
        </x:ExcelWorksheet>
      </x:ExcelWorksheets>
    </x:ExcelWorkbook>
  </xml>
  <![endif]-->
  --}}
</head>

<body class="kv-wrap">
  <table class="kv-grid-table table table-bordered kv-table-wrap">
    <thead>
      <tr>
        <th>#</th>
        <th>Cuenta</th>
        <th>Número</th>
        <th>Fecha</th>
        <th>Beneficiario</th>
        <th>Moneda</th>
        <th>Subtotal</th>
        <th>Impuesto</th>
        <th>Monto</th>
        <th>Tipo Movimiento</th>
        <th>Descripción</th>
        <th>Código Contable</th>
        <th>Centro De Costo</th>
        <th>Bloqueo de fondos</th>
        <th>Comprobante Pendiente</th>
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
          <td>{{ $dato->cuenta->nombre_cuenta ?? '' }}</td>
          <td>{{ $dato->numero }}</td>
          <td>{{ \Carbon\Carbon::parse($dato->fecha)->format('d-m-Y') }}</td>
          <td>{{ $dato->beneficiario }}</td>
          <td>{{ $dato->moneda->codigo ?? '' }}</td>
          <td class="number">{{ number_format($subtotal, 2) }}</td>
          <td class="number">{{ number_format($impuesto, 2) }}</td>
          <td class="number">{{ number_format($monto, 2) }}</td>
          <td>{{ $dato->tipo_movimiento }}</td>
          <td>{{ $dato->descripcion }}</td>
          <td>
            @php
            /*
            @if (count($detalles) <= 1 && method_exists($dato, 'getCentroCosto'))
              {{ $dato->getCentroCosto()['str_codigo_contable'] ?? '' }}
            @endif
            */
            @endphp
          </td>
          <td>
            @if (count($detalles) <= 1 && method_exists($dato, 'getCentroCosto'))
              {{ $dato->getCentroCosto()['str_centro_costo'] ?? '' }}
            @endif
          </td>
          <td>{{ $dato->bloqueo_fondos ? 'SI' : 'NO' }}</td>
          <td>{{ $dato->comprobante_pendiente ? 'SI' : 'NO' }}</td>
        </tr>


        @if (count($detalles) > 1)
          @foreach ($detalles as $key1 => $detalle)
            <tr>
              <td>{{ $key1 + 1 }}</td>
              <td>{{ $dato->cuenta->nombre_cuenta ?? '' }}</td>
              <td>{{ $dato->numero }}</td>
              <td></td>
              <td></td>
              <td>{{ $dato->moneda->codigo ?? '' }}</td>
              <td></td>
              <td></td>
              <td class="number">{{ number_format($detalle->amount ?? 0, 2) }}</td>
              <td>{{ $dato->tipo_movimiento }}</td>
              <td>{{ $dato->descripcion }}</td>
              <td>{{ optional($detalle->codigoContable)->codigo . ' ' . optional($detalle->codigoContable)->descrip }}</td>
              <td>{{ optional($detalle->centroCosto)->codigo . ' ' . optional($detalle->centroCosto)->descrip }}</td>
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
