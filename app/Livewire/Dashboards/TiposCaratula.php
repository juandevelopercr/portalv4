<?php

namespace App\Livewire\Dashboards;

use App\Models\Caratula;
use App\Models\Caso;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class TiposCaratula extends Component
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

    $line_caratulas = $this->getDataLineCaratulas();
    $line_precaratulas = $this->getDataLinePreCaratulas();

    return [
      'line_caratulas' => $line_caratulas,
      'line_precaratulas' => $line_precaratulas,
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.tipos-caratulas');
  }

  public function getDataLineCaratulas(): array
  {
    $query = Caso::select(
      DB::raw('YEAR(fecha_caratula) AS year'),
      DB::raw('MONTH(fecha_caratula) AS month'),
      DB::raw('COUNT(*) AS total')
    )
      ->whereNotNull('fecha_caratula')
      ->whereYear('fecha_caratula', '>=', $this->firstYear)
      ->whereYear('fecha_caratula', '<=', $this->lastYear)
      ->where('caratula_id', Caratula::CARATULA);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy(DB::raw('YEAR(fecha_caratula), MONTH(fecha_caratula)'))
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $estructura = $this->getEstructuraGraficoLine($data);

    $caption = 'Carátulas por meses';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "Desde: {$this->firstYear}";
    $subCaption[] = "Hasta: {$this->lastYear}";

    return [
      'categories' => $estructura['categories'],
      'dataset' => $estructura['dataset'],
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaption)
    ];
  }

  public function getDataLinePreCaratulas(): array
  {
    $query = Caso::select(
      DB::raw('YEAR(fecha_precaratula) AS year'),
      DB::raw('MONTH(fecha_precaratula) AS month'),
      DB::raw('COUNT(*) AS total')
    )
      ->whereNotNull('fecha_precaratula')
      ->whereYear('fecha_precaratula', '>=', $this->firstYear)
      ->whereYear('fecha_precaratula', '<=', $this->lastYear)
      ->where('caratula_id', Caratula::PRECARATULA);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy(DB::raw('YEAR(fecha_precaratula), MONTH(fecha_precaratula)'))
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $estructura = $this->getEstructuraGraficoLine($data);

    $caption = 'Pre carátulas por meses';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "Desde: {$this->firstYear}";
    $subCaption[] = "Hasta: {$this->lastYear}";

    return [
      'categories' => $estructura['categories'],
      'dataset' => $estructura['dataset'],
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaption)
    ];
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
}
