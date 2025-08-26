<tr class="table-row-expanded">
  {{-- Celdas vacías para ocupar columnas 1 a 8 --}}
  @php
  /*
  @for ($i = 1; $i <= 9; $i++)
    <td class="border-0 p-0"></td>
  @endfor
  */
  @endphp
  <td colspan="8" class="p-0 border-0">

  {{-- Contenido expandido entre columna 9 y 14 --}}
  <td colspan="5" class="p-0 border-0">
    <div class="card shadow-sm w-100">
      {{-- ✅ Título centrado y compacto --}}
      <div class="card-header bg-primary text-white py-1 px-2 d-flex justify-content-center" style="font-size: 0.875rem;">
        <strong>Distribución de la factura</strong>
      </div>

      <div class="card-body p-2">
        {{-- Scroll si es necesario --}}
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0" style="font-size: 0.82rem;">
            <thead class="table-light text-nowrap">
              <tr style="line-height: 1.2rem;">
                <th class="text-center" style="padding: 0.3rem 0.4rem;">#</th>
                <th class="text-center" style="padding: 0.3rem 0.4rem;">Monto</th>
                <th class="text-center" style="padding: 0.3rem 0.4rem;">Tipo</th>
                <th class="text-center" style="padding: 0.3rem 0.4rem;">Centro de costo</th>
                <th class="text-center" style="padding: 0.3rem 0.4rem;">Código contable</th>
              </tr>
            </thead>
            <tbody>
              @php
                  $relation = $expandColumn['expand_condition'] ?? null;
                  $items = $relation && isset($record->{$relation}) ? $record->{$relation} : collect();
              @endphp

              @foreach ($items as $index => $detalle)
                <tr style="line-height: 1.2rem;">
                  <td class="text-center" style="padding: 0.3rem 0.4rem;">{{ $index + 1 }}</td>
                  <td class="text-end" style="padding: 0.3rem 0.4rem;">
                    {{ number_format($detalle->amount, 2, '.', ',') }}
                  </td>
                  <td style="padding: 0.3rem 0.4rem;">
                    {{ $record->tipo_movimiento }}
                  </td>
                  <td style="padding: 0.3rem 0.4rem;">
                    {{ $detalle->centroCosto?->codigo }} {{ $detalle->centroCosto?->descrip }}
                  </td>
                  <td style="padding: 0.3rem 0.4rem;">
                    {{ $detalle->codigoContable?->codigo }} {{ $detalle->codigoContable?->descrip }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </td>
</tr>
