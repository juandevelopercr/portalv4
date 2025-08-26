<div>
    <div class="card">
        <h5 class="card-header pb-0 text-md-start text-left">{{ __('Status of Digital Certificates') }}</h5><br>
        <div class="card-datatable text-nowrap">
          <div class="dataTables_wrapper dt-bootstrap5 no-footer">
            <div class="row">
              <div class="col-md-2">
                <div class="ms-n2">
                  <div class="dataTables_length">

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-datatable table-responsive">
          <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="business-location-certificate-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>N° Serie</th>
                    <th>Válido Hasta</th>
                    <th>Estado</th>
                    <th>Próximo a Vencer</th>
                    <th>Cifrado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($locations as $loc)
                  <tr>
                      <td>{{ $loc['name'] }}</td>
                      <td>{{ $loc['serial'] ?? '-' }}</td>
                      <td>{{ $loc['valid_to'] ?? '-' }}</td>
                      <td>
                          @if ($loc['valid'])
                              <span class="badge bg-success">Válido</span>
                          @else
                              <span class="badge bg-danger">Inválido</span>
                          @endif
                      </td>
                      <td>
                          @if ($loc['expires_soon'])
                              <span class="badge bg-warning text-dark">Próximo a vencer</span>
                          @else
                              <span class="badge bg-secondary">OK</span>
                          @endif
                      </td>
                      <td>{{ $loc['cipher'] ?? '-' }}</td>
                      <td>
                          <button wire:click="toggleDetails({{ $loc['id'] }})" class="btn btn-sm btn-outline-info">
                              <i class="bx bx-chevron-down"></i>
                          </button>
                      </td>
                  </tr>

                  @if ($expanded[$loc['id']] ?? false)
                      <tr>
                          <td colspan="6" class="bg-light">
                              <div class="p-2">
                                  <strong>Nombre común (CN):</strong> {{ $loc['cn'] ?? '-' }} <br>
                                  <strong>Emisor:</strong> {{ $loc['issuer'] ?? '-' }} <br>
                                  <strong>Fecha de emisión:</strong> {{ $loc['valid_from'] ?? '-' }} <br>
                                  <strong>Fecha de expiración:</strong> {{ $loc['valid_to'] ?? '-' }}
                              </div>
                          </td>
                      </tr>
                  @endif
                @endforeach

            </tbody>
          </table>
        </div>
    </div>
</div>
