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

class VolumenBanco extends Component
{
  public $department;
  public $departments = [];
  public $years = [];      // Lista de años disponibles para el filtro
  public $year;            // Año (por defecto, año anterior al actual)
  public $months = [];
  public $month;           // mes
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

    $now = Carbon::now();

    // Año actual y anterior como valores por defecto
    $this->year = Carbon::now()->year;
    $this->month = $now->format('m');

    $this->months = [
      ['id' => '01', 'name' => 'Enero'],
      ['id' => '02', 'name' => 'Febrero'],
      ['id' => '03', 'name' => 'Marzo'],
      ['id' => '04', 'name' => 'Abril'],
      ['id' => '05', 'name' => 'Mayo'],
      ['id' => '06', 'name' => 'Junio'],
      ['id' => '07', 'name' => 'Julio'],
      ['id' => '08', 'name' => 'Agosto'],
      ['id' => '09', 'name' => 'Septiembre'],
      ['id' => '10', 'name' => 'Octubre'],
      ['id' => '11', 'name' => 'Noviembre'],
      ['id' => '12', 'name' => 'Diciembre']
    ];

    $this->js(<<<JS
        Livewire.dispatch('updateFusionCharts', {$this->getChartDataJson()});
    JS);
  }

  public function updated($property)
  {
    if (in_array($property, ['department', 'year', 'month', 'chartTheme'])) {
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

    $line_volumen = $this->getDataLine();
    $pie_formalizaciones_mes  = $this->getDataPieFormalizacionesMes();
    $pie_formalizaciones_year  = $this->getDataPieFormalizacionesYear();

    return [
      'line_volumen' => $line_volumen,
      'pie_formalizaciones_mes' => $pie_formalizaciones_mes,
      'pie_formalizaciones_year' => $pie_formalizaciones_year
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.volumen-banco');
  }

  public function getDataLine(): array
  {
    $query = Caso::select(
      'banks.name AS bank',
      DB::raw('YEAR(fecha_firma) AS year'),
      DB::raw('MONTH(fecha_firma) AS month'),
      DB::raw('COUNT(*) AS total')
    )
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '=', $this->year);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy('banks.name', DB::raw('YEAR(fecha_firma)'), DB::raw('MONTH(fecha_firma)'))
      ->orderBy('banks.name')
      ->orderBy('month')
      ->get();

    $estructura = $this->getEstructuraGraficoLine($data);

    $caption = 'Volumen por banco';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "{$this->year}";

    return [
      'categories' => $estructura['categories'],
      'dataset' => $estructura['dataset'],
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaption)
    ];
  }

  public function getDataPieFormalizacionesMes(): array
  {
    $query = Caso::select(
      'banks.name AS bank',
      DB::raw('COUNT(*) AS total')
    )
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '=', $this->year)
      ->whereMonth('fecha_firma', '=', $this->month);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $result = $query
      ->groupBy(DB::raw('banks.name'))
      ->orderBy('banks.name')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => $item->bank,
        'value' => $item->total
      ];
    })->toArray();

    $caption = 'Formalizaciones';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    // Preparar título
    $monthName = Carbon::createFromDate($this->year, $this->month, 1)
      ->locale('es')
      ->monthName;

    if (!empty($monthName))
      $subCaption[] = "$monthName";
    $subCaption[] = "de {$this->year}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
    ];
  }

  public function getDataPieFormalizacionesYear(): array
  {
    $query = Caso::select(
      'banks.name AS bank',
      DB::raw('COUNT(*) AS total')
    )
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '=', $this->year);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $result = $query
      ->groupBy(DB::raw('banks.name'))
      ->orderBy('banks.name')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => $item->bank,
        'value' => $item->total
      ];
    })->toArray();

    $caption = 'Formalizaciones';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "{$this->year}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
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

    // Obtener todos los bancos únicos
    $banks = $lineDataRaw->pluck('bank')->unique()->sort()->values();

    // Obtener todos los años en el rango
    $years = [$this->year];

    // Inicializar estructura de datos por banco y año
    $bankData = [];

    foreach ($banks as $bank) {
      foreach ($years as $year) {
        // Inicializar todos los meses en 0 para cada banco/año
        $bankData[$bank][$year] = array_fill(0, 12, 0);
      }
    }

    // Llenar con datos reales
    foreach ($lineDataRaw as $row) {
      $monthIndex = $row->month - 1;
      $bankData[$row->bank][$row->year][$monthIndex] = $row->total;
    }

    // Construir categorías (meses)
    $categories = [
      ['category' => array_map(fn($month) => ['label' => $month], $months)]
    ];

    // Construir series de datos por banco
    $dataset = [];

    foreach ($bankData as $bankName => $yearsData) {
      foreach ($yearsData as $year => $monthlyData) {
        $dataset[] = [
          'seriesname' => "{$bankName} {$year}",
          'data' => array_map(fn($val) => ['value' => $val], $monthlyData)
        ];
      }
    }

    return [
      'categories' => $categories,
      'dataset' => $dataset
    ];
  }
}
