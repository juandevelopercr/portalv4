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

class FacturacionCentroCosto extends Component
{
  public $department;
  public $departments = [];
  public $years = [];      // Lista de años disponibles para el filtro
  public $year;            // Año (por defecto, año anterior al actual)
  public $month;           // Mes de análisis (por defecto mes actual)
  public $chartTheme = 'zune'; // Valor por defecto
  public $chartsPerRow = 1; // por defecto 2 gráficos por fila
  public $departmentName;
  public $months;
  public $monthName;

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
    // Obtener la fecha actual con Carbon
    $now = Carbon::now();

    // Obtener el mes actual (formato: '01' a '12')
    $this->month = $now->format('m');
    $this->year = $now->year;

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
    $this->monthName = $this->getNombreMes();

    $stackedbar3d = $this->getDataStack();

    return [
      'stackedbar3d' => $stackedbar3d
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
      ->whereYear('transaction_date', $this->year)
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
    $subCaption[] = "de {$this->year}";

    return [
      'categories' => $estructura['categories'],
      'dataset'    => $estructura['dataset'],
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
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
      'subCaption' => "$this->monthName de {$this->year}" .
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

  public function render()
  {
    return view('livewire.dashboards.facturacion-centro-costo');
  }
}
