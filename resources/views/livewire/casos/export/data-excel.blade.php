<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Número de caso') }}</th>
            <th>{{ __('Número de gestión') }}</th>
            <th>{{ __('Fecha') }}</th>
            <th>{{ __('Deudor') }}</th>
            <th>{{ __('Departament') }}</th>
            <th>{{ __('Abogado a cargo') }}</th>
            <th>{{ __('Banco') }}</th>
            <th>{{ __('Número de tomo') }}</th>
            <th>{{ __('Asiento de presentación') }}</th>
            <th>{{ __('Garantía / Acto') }}</th>
            <th>{{ __('Fecha de firma') }}</th>
            <th>{{ __('Fecha de entrega') }}</th>
            <th>{{ __('Fecha de inscripción') }}</th>
            <th>{{ __('Monto USD') }}</th>
            <th>{{ __('Monto CRC') }}</th>
            <th>{{ __('Estado') }}</th>
        </tr>
    </thead>
    <tbody>
        {{-- @foreach($records as $record) --}
        {{--@foreach ($query->cursor() as $key => $dato) --}}
        @foreach ($chunks as $chunk)
          @foreach ($chunk as $key => $record)
          <tr>
              <td>{{ $record->id }}</td>
              <td>{{ $record->numero }}</td>
              <td>{{ $record->numero_gestion ?? '' }}</td>
              <td>{{ $record->fecha_creacion ? \Carbon\Carbon::parse($record->fecha_creacion)->format('d/m/Y') : '' }}</td>
              <td>{{ $record->deudor ?? '' }}</td>
              <td>{{ $record->department ?? '' }}</td>
              <td>{{ $record->abogado_cargo ?? '' }}</td>
              <td>{{ $record->bank_name ?? '' }}</td>
              <td>{{ $record->numero_tomo ?? '' }}</td>
              <td>{{ $record->asiento_presentacion ?? '' }}</td>
              <td>{{ $record->garantia ?? '' }}</td>
              <td>{{ $record->fecha_firma ? \Carbon\Carbon::parse($record->fecha_firma)->format('d/m/Y') : '' }}</td>
              <td>{{ $record->fecha_entrega ? \Carbon\Carbon::parse($record->fecha_entrega)->format('d/m/Y') : '' }}</td>
              <td>{{ $record->fecha_inscripcion ? \Carbon\Carbon::parse($record->fecha_inscripcion)->format('d/m/Y') : '' }}</td>
              <td>{{ $record->garantia ?? '' }}</td>
              <td style="mso-number-format:'#,##0.00'; text-align:right;">{{ $record->monto_usd ?? 0}}</td>
              <td style="mso-number-format:'#,##0.00'; text-align:right;">{{ $record->monto_crc ?? 0 }}</td>
              <td>{{ $record->estado }}</td>
          </tr>
          @endforeach
        @endforeach
    </tbody>
</table>
