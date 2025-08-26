<?php

namespace App\Livewire\Dashboards;

use App\Models\Caso;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Firmas extends Component
{
  public $department;
  public $departments = [];
  public $years = [];      // Lista de años disponibles para el filtro
  public $firstYear;       // Año inicial del filtro (por defecto, año anterior al actual)
  public $lastYear;        // Año final del filtro (por defecto, año actual)
  public $chartTheme = 'zune'; // Valor por defecto
  public $chartsPerRow = 1; // por defecto 1 gráficos por fila
  public $dataDiferenciaFirmas = [];
  public $departmentName;

  public function mount()
  {
    // Obtener departamentos y bancos de la sesión
    $departments = Session::get('current_department', []);

    $this->departments = Department::where('active', 1)
      ->whereIn('id', $departments)
      ->orderBy('name', 'ASC')
      ->get();

    // Obtener años únicos desde la columna created_at
    $this->years = Caso::select(DB::raw('YEAR(fecha_firma) as year'))
      ->whereNotNull('fecha_firma')
      ->distinct()
      ->orderBy('year', 'asc')
      ->pluck('year')
      ->toArray();

    // Año actual y anterior como valores por defecto
    $currentYear = Carbon::now()->year;
    $this->firstYear = $currentYear - 1;
    $this->lastYear = $currentYear;

    $this->js(<<<JS
        Livewire.dispatch('updateFusionCharts', {$this->getChartDataJson()});
    JS);
  }

  public function updated($property)
  {
    if (in_array($property, ['department', 'firstYear', 'lastYear', 'chartTheme'])) {
      $this->js(<<<JS
          Livewire.dispatch('updateFusionCharts', {$this->getChartDataJson()});
      JS);
    }
  }

  public function getChartDataJson()
  {
    return json_encode([
      ...$this->getChartData(),
      'theme' => $this->chartTheme
    ]);
  }

  public function getChartData(): array
  {
    $this->departmentName = Department::find($this->department)?->name ?? '';

    $this->dataDiferenciaFirmas = $this->getDataDiferenciaFirmas();

    $mscolumn3d = $this->getDataMsColumn3d();
    $line = $this->getDataLine();
    $bar = $this->getDataBar();
    //$pie = $this->getDataPie();
    //$area = $this->getDataArea();

    //dd($mscolumn2d);

    return [
      'mscolumn3d' => $mscolumn3d,
      'line' => $line,
      'bar' => $bar
      //'pie' => $pie,
      //'area' => $area
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.firmas');
  }


  public function getEstructuraGraficoLine($lineDataRaw)
  {
    $months = [
      'Ene',
      'Feb',
      'Mar',
      'Abr',
      'May',
      'Jun',
      'Jul',
      'Ago',
      'Sep',
      'Oct',
      'Nov',
      'Dic'
    ];

    $years = range($this->firstYear, $this->lastYear);
    $grouped = [];

    foreach ($years as $year) {
      $grouped[$year] = array_fill(0, 12, 0);
    }

    foreach ($lineDataRaw as $row) {
      $monthIndex = $row->month - 1;
      if (isset($grouped[$row->year][$monthIndex])) {
        $grouped[$row->year][$monthIndex] = $row->total;
      }
    }

    $categories = [
      ['category' => array_map(fn($month) => ['label' => $month], $months)]
    ];

    $dataset = [];
    foreach ($grouped as $year => $monthlyData) {
      $dataset[] = [
        'seriesname' => (string) $year,
        'data' => array_map(fn($val) => ['value' => $val], $monthlyData)
      ];
    }

    return [
      'categories' => $categories,
      'dataset' => $dataset
    ];
  }

  public function getEstructuraGraficoMscolumn3d($firmasMensuales)
  {
    // Agrupar resultados por año
    $firmasPorAno = $firmasMensuales->groupBy('year');

    // Generar etiquetas de meses (ENE, FEB, ..., DIC)
    $monthLabels = collect(range(1, 12))->map(fn($m) => ['label' => strtoupper(now()->startOfYear()->addMonths($m - 1)->isoFormat('MMM'))]);

    // Estructura para FusionCharts
    $data = [
      'categories' => [['category' => $monthLabels->toArray()]],
      'dataset' => []
    ];

    // Rellenar dataset por cada año
    foreach ($firmasPorAno as $year => $registros) {
      // Mapa por mes
      $dataPorMes = array_fill(1, 12, 0); // Llena todos los meses con 0
      foreach ($registros as $r) {
        $dataPorMes[(int) $r->month] = (int) $r->total;
      }

      $data['dataset'][] = [
        'seriesname' => (string) $year,
        'data' => collect($dataPorMes)->map(fn($val) => ['value' => $val])->values()->toArray()
      ];
    }

    return $data;
  }

  public function getDataDiferenciaFirmas()
  {
    $casos = Caso::selectRaw('YEAR(fecha_firma) as year, MONTH(fecha_firma) as month, COUNT(*) as total')
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '>=', $this->firstYear)
      ->whereYear('fecha_firma', '<=', $this->lastYear);

    // Filtrar por departamento
    if (!empty($this->department)) {
      $casos->where('department_id', $this->department);
    } else {
      $departmentIds = collect($this->departments)->pluck('id')->toArray();
      if (!empty($departmentIds)) {
        $casos->whereIn('department_id', $departmentIds);
      }
    }

    $casos = $casos->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();

    // Organizar por año
    $dataByYear = [];
    foreach ($casos as $row) {
      $dataByYear[$row->year][$row->month] = $row->total;
    }

    // Calcular totales por año
    $tablaFirmas = [];
    foreach ($dataByYear as $year => $months) {
      $row = [
        'year' => $year,
        'months' => [],
        'total' => 0
      ];
      for ($i = 1; $i <= 12; $i++) {
        $value = $months[$i] ?? 0;
        $row['months'][$i] = $value;
        $row['total'] += $value;
      }
      $tablaFirmas[] = $row;
    }

    return $tablaFirmas;
  }

  // nuevas funciones
  public function getDataMsColumn3d(): array
  {
    $query = Caso::select(
      DB::raw('YEAR(fecha_firma) AS year'),
      DB::raw('MONTH(fecha_firma) AS month'),
      DB::raw('COUNT(*) AS total')
    )
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '>=', $this->firstYear)
      ->whereYear('fecha_firma', '<=', $this->lastYear);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy(DB::raw('YEAR(fecha_firma), MONTH(fecha_firma)'))
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $estructura = $this->getEstructuraGraficoMscolumn3d($data);

    // Calcula totales por año
    $totalesPorAnio = [];
    foreach ($estructura['dataset'] as $serie) {
      $totalesPorAnio[$serie['seriesname']] = collect($serie['data'])->sum(fn($d) => (int) $d['value']);
    }

    ksort($totalesPorAnio);
    $keys = array_keys($totalesPorAnio);

    $diferencia = null;
    if (count($keys) >= 2) {
      $ultimo = end($keys);
      $penultimo = prev($keys);
      $diferencia = $totalesPorAnio[$ultimo] - $totalesPorAnio[$penultimo];
    }

    $caption = 'Diferencia de Firmas con año anterior';
    $subCaptionParts = [];

    if (!empty($this->departmentName)) {
      $subCaptionParts[] = "Departamento: {$this->departmentName}";
    }

    $subCaptionParts[] = "Desde: {$this->firstYear}";
    $subCaptionParts[] = "Hasta: {$this->lastYear}";

    if (!is_null($diferencia)) {
      $texto = $diferencia > 0
        ? "▲ Incremento de $diferencia firmas"
        : ($diferencia < 0
          ? "▼ Disminución de " . abs($diferencia) . " firmas"
          : "Sin variación");
      $subCaptionParts[] = $texto . ' (' . $this->lastYear . '-' . ($this->lastYear - 1) . ')';
    }

    return [
      'data' => $estructura,
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaptionParts),
      'diferencia' => $diferencia
    ];
  }

  public function getDataLine(): array
  {
    $query = Caso::select(
      DB::raw('YEAR(fecha_firma) AS year'),
      DB::raw('MONTH(fecha_firma) AS month'),
      DB::raw('COUNT(*) AS total')
    )
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '>=', $this->firstYear)
      ->whereYear('fecha_firma', '<=', $this->lastYear);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy(DB::raw('YEAR(fecha_firma), MONTH(fecha_firma)'))
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $estructura = $this->getEstructuraGraficoLine($data);

    // Totales por año
    $totales = [];
    foreach ($estructura['dataset'] as $serie) {
      $totales[$serie['seriesname']] = collect($serie['data'])->sum(fn($d) => (int) $d['value']);
    }

    ksort($totales);
    $keys = array_keys($totales);

    $diferencia = null;
    if (count($keys) >= 2) {
      $ultimo = end($keys);
      $penultimo = prev($keys);
      $diferencia = $totales[$ultimo] - $totales[$penultimo];
    }

    $caption = 'Tendencia de Firmas por Año';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "Desde: {$this->firstYear}";
    $subCaption[] = "Hasta: {$this->lastYear}";

    if (!is_null($diferencia)) {
      $texto = $diferencia > 0
        ? "▲ Incremento de $diferencia firmas"
        : ($diferencia < 0
          ? "▼ Disminución de " . abs($diferencia) . " firmas"
          : "Sin variación");
      $subCaption[] = $texto . " ({$this->lastYear} - " . ($this->lastYear - 1) . ")";
    }

    return [
      'categories' => $estructura['categories'],
      'dataset' => $estructura['dataset'],
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaption),
      'diferencia' => $diferencia
    ];
  }

  public function getDataBar(): array
  {
    $query = Caso::select(
      DB::raw('YEAR(fecha_firma) AS year'),
      DB::raw('COUNT(*) AS total')
    )
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '>=', $this->firstYear)
      ->whereYear('fecha_firma', '<=', $this->lastYear);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy(DB::raw('YEAR(fecha_firma)'))
      ->orderBy('year')
      ->get();

    $dataset = $data->map(function ($item) {
      return [
        'label' => (string) $item->year,
        'value' => $item->total,
      ];
    })->toArray();


    return [
      'data' => $dataset,
      'caption' => 'Total de Firmas por Año',
      'subCaption' => "Desde: {$this->firstYear} | Hasta: {$this->lastYear}" .
        (!empty($this->departmentName) ? " | Departamento: {$this->departmentName}" : '')
    ];
  }

  public function getDataPie(): array
  {
    $query = Caso::select(
      DB::raw('YEAR(fecha_firma) as year'),
      DB::raw('COUNT(*) as total')
    )
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '>=', $this->firstYear)
      ->whereYear('fecha_firma', '<=', $this->lastYear);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $result = $query->groupBy('year')
      ->orderBy('year')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => (string) $item->year,
        'value' => $item->total
      ];
    })->toArray();

    return [
      'caption' => 'Proporción de Firmas por Año',
      'subCaption' => "Desde: {$this->firstYear} | Hasta: {$this->lastYear}" .
        (!empty($this->departmentName) ? " | Departamento: {$this->departmentName}" : ''),
      'data' => $data
    ];
  }

  public function getDataArea(): array
  {
    $meses = [
      1 => 'Ene',
      2 => 'Feb',
      3 => 'Mar',
      4 => 'Abr',
      5 => 'May',
      6 => 'Jun',
      7 => 'Jul',
      8 => 'Ago',
      9 => 'Sep',
      10 => 'Oct',
      11 => 'Nov',
      12 => 'Dic',
    ];

    $query = Caso::select(
      DB::raw('YEAR(fecha_firma) as year'),
      DB::raw('MONTH(fecha_firma) as month'),
      DB::raw('COUNT(*) as total')
    )
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '>=', $this->firstYear)
      ->whereYear('fecha_firma', '<=', $this->lastYear);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $rawData = $query->groupBy('year', 'month')
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    // Agrupamos por año
    $dataPorAnio = [];
    foreach ($rawData as $item) {
      $anio = $item->year;
      $mes = $item->month;
      $dataPorAnio[$anio][$mes] = $item->total;
    }

    // Generamos categorías por mes
    $categories = [
      [
        'category' => collect($meses)->map(fn($mes) => ['label' => $mes])->values()->toArray()
      ]
    ];

    // Dataset por año
    $dataset = [];
    foreach (range($this->firstYear, $this->lastYear) as $anio) {
      $dataset[] = [
        'seriesname' => (string) $anio,
        'data' => collect($meses)->map(function ($_, $mes) use ($dataPorAnio, $anio) {
          return ['value' => $dataPorAnio[$anio][$mes] ?? 0];
        })->values()->toArray()
      ];
    }

    return [
      'categories' => $categories,
      'dataset' => $dataset,
      'caption' => 'Firmas por Año (Área)',
      'subCaption' => "Desde: {$this->firstYear} | Hasta: {$this->lastYear}" .
        (!empty($this->departmentName) ? " | Departamento: {$this->departmentName}" : '')
    ];
  }
}
