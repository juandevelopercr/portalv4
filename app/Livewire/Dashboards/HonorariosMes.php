<?php

namespace App\Livewire\Dashboards;

use App\Helpers\Helpers;
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

class HonorariosMes extends Component
{
  public $years = [];      // Lista de años disponibles para el filtro
  public $firstYear;       // Año inicial del filtro (por defecto, año anterior al actual)
  public $lastYear;        // Año final del filtro (por defecto, año actual)
  public $month;           // Mes de análisis (por defecto mes actual)
  public $chartTheme = 'zune'; // Valor por defecto
  public $chartsPerRow = 2; // por defecto 2 gráficos por fila
  public $months;
  public $monthName;

  public $total_honorario_iva;
  public $total_honorario;
  public $total_gasto;
  public $total_iva;
  public $total_descuento;
  public $total_transaction;

  public function mount()
  {
    $this->total_honorario_iva = 0;
    $this->total_honorario = 0;
    $this->total_gasto = 0;
    $this->total_iva = 0;
    $this->total_descuento = 0;
    $this->total_transaction = 0;

    // Obtener años únicos desde la columna created_at
    $this->years = Transaction::select(DB::raw('YEAR(transaction_date) as year'))
      ->where('status', Transaction::ACEPTADA)
      ->whereIn('document_type', [Transaction::FACTURAELECTRONICA, Transaction::TIQUETEELECTRONICO])
      ->whereNotNull('transaction_date')
      ->distinct()
      ->orderBy('year', 'asc')
      ->pluck('year')
      ->toArray();

    // Año actual y anterior como valores por defecto
    // Obtener la fecha actual con Carbon
    $now = Carbon::now();

    // Obtener el año actual (formato: '2024')
    $currentYear = $now->year; // o $now->format('Y');

    // Obtener el mes actual (formato: '01' a '12')
    $this->month = $now->format('m');
    $this->firstYear = $currentYear - 1;
    $this->lastYear = $currentYear;

    if ($this->lastYear == $currentYear)
      $this->firstYear = $currentYear;

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
    if (in_array($property, ['firstYear', 'lastYear', 'month', 'chartTheme'])) {
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
    $this->monthName = $this->getNombreMes();

    $line = $this->getDataLine();
    //$pie  = $this->getDataPie();
    //$stackedbar3d = $this->getDataStack();

    $data = $this->getTransactionTotal();

    $this->total_honorario_iva = Helpers::formatDecimal($data['total_honorario_iva'], 2);
    $this->total_honorario = Helpers::formatDecimal($data['total_honorario'], 2);
    $this->total_gasto = Helpers::formatDecimal($data['total_gasto'], 2);
    $this->total_iva = Helpers::formatDecimal($data['total_iva'], 2);
    $this->total_descuento = Helpers::formatDecimal($data['total_descuento'], 2);
    $this->total_transaction = (int)$data['total_transaction'];

    // Datos para el panel KPI
    $kpiData = [
      'total_honorario_iva' => $data['total_honorario_iva'],
      'total_honorario' => $data['total_honorario'],
      'total_gasto' => $data['total_gasto'],
      'total_iva' => $data['total_iva'],
      'total_descuento' => $data['total_descuento'],
      'total_transaction' => $data['total_transaction'],
      'mes' => $this->getNombreMes(),
      'anno' => $this->lastYear
    ];

    // Construir la estructura completa del gráfico KPI
    $kpiChart = $this->buildKpiChartStructure($kpiData);

    $caption = 'Resumen de indicadores';
    $subCaption = [];

    if (!empty($this->monthName))
      $subCaption[] = "$this->monthName";
    $subCaption[] = "de {$this->lastYear}";

    return [
      'kpi'  => $kpiChart,
      'kpiMeta' => [ // Metadatos para el título
        'mes' => $this->monthName,
        'anno' => $this->lastYear,
        'caption'    => $caption,
        'subCaption' => implode('  ', $subCaption)
      ],
      'line' => $line,
      //'pie'  => $pie,
      //'stackedbar3d' => $stackedbar3d
    ];
  }

  public function getDataLine(): array
  {
    $query = Transaction::where('status', Transaction::ACEPTADA)
      ->whereIn('document_type', [
        Transaction::FACTURAELECTRONICA,
        Transaction::TIQUETEELECTRONICO
      ])
      ->whereNotNull('transaction_date')
      ->whereYear('transaction_date', '>=', $this->firstYear)
      ->whereYear('transaction_date', '<=', $this->lastYear);

    $data = $query
      ->select(
        DB::raw('YEAR(transaction_date) AS year'),
        DB::raw('MONTH(transaction_date) AS month'),
        DB::raw("SUM(
            CASE
                WHEN transactions.currency_id = 1
                    THEN transactions.totalComprobante
                ELSE transactions.totalComprobante / transactions.factura_change_type
            END
        ) AS total")
      )
      ->groupBy(DB::raw('YEAR(transaction_date), MONTH(transaction_date)'))
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $estructura = $this->getEstructuraGraficoLine($data);

    $caption = 'Total facturado por meses en USD';
    $subCaption = [];

    $subCaption[] = "Desde: {$this->firstYear}";
    $subCaption[] = "Hasta: {$this->lastYear}";

    return [
      'categories' => $estructura['categories'],
      'dataset'    => $estructura['dataset'],
      'caption'    => $caption,
      'subCaption' => implode(' | ', $subCaption)
    ];
  }

  public function getDataPie(): array
  {
    $query = Transaction::where('status', Transaction::ACEPTADA)
      ->whereIn('document_type', [
        Transaction::FACTURAELECTRONICA,
        Transaction::TIQUETEELECTRONICO
      ])
      ->whereNotNull('transaction_date')
      ->whereMonth('transaction_date', $this->month)
      ->whereYear('transaction_date', $this->lastYear);

    $result = $query->select(
      'banks.name AS bank',
      DB::raw("SUM(
                CASE
                    WHEN transactions.currency_id = 1
                        THEN (transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax)
                    ELSE (transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax) / transactions.proforma_change_type
                END * COALESCE(comisiones.total_percent, 0)
            ) AS total")
    )
      ->groupBy('banks.name')
      ->orderBy('bank')
      ->havingRaw('SUM(
            CASE
                WHEN transactions.currency_id = 1
                    THEN (transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax)
                ELSE (transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax) / transactions.proforma_change_type
            END * COALESCE(comisiones.total_percent, 0)
        ) > 0')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => $item->bank,
        'value' => $item->total
      ];
    })->toArray();

    $caption = 'Honorarios USD facturado por banco';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    if (!empty($this->monthName))
      $subCaption[] = "$this->monthName";
    $subCaption[] = "de {$this->lastYear}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
    ];
  }

  public function getDataStack(): array
  {
    // Definir los códigos como array
    $codigosArray = ['01', '02', '03', '05', '06', '07', '19', '20', '21', '23', '24'];

    // Subconsulta para comisiones
    $comisionesSubquery = DB::table('transactions_commissions as fc')
      ->join('centro_costos as cc', 'fc.centro_costo_id', '=', 'cc.id')
      ->whereIn('cc.codigo', $codigosArray)
      ->select(
        'fc.transaction_id',
        'cc.descrip',  // Mantenemos la descripción para el JOIN
        DB::raw('SUM(fc.percent) / 100 as total_percent')
      )
      ->groupBy('fc.transaction_id', 'cc.descrip');  // Agrupamos por ambos campos

    $query = Transaction::joinSub($comisionesSubquery, 'comisiones', function ($join) {
      $join->on('transactions.id', '=', 'comisiones.transaction_id');
    })
      ->where('proforma_status', Transaction::FACTURADA)
      ->whereIn('document_type', [
        Transaction::PROFORMA,
        Transaction::FACTURAELECTRONICA,
        Transaction::TIQUETEELECTRONICO
      ])
      ->whereNotNull('transaction_date')
      ->whereMonth('transaction_date', $this->month)
      ->whereYear('transaction_date', $this->lastYear)
      ->select(
        'comisiones.descrip AS centroCosto',  // Usamos la descripción de la subconsulta
        DB::raw('MONTH(transactions.transaction_date) AS month'),
        DB::raw("SUM(
            CASE
                WHEN transactions.proforma_type = 'HONORARIO' AND transactions.currency_id = 1
                    THEN (transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax) * comisiones.total_percent
                WHEN transactions.proforma_type = 'HONORARIO' AND transactions.currency_id != 1
                    THEN ((transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax) /
                      transactions.proforma_change_type) * comisiones.total_percent
                ELSE 0
            END) AS total_honorario"),
        DB::raw("SUM(
            CASE
                WHEN transactions.proforma_type = 'GASTO' AND transactions.currency_id = 1
                    THEN (transactions.totalTimbres - transactions.totalDiscount + transactions.totalTax) * comisiones.total_percent
                WHEN transactions.proforma_type = 'GASTO' AND transactions.currency_id != 1
                    THEN ((transactions.totalTimbres - transactions.totalDiscount + transactions.totalTax) /
                      transactions.proforma_change_type) * comisiones.total_percent
                ELSE 0
            END) AS total_gasto")
      )
      ->groupBy('comisiones.descrip', DB::raw('MONTH(transactions.transaction_date)'))
      ->orderBy('comisiones.descrip');

    // Filtro por departamento (igual que antes)
    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } elseif (!empty($this->departments)) {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query->get();

    $estructura = $this->getEstructuraGraficoStack($data);

    $caption = 'Honorarios facturados por centro de costo en USD';
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    if (!empty($this->monthName))
      $subCaption[] = "$this->monthName";
    $subCaption[] = "de {$this->lastYear}";

    return [
      'categories' => $estructura['categories'],
      'dataset'    => $estructura['dataset'],
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
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

  public function getEstructuraGraficoStack($stackDataRaw)
  {
    // Convertir la colección a array si es necesario
    $data = $stackDataRaw instanceof \Illuminate\Support\Collection
      ? $stackDataRaw->toArray()
      : $stackDataRaw;

    // Paso 1: Extraer los centros de costo únicos usando colecciones
    $categorias = collect($data)
      ->pluck('centroCosto')
      ->unique()
      ->values()
      ->toArray();

    // Paso 2: Preparar los datos para las series
    $honorarios = [];
    $gastos = [];

    foreach ($categorias as $centro) {
      // Buscar todos los elementos para este centro de costo
      $items = collect($data)->where('centroCosto', $centro);

      // Sumar los valores si hay múltiples registros
      $totalHonorario = $items->sum('total_honorario');
      $totalGasto = $items->sum('total_gasto');

      $honorarios[] = [
        'value' => (float) $totalHonorario
      ];

      $gastos[] = [
        'value' => (float) $totalGasto
      ];
    }

    // Paso 3: Construir la estructura final
    $categories = [
      [
        'category' => array_map(fn($cat) => ['label' => $cat], $categorias)
      ]
    ];

    $dataset = [
      [
        'seriesname' => 'Honorarios',
        'data' => $honorarios
      ],
      [
        'seriesname' => 'Gastos',
        'data' => $gastos
      ]
    ];

    return [
      'categories' => $categories,
      'dataset' => $dataset,
      'caption'    => 'Honoarios facturado por centro de costo USD',
      'subCaption' => "$this->monthName de {$this->lastYear}" .
        (!empty($this->departmentName) ? " | Departamento: {$this->departmentName}" : '')
    ];
  }

  public function getNombreMes()
  {
    // Obtener el nombre del mes
    $monthName = collect($this->months)
      ->firstWhere('id', $this->month)['name'] ?? 'Mes Desconocido';
    return $monthName;
  }

  public function getTransactionTotal(): array
  {
    $query = Transaction::where('status', Transaction::ACEPTADA)
      ->whereIn('document_type', [
        Transaction::FACTURAELECTRONICA,
        Transaction::TIQUETEELECTRONICO
      ])
      ->whereNotNull('transaction_date')
      ->whereMonth('transaction_date', $this->month)
      ->whereYear('transaction_date', $this->lastYear)
      ->select(
        // Total Honorario con IVA
        DB::raw("SUM(
            CASE
                WHEN transactions.currency_id = " . Currency::DOLARES . "
                    THEN transactions.totalComprobante
                WHEN transactions.currency_id != " . Currency::DOLARES . "
                    THEN transactions.totalComprobante / transactions.factura_change_type
                ELSE 0
            END) AS total_honorario_iva"),

        // Total Honorario neto
        DB::raw("SUM(
            CASE
                WHEN transactions.currency_id = " . Currency::DOLARES . "
                    THEN transactions.totalVenta - transactions.totalDiscount
                WHEN transactions.currency_id != " . Currency::DOLARES . "
                    THEN (transactions.totalVenta - transactions.totalDiscount) / transactions.factura_change_type
                ELSE 0
            END) AS total_honorario"),

        // Total IVA (para todos los tipos)
        DB::raw("SUM(
            CASE
                WHEN transactions.currency_id = 1
                    THEN transactions.totalTax
                ELSE transactions.totalTax / transactions.factura_change_type
            END) AS total_iva"),

        // Total Descuentos (para todos los tipos)
        DB::raw("SUM(
            CASE
                WHEN transactions.currency_id = 1
                    THEN transactions.totalDiscount
                ELSE transactions.totalDiscount / transactions.factura_change_type
            END) AS total_descuento"),

        // Conteo correcto de transacciones
        DB::raw("COUNT(DISTINCT transactions.id) as total_transaction")
      );

    $data = $query->first();

    return [
      'total_honorario_iva' => $data ? $data->total_honorario_iva : 0,
      'total_honorario' => $data ? $data->total_honorario : 0,
      'total_gasto' => $data ? $data->total_gasto : 0,
      'total_iva' => $data ? $data->total_iva : 0,
      'total_descuento' => $data ? $data->total_descuento : 0,
      'total_transaction' => $data ? $data->total_transaction : 0
    ];
  }

  private function buildKpiChartStructure(array $kpiData): array
  {
    // Función para formatear valores monetarios
    $formatMoney = function ($value) {
      return '$' . number_format($value, 2, ',', '.');
    };

    // Definir colores para cada indicador
    /*
    $colors = [
      'total_honorario_iva' => '#6baa01',
      'total_honorario' => '#f8bd19',
      'total_gasto' => '#e44a00',
      'total_iva' => '#1aaf5d',
      'total_descuento' => '#f2c500',
      'total_transaction' => '#008ee4'
    ];
    */

    // Etiquetas para cada indicador
    $labels = [
      'total_honorario_iva' => 'Total Facturado Con Iva',
      'total_honorario' => 'Total Facturado Sin Iva',
      'total_iva' => 'IVA',
      'total_descuento' => 'Descuentos',
      'total_transaction' => 'Facturas'
    ];

    // Construir los puntos de datos
    $dataPoints = [];
    foreach ($labels as $key => $label) {
      $isCurrency = $key !== 'total_transaction';

      $dataPoint = [
        'label' => $label,
        'value' => $isCurrency ? (float) $kpiData[$key] : (int) $kpiData[$key],
        //'color' => $colors[$key],
        'tooltext' => $isCurrency
          ? "{$label}: {$formatMoney($kpiData[$key])}"
          : "{$label}: {$kpiData[$key]}"
      ];

      // Configuración especial para Facturas
      if ($key === 'total_transaction') {
        $dataPoint['numberPrefix'] = '';
        $dataPoint['numberScaleValue'] = '';
        $dataPoint['formatNumber'] = '1';
      }

      $dataPoints[] = $dataPoint;
    }

    return $dataPoints;
  }

  public function render()
  {
    return view('livewire.dashboards.honorarios-mes');
  }
}
