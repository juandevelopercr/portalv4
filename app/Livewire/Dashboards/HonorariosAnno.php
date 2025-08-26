<?php

namespace App\Livewire\Dashboards;

use App\Models\Bank;
use App\Models\Caso;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class HonorariosAnno extends Component
{
  public $department;
  public $departments = [];
  public $years = [];      // Lista de años disponibles para el filtro
  public $firstYear;       // Año inicial del filtro (por defecto, año anterior al actual)
  public $lastYear;        // Año final del filtro (por defecto, año actual)
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
    $this->years = Transaction::select(DB::raw('YEAR(transaction_date) as year'))
      ->where('proforma_status', Transaction::FACTURADA)
      ->whereIn('document_type', [Transaction::PROFORMA, Transaction::FACTURAELECTRONICA, Transaction::TIQUETEELECTRONICO])
      ->whereNotNull('transaction_date')
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

    //$mscolumn2d = $this->getDataMsColumn2d();

    $data = $this->getDataBar();
    $heatmap = $this->getHeatmapByYearData(); // Nueva función para obtener datos del heatmap
    $heatmap_banks = $this->getHeatmapByBankData();

    $bar_usd = [
      'categories' => $data['categories'],
      'dataset' => $data['dataset_usd'],
      'caption' => $data['caption_usd'],
      'subCaption' => $data['subCaption']
    ];
    $bar_crc = [
      'categories' => $data['categories'],
      'dataset' => $data['dataset_crc'],
      'caption' => $data['caption_crc'],
      'subCaption' => $data['subCaption']
    ];

    return [
      'bar_usd' => $bar_usd,
      'bar_crc' => $bar_crc,
      'heatmap_usd' => $heatmap['data_usd'],
      'heatmap_crc' => $heatmap['data_crc'],

      'heatmap_bank_usd' => $heatmap_banks['data_usd'],
      'heatmap_bank_crc' => $heatmap_banks['data_crc'],
    ];
  }

  public function getDataBar(): array
  {
    $query = Transaction::select(
      DB::raw('YEAR(transaction_date) AS year'),
      DB::raw('MONTH(transaction_date) AS month'),
      DB::raw("
        ROUND(SUM(
            CASE                                                                                             
                WHEN transactions.currency_id = " . Currency::DOLARES . " AND transactions.department_id = casos.department_id
                    THEN (totalHonorarios - totalDiscount + totalTax)
                ELSE 0
            END
        ), 2) AS total_usd
    "),
      DB::raw("
        ROUND(SUM(
            CASE 
                WHEN transactions.currency_id = " . Currency::COLONES . " AND transactions.department_id = casos.department_id
                    THEN (totalHonorarios - totalDiscount + totalTax)
                ELSE 0
            END
        ), 2) AS total_crc
    ")
    )
      ->join('casos', 'transactions.caso_id', '=', 'casos.id')
      ->where('proforma_status', Transaction::FACTURADA)
      ->whereIn('document_type', [
        Transaction::PROFORMA,
        Transaction::FACTURAELECTRONICA,
        Transaction::TIQUETEELECTRONICO
      ])
      ->whereNotNull('transaction_date')
      ->whereYear('transaction_date', '>=', $this->firstYear)
      ->whereYear('transaction_date', '<=', $this->lastYear);

    // Filtro por departamento
    if (!empty($this->department)) {
      $query->where('transactions.department_id', $this->department);
    } elseif (!empty($this->departments)) {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('transactions.department_id', $ids);
      }
    }

    $data = $query
      ->groupBy(DB::raw('YEAR(transaction_date), MONTH(transaction_date)'))
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $estructura_usd = $this->getEstructuraGraficoBar($data, 'USD');
    $estructura_crc = $this->getEstructuraGraficoBar($data, 'CRC');

    $caption_usd = 'Honorarios USD';
    $caption_crc = 'Honorarios CRC';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "Desde: {$this->firstYear}";
    $subCaption[] = "Hasta: {$this->lastYear}";

    return [
      'categories' => $estructura_usd['categories'],
      'dataset_usd' => $estructura_usd['dataset'],
      'dataset_crc' => $estructura_crc['dataset'],
      'caption_usd' => $caption_usd,
      'caption_crc' => $caption_crc,
      'subCaption' => implode(' | ', $subCaption),
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.honorarios-anno');
  }

  public function getEstructuraGraficoBar($DataRaw, $currency)
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

    foreach ($DataRaw as $row) {
      $monthIndex = $row->month - 1;
      if (isset($grouped[$row->year][$monthIndex])) {
        $grouped[$row->year][$monthIndex] = $currency == 'USD' ? $row->total_usd : $row->total_crc;
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

  public function getHeatmapByYearData(): array
  {
    // Obtener los datos base de la misma consulta que el gráfico de barras
    $baseQuery = Transaction::select(
      DB::raw('YEAR(transaction_date) AS year'),
      DB::raw('MONTH(transaction_date) AS month'),
      DB::raw("
            ROUND(SUM(
                CASE 
                    WHEN transactions.currency_id = " . Currency::DOLARES . " AND transactions.department_id = casos.department_id
                        THEN (totalHonorarios - totalDiscount + totalTax)
                    ELSE 0
                END
            ), 2) AS total_usd
        "),
      DB::raw("
            ROUND(SUM(
                CASE 
                    WHEN transactions.currency_id = " . Currency::COLONES . " AND transactions.department_id = casos.department_id
                        THEN (totalHonorarios - totalDiscount + totalTax)
                    ELSE 0
                END
            ), 2) AS total_crc
        ")
    )
      ->join('casos', 'transactions.caso_id', '=', 'casos.id')
      ->where('proforma_status', Transaction::FACTURADA)
      ->whereIn('document_type', [
        Transaction::PROFORMA,
        Transaction::FACTURAELECTRONICA,
        Transaction::TIQUETEELECTRONICO
      ])
      ->whereNotNull('transaction_date')
      ->whereYear('transaction_date', '>=', $this->firstYear)
      ->whereYear('transaction_date', '<=', $this->lastYear);

    // Aplicar filtros de departamento
    if (!empty($this->department)) {
      $baseQuery->where('transactions.department_id', $this->department);
    } elseif (!empty($this->departments)) {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $baseQuery->whereIn('transactions.department_id', $ids);
      }
    }

    // Ejecutar consulta y obtener resultados
    $results = $baseQuery
      ->groupBy(DB::raw('YEAR(transaction_date), MONTH(transaction_date)'))
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    // Obtener todos los años y meses posibles en el rango
    $years = range($this->firstYear, $this->lastYear);
    $months = range(1, 12);

    // Preparar estructura para filas (años)
    $rows = [];
    foreach ($years as $year) {
      $rows[] = [
        'id' => (string)$year,
        'label' => (string)$year
      ];
    }

    // Preparar estructura para columnas (meses)
    $columns = [];
    foreach ($months as $month) {

      // Usar Carbon para obtener el nombre del mes en español
      /*
      $monthName = Carbon::createFromDate(null, $month, null)
        ->locale('es') // Establecer localización en español
        ->monthName;   // Obtener nombre completo del mes
      */

      // Forma alternativa para nombre abreviado:
      $monthName = ucfirst(Carbon::createFromDate(null, $month, null)
        ->locale('es')
        ->shortMonthName);

      $columns[] = [
        'id' => (string)$month,
        'label' => $monthName
      ];
    }

    // Preparar dataset con valores
    $dataset_usd = [];
    $dataset_crc = [];
    foreach ($years as $year) {
      foreach ($months as $month) {
        // Buscar el resultado correspondiente
        $result = $results->first(function ($item) use ($year, $month) {
          return $item->year == $year && $item->month == $month;
        });

        // Determinar el valor a mostrar
        $value_usd = $result ? (float)$result->total_usd : 0;
        $value_crc = $result ? (float)$result->total_crc : 0;

        // CORRECCIÓN: Variables con nombre correcto
        $dataset_usd[] = [
          'rowid' => (string)$year,
          'columnid' => (string)$month,
          'value' => $value_usd,
          'displayvalue' => '$' . number_format($value_usd, 2)
        ];

        $dataset_crc[] = [
          'rowid' => (string)$year,
          'columnid' => (string)$month,
          'value' => $value_crc,
          'displayvalue' => '₡' . number_format($value_crc, 2)
        ];
      }
    }

    // Calcular valor máximo para la escala de colores
    $maxValueUsd = $results->max('total_usd') ?? 1;
    $maxValueCrc = $results->max('total_crc') ?? 1;

    // Obtener el tema activo (asumiendo que está disponible)
    $theme = $this->chartTheme ?? 'zune';

    $caption_usd = 'Facturación Anual por Mes (USD)';
    $caption_crc = 'Facturación Anual por Mes (CRC)';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "Desde: {$this->firstYear}";
    $subCaption[] = "Hasta: {$this->lastYear}";

    $data_usd = [
      'caption' => $caption_usd,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset_usd]], // CORRECCIÓN: Dataset USD
      'colorrange' => $this->generateColorRange($maxValueUsd, $theme)
    ];

    $data_crc = [
      'caption' => $caption_crc,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset_crc]], // CORRECCIÓN: Dataset CRC
      'colorrange' => $this->generateColorRange($maxValueCrc, $theme)
    ];

    return [
      'data_usd' => $data_usd,
      'data_crc' => $data_crc
    ];
  }

  public function getHeatmapByBankData(): array
  {
    // Obtener los datos base agrupados por banco y mes
    $baseQuery = Transaction::select(
      'transactions.bank_id', // Agregamos el campo bank_id
      DB::raw('YEAR(transaction_date) AS year'),
      DB::raw('MONTH(transaction_date) AS month'),
      DB::raw("
            ROUND(SUM(
                CASE 
                    WHEN transactions.currency_id = " . Currency::DOLARES . " AND transactions.department_id = casos.department_id
                        THEN (totalHonorarios - totalDiscount + totalTax)
                    ELSE 0
                END
            ), 2) AS total_usd
        "),
      DB::raw("
            ROUND(SUM(
                CASE 
                    WHEN transactions.currency_id = " . Currency::COLONES . " AND transactions.department_id = casos.department_id
                        THEN (totalHonorarios - totalDiscount + totalTax)
                    ELSE 0
                END
            ), 2) AS total_crc
        ")
    )
      ->join('casos', 'transactions.caso_id', '=', 'casos.id')
      ->where('proforma_status', Transaction::FACTURADA)
      ->whereIn('document_type', [
        Transaction::PROFORMA,
        Transaction::FACTURAELECTRONICA,
        Transaction::TIQUETEELECTRONICO
      ])
      ->whereNotNull('transaction_date')
      //->whereYear('transaction_date', '>=', $this->firstYear)
      ->whereYear('transaction_date', '=', $this->lastYear)
      ->with('bank') // Cargar relación con el banco
      ->groupBy('bank_id', DB::raw('YEAR(transaction_date)'), DB::raw('MONTH(transaction_date)'))
      ->orderBy('bank_id')
      ->orderBy('year')
      ->orderBy('month');

    // Aplicar filtros de departamento
    if (!empty($this->department)) {
      $baseQuery->where('transactions.department_id', $this->department);
    } elseif (!empty($this->departments)) {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $baseQuery->whereIn('transactions.department_id', $ids);
      }
    }

    // Ejecutar consulta y obtener resultados
    $results = $baseQuery->get();

    // Obtener todos los bancos únicos
    $bankIds = $results->pluck('bank_id')->unique();
    $banks = Bank::whereIn('id', $bankIds)->get()->keyBy('id');

    // Obtener todos los meses posibles en el rango
    $months = range(1, 12);

    // Preparar estructura para filas (bancos)
    $rows = [];
    foreach ($banks as $bank) {
      $rows[] = [
        'id' => (string)$bank->id,
        'label' => $bank->name
      ];
    }

    // Preparar estructura para columnas (meses)
    $columns = [];
    foreach ($months as $month) {
      $monthName = ucfirst(Carbon::createFromDate(null, $month, null)
        ->locale('es')
        ->shortMonthName);

      $columns[] = [
        'id' => (string)$month,
        'label' => $monthName
      ];
    }

    // Preparar dataset con valores
    $dataset_usd = [];
    $dataset_crc = [];

    // Para cada banco y cada mes, buscar el valor
    foreach ($banks as $bankId => $bank) {
      foreach ($months as $month) {
        // Buscar el resultado correspondiente para este banco y mes
        $result = $results->first(function ($item) use ($bankId, $month) {
          return $item->bank_id == $bankId && $item->month == $month;
        });

        // Determinar el valor a mostrar
        $value_usd = $result ? (float)$result->total_usd : 0;
        $value_crc = $result ? (float)$result->total_crc : 0;

        $dataset_usd[] = [
          'rowid' => (string)$bankId,
          'columnid' => (string)$month,
          'value' => $value_usd,
          'displayvalue' => '$' . number_format($value_usd, 2)
        ];

        $dataset_crc[] = [
          'rowid' => (string)$bankId,
          'columnid' => (string)$month,
          'value' => $value_crc,
          'displayvalue' => '₡' . number_format($value_crc, 2)
        ];
      }
    }

    // Calcular valor máximo para la escala de colores
    $maxValueUsd = $results->max('total_usd') ?? 1;
    $maxValueCrc = $results->max('total_crc') ?? 1;

    // Obtener el tema activo
    $theme = $this->chartTheme ?? 'zune';

    $caption_usd = 'Facturación por Banco y Mes (USD)';
    $caption_crc = 'Facturación por Banco y Mes (CRC)';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "Desde: {$this->firstYear}";
    $subCaption[] = "Hasta: {$this->lastYear}";

    $data_usd = [
      'caption' => $caption_usd,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset_usd]],
      'colorrange' => $this->generateColorRange($maxValueUsd, $theme)
    ];

    $data_crc = [
      'caption' => $caption_crc,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset_crc]],
      'colorrange' => $this->generateColorRange($maxValueCrc, $theme)
    ];

    return [
      'data_usd' => $data_usd,
      'data_crc' => $data_crc
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
}
