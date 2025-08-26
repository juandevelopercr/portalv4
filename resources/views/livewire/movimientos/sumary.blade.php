<div class="card mt-3">
  <div class="card-body p-3">
    <h6 class="mb-3 text-center">{{ __('Balance') }}</h6>
    <div class="table-responsive">
      <table class="table table-sm table-borderless mb-0" style="width: auto; margin-left: auto;">
        <tbody>
          <tr>
            <th class="text-start fw-bold pe-4">Saldo Inicial</th>
            <td class="text-end text-nowrap" style="width: 200px;">₡ {{ $saldo_inicial_colones }}</td>
            <td class="text-end text-nowrap" style="width: 200px;">$ {{ $saldo_inicial_dolares }}</td>
          </tr>
          <tr>
            <th class="text-start fw-bold pe-4">- Débitos</th>
            <td class="text-end text-nowrap" style="width: 200px;">₡ {{ $debitos_colones }}</td>
            <td class="text-end text-nowrap" style="width: 200px;">$ {{ $debitos_dolares }}</td>
          </tr>
          <tr>
            <th class="text-start fw-bold pe-4">- En Tránsito</th>
            <td class="text-end text-nowrap" style="width: 200px;">₡ {{ $en_transito_colones }}</td>
            <td class="text-end text-nowrap" style="width: 200px;">$ {{ $en_transito_dolares }}</td>
          </tr>
          <tr>
            <th class="text-start fw-bold pe-4">+ Créditos</th>
            <td class="text-end text-nowrap" style="width: 200px;">₡ {{ $creditos_colones }}</td>
            <td class="text-end text-nowrap" style="width: 200px;">$ {{ $creditos_dolares }}</td>
          </tr>
          <tr>
            <th class="text-start fw-bold pe-4 text-danger">- Bloqueado</th>
            <td class="text-end text-nowrap" style="width: 200px;">₡ {{ $bloqueado_colones }}</td>
            <td class="text-end text-nowrap" style="width: 200px;">$ {{ $bloqueado_dolares }}</td>
          </tr>
          <tr class="border-top">
            <th class="text-start fw-bold pe-4">Saldo Final</th>
            <td class="text-end text-nowrap fw-bold" style="width: 200px;">₡ {{ $saldo_final_colones }}</td>
            <td class="text-end text-nowrap fw-bold" style="width: 200px;">$ {{ $saldo_final_dolares }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
