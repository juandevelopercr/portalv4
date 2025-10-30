<?php
use App\Models\User;
?>
<div>
    <div class="card mb-6">
      <h5 class="card-header pb-0 text-md-start text-center">{{ __('Volumen por banco') }}</h5>
      <div class="card-datatable text-nowrap">
        <div class="dataTables_wrapper dt-bootstrap5 no-footer">
          <div class="row mt-3 mb-6">
              <div class="col-md-3 flex-column-filter elect2-primary fv-plugins-icon-container"
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
                    wireModelName: 'year',
                    postUpdate: true
                  })"
                  x-init="init($refs.select)"
                  wire:ignore>
                <label class="form-label" for="year">{{ __('Año') }}</label>
                <select x-ref="select" id="year"
                        class="select2 form-select @error('year') is-invalid @enderror">
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
          <div class="row mt-3 mb-6 px-3">
            <div class="col-md-12">
                {{--  @include('livewire.dashboards.tables.diferencia-firmas', ['tablaFirmas' => $this->dataDiferenciaFirmas]) --}}
            </div>
          </div>
          <div class="row mt-3 mb-6">
              @foreach (['volumen_line', 'formalizaciones-mes', 'formalizaciones-year'] as $chartId)
                <div class="{{ $chartsPerRow == 1 ? 'col-md-12' : 'col-md-6' }}">
                  @include('dashboard.components.chart-wrapper', ['chartId' => $chartId])
                </div>
              @endforeach
          </div>
        </div>
      <div>
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
