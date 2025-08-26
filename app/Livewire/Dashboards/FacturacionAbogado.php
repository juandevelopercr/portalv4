<?php

namespace App\Livewire\Dashboards;

use App\Models\Bank;
use App\Models\Caratula;
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

class FacturacionAbogado extends Component
{
  public $department;
  public $departments = [];
  public $years = [];      // Lista de años disponibles para el filtro
  public $months = [];      // Lista de meses disponibles para el filtro
  public $year;            // Año del filtro (por defecto, año anterior al actual)
  public $month;        // Mes del filtro (por defecto, mes actual)
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

    $mscolumn3d_facturas_usd = $this->getDataMsColumn3dFacturas(Currency::DOLARES);
    $mscolumn3d_facturas_crc = $this->getDataMsColumn3dFacturas(Currency::COLONES);

    return [
      'mscolumn3d_facturas_usd' => $mscolumn3d_facturas_usd,
      'mscolumn3d_facturas_crc' => $mscolumn3d_facturas_crc
    ];
  }

  public function getDataMsColumn3dFacturas($currency): array
  {
    $query = Transaction::select(
      'users.name as abogado',
      DB::raw("SUM(
            CASE
                WHEN transactions.currency_id = 1 
                    THEN (transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax)
                ELSE ((transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax) / 
                  transactions.proforma_change_type) 
            END
        ) AS total_usd"),
      DB::raw("SUM(
            CASE
                WHEN transactions.currency_id = 1 
                    THEN (transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax) * transactions.proforma_change_type
                ELSE transactions.totalHonorarios - transactions.totalDiscount + transactions.totalTax 
            END
        ) AS total_crc")
    )
      ->join('casos', 'transactions.caso_id', '=', 'casos.id')
      ->join('users', 'casos.abogado_cargo_id', '=', 'users.id')
      ->whereNotNull('transaction_date')
      ->whereYear('transaction_date', '=', $this->year)
      ->whereMonth('transaction_date', '=', $this->month)
      ->where('users.active', 1);

    if (!empty($this->department)) {
      $query->where('transactions.department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('transactions.department_id', $ids);
      }
    }

    if ($currency == Currency::DOLARES)
      $data = $query
        ->groupBy('users.name')
        ->orderBy('total_usd')
        ->get();
    else
      $data = $query
        ->groupBy('users.name')
        ->orderBy('total_crc')
        ->get();

    // Crear estructura para FusionCharts
    $categories = $data->map(function ($item) {
      return ['label' => $item->abogado];
    })->toArray();

    $values = $data->map(function ($item) use ($currency) {
      return ['value' => $currency == Currency::DOLARES ? $item->total_usd : $item->total_crc];
    })->toArray();

    $estructura = [
      'categories' => [['category' => $categories]],
      'dataset' => [
        [
          'seriesname' => 'Facturación',
          'data' => $values
        ]
      ]
    ];

    $caption = 'Facturación por abogados ' . ($currency == Currency::DOLARES ? 'USD' : 'CRC');
    $subCaptionParts = [];

    $monthName = Carbon::createFromDate($this->year, $this->month, 1)
      ->locale('es')
      ->monthName;

    if (!empty($this->departmentName)) {
      $subCaptionParts[] = "Departamento: {$this->departmentName}";
    }

    $subCaptionParts[] = "{$monthName} de $this->year";

    return [
      'data' => $estructura,
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaptionParts),
    ];
  }

  public function getEstructuraGraficoMscolumn3d($results)
  {
    $categories = [];
    $firmasData = [];
    $caratulasData = [];
    $revisionesData = [];

    // Recopilar datos por abogado
    foreach ($results as $registro) {
      $categories[] = ['label' => $registro->abogado];
      $firmasData[] = ['value' => (int)$registro->firmas];
      $caratulasData[] = ['value' => (int)$registro->caratulas];
      $revisionesData[] = ['value' => (int)$registro->revisiones];
    }

    // Construir dataset para FusionCharts
    $dataset = [
      [
        'seriesname' => 'Firmas',
        'data' => $firmasData
      ],
      [
        'seriesname' => 'Carátulas',
        'data' => $caratulasData
      ],
      [
        'seriesname' => 'Revisiones',
        'data' => $revisionesData
      ]
    ];

    return [
      'categories' => [['category' => $categories]],
      'dataset' => $dataset
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.facturacion-abogado');
  }
}
