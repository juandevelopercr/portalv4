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

class CargaTrabajo extends Component
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

    $mscolumn3d_firmas = $this->getDataMsColumn3dFirmas();
    $mscolumn3d_caratulas = $this->getDataMsColumn3dCaratulas();
    $mscolumn3d_precaratulas = $this->getDataMsColumn3dPreCaratulas();
    $mscolumn3d_cargatrabajo = $this->getDataMsColumn3dCargatRabajo();

    return [
      'mscolumn3d_firmas' => $mscolumn3d_firmas,
      'mscolumn3d_caratulas' => $mscolumn3d_caratulas,
      'mscolumn3d_precaratulas' => $mscolumn3d_precaratulas,
      'mscolumn3d_cargatrabajo' => $mscolumn3d_cargatrabajo
    ];
  }

  public function getDataMsColumn3dFirmas(): array
  {
    $query = Caso::select(
      'users.name as abogado',
      DB::raw('COUNT(*) AS total')
    )
      ->join('users', 'casos.abogado_cargo_id', '=', 'users.id')
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', '=', $this->year)
      ->whereMonth('fecha_firma', '=', $this->month)
      ->where('users.active', 1);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy('users.name')
      ->orderBy('total')
      ->get();

    // Crear estructura para FusionCharts
    $categories = $data->map(function ($item) {
      return ['label' => $item->abogado];
    })->toArray();

    $values = $data->map(function ($item) {
      return ['value' => (int)$item->total];
    })->toArray();

    $estructura = [
      'categories' => [['category' => $categories]],
      'dataset' => [
        [
          'seriesname' => 'Firmas',
          'data' => $values
        ]
      ]
    ];

    $caption = 'Firmas por abogados';
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

  public function getDataMsColumn3dCaratulas(): array
  {
    $query = Caso::select(
      'users.name as abogado',
      DB::raw('COUNT(*) AS total')
    )
      ->join('users', 'casos.abogado_cargo_id', '=', 'users.id')
      ->whereNotNull('fecha_caratula')
      ->whereYear('fecha_caratula', '=', $this->year)
      ->whereMonth('fecha_caratula', '=', $this->month)
      ->where('caratula_id', Caratula::CARATULA)
      ->where('users.active', 1);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy('users.name')
      ->orderBy('total')
      ->get();

    // Crear estructura para FusionCharts
    $categories = $data->map(function ($item) {
      return ['label' => $item->abogado];
    })->toArray();

    $values = $data->map(function ($item) {
      return ['value' => (int)$item->total];
    })->toArray();

    $estructura = [
      'categories' => [['category' => $categories]],
      'dataset' => [
        [
          'seriesname' => 'Carátulas',
          'data' => $values
        ]
      ]
    ];

    $caption = 'Carátulas por abogados';
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

  public function getDataMsColumn3dPreCaratulas(): array
  {
    $query = Caso::select(
      'users.name as abogado',
      DB::raw('COUNT(*) AS total')
    )
      ->join('users', 'casos.abogado_cargo_id', '=', 'users.id')
      ->whereNotNull('fecha_precaratula')
      ->whereYear('fecha_precaratula', '=', $this->year)
      ->whereMonth('fecha_precaratula', '=', $this->month)
      ->where('caratula_id', Caratula::PRECARATULA)
      ->where('users.active', 1);

    if (!empty($this->department)) {
      $query->where('department_id', $this->department);
    } else {
      $ids = collect($this->departments)->pluck('id')->toArray();
      if (!empty($ids)) {
        $query->whereIn('department_id', $ids);
      }
    }

    $data = $query
      ->groupBy('users.name')
      ->orderBy('total')
      ->get();

    // Crear estructura para FusionCharts
    $categories = $data->map(function ($item) {
      return ['label' => $item->abogado];
    })->toArray();

    $values = $data->map(function ($item) {
      return ['value' => (int)$item->total];
    })->toArray();

    $estructura = [
      'categories' => [['category' => $categories]],
      'dataset' => [
        [
          'seriesname' => 'Carátulas',
          'data' => $values
        ]
      ]
    ];

    $caption = 'Pre Carátulas por abogados';
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

  public function getDataMsColumn3dCargatRabajo(): array
  {
    $year = $this->year;
    $caratulaType = Caratula::CARATULA;

    $departmentIds = [];
    if (!empty($this->department)) {
      $departmentIds = [$this->department];
    } elseif (!empty($this->departments)) {
      $departmentIds = collect($this->departments)->pluck('id')->toArray();
    }

    // Precalcular los conteos por separado
    $firmas = Caso::select('abogado_cargo_id', DB::raw('COUNT(DISTINCT id) as count'))
      ->whereNotNull('fecha_firma')
      ->whereYear('fecha_firma', $year)
      ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
      ->groupBy('abogado_cargo_id')
      ->get()
      ->keyBy('abogado_cargo_id');

    $caratulas = Caso::select('abogado_cargo_id', DB::raw('COUNT(DISTINCT id) as count'))
      ->whereNotNull('fecha_caratula')
      ->where('caratula_id', $caratulaType)
      ->whereYear('fecha_caratula', $year)
      ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
      ->groupBy('abogado_cargo_id')
      ->get()
      ->keyBy('abogado_cargo_id');

    $revisiones = Caso::select('abogado_revisor_id', DB::raw('COUNT(DISTINCT id) as count'))
      ->whereNotNull('fecha_creacion')
      ->whereYear('fecha_creacion', $year)
      ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
      ->groupBy('abogado_revisor_id')
      ->get()
      ->keyBy('abogado_revisor_id');

    // Combinar los resultados
    $results = User::select('id', 'name')
      ->get()
      ->map(function ($user) use ($firmas, $caratulas, $revisiones) {
        return (object) [
          'abogado' => $user->name,
          'firmas' => $firmas->get($user->id)?->count ?? 0,
          'caratulas' => $caratulas->get($user->id)?->count ?? 0,
          'revisiones' => $revisiones->get($user->id)?->count ?? 0,
        ];
      })
      ->filter(fn($item) => $item->firmas > 0 || $item->caratulas > 0 || $item->revisiones > 0)
      ->sortBy('abogado');

    $estructura = $this->getEstructuraGraficoMscolumn3d($results);

    $caption = 'Carga de trabajo por abogado';
    $subCaptionParts = [];

    if (!empty($this->departmentName)) {
      $subCaptionParts[] = "Departamento: {$this->departmentName}";
    }

    $subCaptionParts[] = "{$year}";

    return [
      'data' => $estructura,
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaptionParts)
    ];
  }

  /*
  public function getEstructuraGraficoMscolumn3d($results)
  {
    $categories = [];
    foreach ($results as $d) {
      $categories[] = ['label' => (string)$d->abogado];
    }

    // Estructura para FusionCharts
    $data = [
      'categories' => [['category' => $categories]],
      'dataset' => []
    ];

    // Rellenar dataset por cada año
    foreach ($results as $abogado => $registros) {

      $data['dataset'][] = [
        'seriesname' => (string) $abogado,
        'data' => collect($dataPorMes)->map(fn($val) => ['value' => $val])->values()->toArray()
      ];
    }

    return $data;
  }
    */

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
    return view('livewire.dashboards.carga-trabajo');
  }
}
