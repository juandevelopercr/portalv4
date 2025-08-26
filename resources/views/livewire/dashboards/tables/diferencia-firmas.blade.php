<table class="table table-bordered table-striped">
  <thead class="text-center">
    <tr>
      <th>Año</th>
      @foreach(['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'] as $mes)
        <th>{{ $mes }}</th>
      @endforeach
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    @foreach($tablaFirmas as $i => $fila)
      <tr>
        <td class="text-center"><strong>{{ $fila['year'] }}</strong></td>
        @for ($m = 1; $m <= 12; $m++)
          <td class="text-end">{{ $fila['months'][$m] }}</td>
        @endfor
        <td class="text-end fw-bold">{{ $fila['total'] }}</td>
      </tr>
      @if($i > 0)
        @php
          $diferencia = $fila['total'] - $tablaFirmas[$i - 1]['total'];
        @endphp
        <tr>
          <td colspan="14" class="text-center text-muted" style="font-style: italic;">
            Diferencia con {{ $tablaFirmas[$i - 1]['year'] }}:
            <strong class="{{ $diferencia >= 0 ? 'text-success' : 'text-danger' }}">
              {{ $diferencia >= 0 ? '+' : '' }}{{ $diferencia }}
            </strong>
          </td>
        </tr>
      @endif
    @endforeach
  </tbody>
</table>
