<?php

namespace App\Livewire\Dashboards;

use App\Helpers\Helpers;
use App\Models\Bank;
use App\Models\Caratula;
use App\Models\Caso;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ControlMensual extends Component
{
  public $department;
  public $departments = [];
  public $years = [];      // Lista de años disponibles para el filtro
  public $months = [];     // Lista de meses
  public $year;       // Año inicial del filtro (por defecto, año anterior al actual)
  public $month;
  public $chartTheme = 'zune'; // Valor por defecto
  public $chartsPerRow = 1; // por defecto 2 gráficos por fila
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
    $this->years = Caso::select(DB::raw('YEAR(fecha_creacion) as year'))
      ->whereNotNull('fecha_creacion')
      ->distinct()
      ->orderBy('year', 'asc')
      ->pluck('year')
      ->toArray();

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

    // Año actual y anterior como valores por defecto
    $this->year =  Carbon::now()->year;
    $this->month =  Carbon::now()->format('m');

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

    $dataPreviusDate = $this->getPreviousMonths($this->year, $this->month);

    $heatmap_one   = $this->getHeatmapByDayData($this->year, $this->month); // Nueva función para obtener datos del heatmap
    $heatmap_two   = $this->getHeatmapByDayData($dataPreviusDate['first_previous']['year'], $dataPreviusDate['first_previous']['month']); // Nueva función para obtener datos del heatmap
    $heatmap_three = $this->getHeatmapByDayData($dataPreviusDate['second_previous']['year'], $dataPreviusDate['second_previous']['month']); // Nueva función para obtener datos del heatmap

    return [
      'heatmap_one' => $heatmap_one,
      'heatmap_two' => $heatmap_two,
      'heatmap_three' => $heatmap_three
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.control-mensual');
  }

  public function getHeatmapByDayData($year, $month): array
  {
    // Obtener el número de días en el mes
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;

    // Crear array de días (columnas)
    $columns = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
      $columns[] = [
        'id' => (string)$day,
        'label' => (string)$day
      ];
    }

    // Filas (tipos de eventos)
    $rows = [
      ['id' => 'firmas', 'label' => 'Firmas'],
      ['id' => 'caratulas', 'label' => 'Carátulas']
    ];

    // Consulta para obtener datos diarios - AJUSTADA CON COALESCE
    $results = Caso::select(
      DB::raw('DAY(COALESCE(fecha_firma, fecha_caratula)) AS day'),
      DB::raw('COUNT(CASE WHEN fecha_firma IS NOT NULL THEN id END) AS firmas'),
      DB::raw('COUNT(CASE WHEN fecha_caratula IS NOT NULL AND caratula_id = ' . Caratula::CARATULA . ' THEN id END) AS caratulas')
    )
      ->where(function ($q) use ($year, $month) {
        // Filtrar por mes y año en ambas fechas
        $q->where(function ($q1) use ($year, $month) {
          $q1->whereYear('fecha_firma', $year)
            ->whereMonth('fecha_firma', $month);
        })->orWhere(function ($q2) use ($year, $month) {
          $q2->whereYear('fecha_caratula', $year)
            ->whereMonth('fecha_caratula', $month)
            ->where('caratula_id', Caratula::CARATULA);
        });
      });

    // Aplicar filtros de departamento
    if (!empty($this->department)) {
      $results->where('department_id', $this->department);
    } elseif (!empty($this->departments)) {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $results->whereIn('department_id', $ids);
      }
    }

    // Ejecutar y agrupar por día - AJUSTADO CON COALESCE
    $dailyData = $results
      ->groupBy(DB::raw('DAY(COALESCE(fecha_firma, fecha_caratula))'))
      ->orderBy('day')
      ->get()
      ->keyBy('day');

    // Preparar dataset
    $dataset = [];
    $maxValue = 0;

    // Recorrer cada fila (firmas y caratulas)
    foreach ($rows as $row) {
      $rowId = $row['id'];

      // Recorrer cada columna (día)
      foreach ($columns as $col) {
        $day = (int)$col['id'];
        $value = 0;

        // Obtener el valor para este día y fila
        if ($dailyData->has($day)) {
          $value = (int)$dailyData[$day]->{$rowId};
        }

        // Actualizar el valor máximo para la escala de colores
        if ($value > $maxValue) {
          $maxValue = $value;
        }

        // Agregar al dataset
        $dataset[] = [
          'rowid' => $rowId,
          'columnid' => (string)$day,
          'value' => $value,
          'displayvalue' => (string)$value
        ];
      }
    }

    // Asegurar maxValue mínimo de 1
    $maxValue = max($maxValue, 1);

    // Preparar título
    $monthName = Carbon::createFromDate($year, $month, 1)
      ->locale('es')
      ->monthName;

    $caption = "Actividad diaria - $monthName {$year}";
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $theme = $this->chartTheme ?? 'zune';

    return [
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset]],
      'colorrange' => $this->generateColorRange($maxValue, $theme)
    ];
  }

  private function generateColorRange(float $maxValue, string $theme): array
  {
    // Definir paletas de colores para el heatmap por tema
    $themeColors = [
      'candy' => ['#36B5D8', '#F0DC46', '#F066AC', '#6EC85A', '#6E80CA'],
      'carbon' => ['#444444', '#666666', '#888888', '#aaaaaa', '#cccccc'],
      'fint' => ['#0075c2', '#1aaf5d', '#f2c500', '#f45b00', '#8e0000'],
      'fusion' => ['#5D62B5', '#29C3BE', '#F2726F', '#FFC533', '#62B58F'],
      'gammel' => ['#7CB5EC', '#434348', '#8EED7D', '#F7A35C', '#8085E9'],
      'ocean' => ['#04476c', '#4d998d', '#77be99', '#a7dca6', '#cef19a'],
      'umber' => ['#5D4037', '#7B1FA2', '#0288D1', '#388E3C', '#E64A19'],
      'zune' => ['#0075c2', '#1aaf5d', '#f2c500', '#f45b00', '#8e0000'],
    ];

    // Seleccionar paleta basada en el tema
    $colors = $themeColors[$theme] ?? $themeColors['zune'];

    // Si no hay datos o el valor máximo es 0, usar un rango simple
    if ($maxValue <= 0) {
      return [
        'gradient' => "1",
        'minvalue' => "0",
        'code' => $colors[0],
        'color' => [
          ['code' => $colors[0], 'minvalue' => "0", 'maxvalue' => "1"]
        ]
      ];
    }

    // Calcular los rangos de valores
    $range1 = $maxValue * 0.2;
    $range2 = $maxValue * 0.4;
    $range3 = $maxValue * 0.6;
    $range4 = $maxValue * 0.8;

    return [
      'gradient' => "1",
      'minvalue' => "0",
      'code' => $colors[0],
      'color' => [
        ['code' => $colors[0], 'minvalue' => "0", 'maxvalue' => $range1],
        ['code' => $colors[1], 'minvalue' => $range1, 'maxvalue' => $range2],
        ['code' => $colors[2], 'minvalue' => $range2, 'maxvalue' => $range3],
        ['code' => $colors[3], 'minvalue' => $range3, 'maxvalue' => $range4],
        ['code' => $colors[4], 'minvalue' => $range4, 'maxvalue' => $maxValue]
      ]
    ];
  }

  function getPreviousMonths($year, $month)
  {
    // Crear fecha base (primer día del mes)
    $date = Carbon::create($year, $month, 1);

    // Calcular meses anteriores
    $firstPrevious = $date->copy()->subMonth();
    $secondPrevious = $date->copy()->subMonths(2);

    return [
      'first_previous' => [
        'year' => $firstPrevious->year,
        'month' => $firstPrevious->month,
        'name' => $firstPrevious->locale('es')->monthName
      ],
      'second_previous' => [
        'year' => $secondPrevious->year,
        'month' => $secondPrevious->month,
        'name' => $secondPrevious->locale('es')->monthName
      ]
    ];
  }
}
