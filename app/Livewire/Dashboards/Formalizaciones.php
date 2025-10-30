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

class Formalizaciones extends Component
{
  public $department;
  public $departments = [];
  public $years = [];      // Lista de años disponibles para el filtro
  public $year;            // Año
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
    $this->years = Caso::select(DB::raw('YEAR(fecha_creacion) as year'))
      ->whereNotNull('fecha_creacion')
      ->distinct()
      ->orderBy('year', 'asc')
      ->pluck('year')
      ->toArray();

    // Año actual y anterior como valores por defecto
    // Obtener la fecha actual con Carbon
    $now = Carbon::now();

    // Obtener el mes actual (formato: '01' a '12')
    $this->month = $now->format('m');
    $this->year = $now->year; // o $now->format('Y');

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

    $pie_formalizaciones_mes_usd  = $this->getDataPieFormalizacionesMes(Currency::DOLARES);
    $pie_formalizaciones_mes_crc  = $this->getDataPieFormalizacionesMes(Currency::COLONES);

    $pie_formalizaciones_year_usd  = $this->getDataPieFormalizacionesYear(Currency::DOLARES);
    $pie_formalizaciones_year_crc  = $this->getDataPieFormalizacionesYear(Currency::COLONES);

    return [
      'pie_formalizaciones_mes_usd'  => $pie_formalizaciones_mes_usd,
      'pie_formalizaciones_mes_crc'  => $pie_formalizaciones_mes_crc,
      'pie_formalizaciones_year_usd' => $pie_formalizaciones_year_usd,
      'pie_formalizaciones_year_crc' => $pie_formalizaciones_year_crc
    ];
  }

  public function getDataPieFormalizacionesMes($currency): array
  {
    $query = Caso::select(
      'banks.name AS bank',
      DB::raw('COUNT(*) AS total')
    )
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->where('casos.currency_id', $currency)
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

    $caption = 'Formalizaciones ' . (($currency == Currency::DOLARES) ? 'USD' : 'CRC');
    //dd($caption);
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    if (!empty($this->monthName))
      $subCaption[] = "$this->monthName";
    $subCaption[] = "de {$this->year}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
    ];
  }

  public function getDataPieFormalizacionesYear($currency): array
  {
    $query = Caso::select(
      'banks.name AS bank',
      DB::raw('COUNT(*) AS total')
    )
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->where('casos.currency_id', $currency)
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

    $caption = 'Formalizaciones ' . (($currency == Currency::DOLARES) ? 'USD' : 'CRC');
    $subCaption = [];

    if (!empty($this->departmentName)) {
      $subCaption[] = "Departamento: {$this->departmentName}";
    }

    $subCaption[] = "Total {$this->year}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
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
    return view('livewire.dashboards.formalizaciones');
  }
}
