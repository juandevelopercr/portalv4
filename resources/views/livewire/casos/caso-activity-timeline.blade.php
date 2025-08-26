@php
  use Carbon\Carbon;
  use Illuminate\Support\Facades\Lang;
  use App\Models\User;
  use App\Models\Currency;
  use App\Models\Department;
  use App\Models\CasoEstado;
  use App\Models\Caratula;
  use App\Models\Garantia;
  use App\Models\Bank;

  $camposFecha = [
    'fecha_creacion', 'fecha_firma', 'fecha_presentacion', 'fecha_inscripcion', 'fecha_entrega',
    'fecha_caratula', 'fecha_precaratula', 'created_at', 'updated_at'
  ];

  $colors = ['primary', 'success', 'danger', 'info', 'warning', 'secondary'];
  $colorIndex = 0;

  // Relational value maps
  $users = User::pluck('name', 'id')->toArray();
  $currencies = Currency::pluck('code', 'id')->toArray();
  $departments = Department::pluck('name', 'id')->toArray();
  $estados = CasoEstado::pluck('name', 'id')->toArray();
  $caratulas = Caratula::pluck('name', 'id')->toArray();
  $garantias = Garantia::pluck('name', 'id')->toArray();
  $banks = Bank::pluck('name', 'id')->toArray();

  $mostrar = function($campo, $valor) use ($users, $currencies, $departments, $estados, $caratulas, $garantias, $banks) {
    if ($valor === null) return '—';
    return match($campo) {
      'abogado_cargo_id',
      'abogado_revisor_id',
      'abogado_formalizador_id',
      'asistente_id' => $users[$valor] ?? $valor,
      'currency_id' => $currencies[$valor] ?? $valor,
      'department_id' => $departments[$valor] ?? $valor,
      'estado_id' => $estados[$valor] ?? $valor,
      'caratula_id' => $caratulas[$valor] ?? $valor,
      'garantia_id' => $garantias[$valor] ?? $valor,
      'bank_id' => $banks[$valor] ?? $valor,
      default => $valor,
    };
  };
@endphp

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <h5 class="card-header">Historial de Actividad</h5>
      <div class="card-body">
        <ul class="timeline mb-0">
          @foreach ($logs as $log)
            @php
              $currentColor = $colors[$colorIndex % count($colors)];
              $colorIndex++;

              $eventLabel = match ($log->event) {
                'created' => 'creado',
                'updated' => 'actualizado',
                'deleted' => 'eliminado',
                default => $log->event,
              };
            @endphp

            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-{{ $currentColor }}"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-3">
                  <h6 class="mb-0">El caso ha sido {{ $eventLabel }}</h6>
                  <small class="text-muted">
                    {{ Carbon::parse($log->created_at)->translatedFormat('j \d\e F \d\e Y, g:i A') }}
                  </small>
                </div>

                @php
                  $causer = $log->causer;
                  $properties = $log->properties;
                  $old = $properties['old'] ?? [];
                  $new = $properties['attributes'] ?? [];
                  $states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                  $state = $states[array_rand($states)];
                  $name = $causer->name ?? 'Usuario';
                  preg_match_all('/\b\w/', $name, $matches);
                  $initials = strtoupper(substr(implode('', $matches[0]), 0, 2));
                @endphp

                <div class="d-flex align-items-center mb-2">
                  <div class="avatar me-2">
                    @if ($causer?->profile_photo_path)
                      <img src="{{ asset('storage/assets/img/avatars/' . $causer->profile_photo_path) }}" alt="Avatar" class="rounded-circle" />
                    @else
                      <span class="avatar-initial rounded-circle bg-label-{{ $state }}">{{ $initials }}</span>
                    @endif
                  </div>
                  <div class="d-flex flex-column">
                    <span class="emp_name text-truncate">{{ $causer->name ?? '—' }}</span>
                    <small class="emp_post text-truncate text-muted">
                      @foreach ($causer?->getRoleNames() ?? [] as $role)
                        {{ $role }}<br>
                      @endforeach
                    </small>
                  </div>
                </div>

                @if ($new)
                  <p class="mb-2">Se modificaron los siguientes campos:</p>
                  <ul class="list-group list-group-flush">
                    @foreach ($new as $field => $newValue)
                      @php
                        $isDate = in_array($field, $camposFecha);
                        $label = Lang::has('fields.' . $field) ? __('fields.' . $field) : ucwords(str_replace('_', ' ', $field));
                      @endphp
                      <li class="list-group-item d-flex justify-content-between align-items-start border-top-0 p-0">
                        <div class="me-2">
                          <strong>{{ $label }}</strong><br>
                          <small class="text-muted">Antes:</small>
                          {{ isset($old[$field]) ? ($isDate ? Carbon::parse($old[$field])->translatedFormat('j \d\e F \d\e Y, g:i A') : $mostrar($field, $old[$field])) : '—' }}<br>
                          <small class="text-muted">Ahora:</small>
                          {{ $isDate ? Carbon::parse($newValue)->translatedFormat('j \d\e F \d\e Y, g:i A') : $mostrar($field, $newValue) }}
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @endif
              </div>
            </li>
          @endforeach
        </ul>

        <div class="mt-3">
          {{ $logs->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
