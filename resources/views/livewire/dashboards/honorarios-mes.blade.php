<?php
use App\Models\User;
?>
<div>
  <div class="card mb-6">
    <div class="row mt-3 mb-6 px-3">
      <div class="col-md-2 flex-column-filter elect2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'department',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="department">{{ __('Department') }}</label>
        <select x-ref="select" id="department"
                class="select2 form-select @error('department') is-invalid @enderror">
          @if (in_array(session('current_role_name'), User::ROLES_ALL_DEPARTMENTS))                  
              <option value="">{{ __('Todos') }}</option>
          @endif
          @foreach ($this->departments as $department)
            <option value="{{ $department->id }}">{{ $department->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2 flex-column-filter select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'firstYear',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="firstYear">{{ __('Año inicial') }}</label>
        <select x-ref="select" id="firstYear"
                class="select2 form-select @error('firstYear') is-invalid @enderror">
          @foreach ($years as $year)
            <option value="{{ $year }}">{{ $year }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2 flex-column-filter select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'lastYear',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="lastYear">{{ __('Año final') }}</label>
        <select x-ref="select" id="lastYear"
                class="select2 form-select @error('lastYear') is-invalid @enderror">
          @foreach ($years as $year)
            <option value="{{ $year }}">{{ $year }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2 flex-column-filter select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'month',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="month">{{ __('Mes') }}</label>
        <select x-ref="select" id="month"
                class="select2 form-select @error('month') is-invalid @enderror">
          @foreach ($months as $month)
            <option value="{{ $month['id'] }}">{{ $month['name'] }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2 flex-column-filter select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'chartsPerRow',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="chartsPerRow">{{ __('Gráficos por fila') }}</label>
        <select x-ref="select" id="chartsPerRow"
                class="select2 form-select @error('chartsPerRow') is-invalid @enderror">
          <option value="1">1 por fila</option>
          <option value="2">2 por fila</option>
        </select>
      </div>

      <div class="col-md-2 flex-column-filter select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({ wireModelName: 'chartTheme', postUpdate: true })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="chartTheme">{{ __('Tema del gráfico') }}</label>
        <select x-ref="select" id="chartTheme"
                class="select2 form-select @error('chartTheme') is-invalid @enderror">
          <option value="fusion">Fusion (clásico)</option>
          <option value="gammel">Gammel (oscuro)</option>
          <option value="candy">Candy (colores vivos)</option>
          <option value="zune">Zune</option>
          <option value="umber">Umber (tierra)</option>
          <option value="ocean">Ocean (azul profundo)</option>
          <option value="carbon">Carbon (oscuro)</option>
          <option value="fint">Fint (corporativo)</option>
        </select>
      </div>
    </div>
  </div>

  <div class="row g-6 mb-6">
    <!-- Card Border Shadow -->
    <div class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-danger h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-dollar bx-lg"></i></span>
            </div>
            <h4 class="mb-0">${{ $this->total_honorario_iva }}</h4>
          </div>
          <p class="mb-2">Total Facturado Con IVA </p>
          <p class="mb-0">
            <span class="text-heading fw-medium me-2">{{ $this->monthName }} {{ $this->lastYear }}</span>
            <span class="text-muted"></span>
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success"><i class="bx bx-dollar bx-lg"></i></span>
            </div>
            <h4 class="mb-0">${{ $this->total_honorario }}</h4>
          </div>
          <p class="mb-2">Total honorarios Facturado</p>
          <p class="mb-0">
            <span class="text-heading fw-medium me-2">{{ $this->monthName }} {{ $this->lastYear }}</span>
            <span class="text-muted"></span>
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-dollar bx-lg"></i></span>
            </div>
            <h4 class="mb-0">${{ $this->total_gasto }}</h4>
          </div>
          <p class="mb-2">Total gastos facturado</p>
          <p class="mb-0">
            <span class="text-heading fw-medium me-2">{{ $this->monthName }} {{ $this->lastYear }}</span>
            <span class="text-muted"></span>
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-dollar bx-lg"></i></span>
            </div>
            <h4 class="mb-0">${{ $this->total_iva }}</h4>
          </div>
          <p class="mb-2">Total IVA</p>
          <p class="mb-0">
            <span class="text-heading fw-medium me-2">{{ $this->monthName }} {{ $this->lastYear }}</span>
            <span class="text-muted"></span>
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-info h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-info"><i class="bx bx-dollar bx-lg"></i></span>
            </div>
            <h4 class="mb-0">${{ $this->total_descuento }}</h4>
          </div>
          <p class="mb-2">Total Descuentos</p>
          <p class="mb-0">
            <span class="text-heading fw-medium me-2">{{ $this->monthName }} {{ $this->lastYear }}</span>
            <span class="text-muted"></span>
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-secondary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-data bx-lg"></i></span>
            </div>
            <h4 class="mb-0">{{ $this->total_transaction }}</h4>
          </div>
          <p class="mb-2">Cantidad de Facturas Realizadas</p>
          <p class="mb-0">
            <span class="text-heading fw-medium me-2">{{ $this->monthName }} {{ $this->lastYear }}</span>
            <span class="text-muted"></span>
          </p>
        </div>
      </div>
    </div>
    <!--/ Card Border Shadow -->
  </div>


  <div class="row">
    <div class="col-xl">
      <div class="card mb-6">
        <div class="card-body">
          <div class="col-md-12">
            <div class="row mt-3 mb-6 px-3">
              <div class="col-md-12">
                  {{--  @include('livewire.dashboards.tables.diferencia-firmas', ['tablaFirmas' => $this->dataDiferenciaFirmas]) --}}
              </div>
            </div>
            <div class="row mt-3 mb-6">
                @foreach (['kpi-chart-container', 'honorarios_mes_line', 'honorarios_mes_pie', 'centro_costo_stackedbar3d'] as $chartId)
                  <div class="{{ $chartsPerRow == 1 ? 'col-md-12' : 'col-md-6' }}">
                    @include('dashboard.components.chart-wrapper', ['chartId' => $chartId, ])
                  </div>
                @endforeach
            </div>
          </div>
        <div>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
  Livewire.on('updateFusionCharts', (data) => {
      console.log('🔁 Datos recibidos:', data);
      renderFusionCharts(data);
  });
</script>
@endpush


@php
/*
<table class="kv-grid-table table table-bordered table-striped kv-table-wrap">
<thead>
  <tr data-key="1">
    <th colspan="13" class="kv-align-center kv-align-middle kv-merged-header" align="center" style="text-align:center">TOTAL Honorarios: Todos los departamentos</th>
  </tr>
</thead>
<thead>
  <tr>
    <th class="kv-align-center kv-align-middle kv-merged-header" style="width:50px; text-align: center;" rowspan="2" data-col-seq="0">DOLARES</th>
    <th class="kv-align-center kv-align-middle" data-col-seq="2" style="text-align: center;"><a href="#" data-sort="codigo">ENE</a></th>
    <th class="kv-align-center kv-align-middle" data-col-seq="3" style="text-align: center;"><a href="#" data-sort="descripcion">FEB</a></th>
    <th class="kv-align-center kv-align-middle" data-col-seq="4" style="text-align: center;"><a href="#" data-sort="es_gasto">MAR</a></th>
    <th class="kv-align-center kv-align-middle" data-col-seq="5" style="text-align: center;"><a href="#" data-sort="unidad_medida_id">ABR</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="6" style="text-align: center;"><a href="#" data-sort="precio">MAY</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="7" style="text-align: center;"><a href="#" data-sort="precio">JUN</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="8" style="text-align: center;"><a href="#" data-sort="precio">JUL</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="9" style="text-align: center;"><a href="#" data-sort="precio">AGO</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="10" style="text-align: center;"><a href="#" data-sort="precio">SEP</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="11" style="text-align: center;"><a href="#" data-sort="precio">OCT</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="12" style="text-align: center;"><a href="#" data-sort="precio">NOV</a></th>
    <th class="sort-numerical kv-align-center kv-align-middle" data-col-seq="13" style="text-align: center;"><a href="#" data-sort="precio">DIC</a></th>
  </tr>
</thead>
<tbody>
  <tr data-key="1">
    <td class="kv-align-center kv-align-middle" style="width: 50px; mso-number-format: \@; text-align: center;" data-col-seq="0">2024</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      213.731,02</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      307.470,09</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      226.957,03</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      340.646,17</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      399.669,28</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      407.190,81</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      638.040,82</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      364.854,34</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      399.401,17</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      504.062,03</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      446.791,96</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      591.318,10</td>
      </tr>
  <tr data-key="1">
    <td class="kv-align-center kv-align-middle" style="width: 50px; mso-number-format: \@; text-align: center;" data-col-seq="0">2025</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      277.664,29</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      460.838,89</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      644.916,12</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      339.503,79</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      302.448,81</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      238.591,20</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      0,00</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      0,00</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      0,00</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      0,00</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      0,00</td>
        <td class="kv-align-center kv-align-middle" data-raw-value="0.00000" style="mso-number-format: \#\,\#\#0\.00; text-align: right;">$
      0,00</td>
      </tr>
</tbody>
</table>
*/
@endphp
