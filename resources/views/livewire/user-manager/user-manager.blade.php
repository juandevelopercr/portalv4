<div>
  @if($action == 'list')
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Users') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $totalUser }}</h4>
                <p class="text-success mb-0">(100%)</p>
              </div>
              <small class="mb-0">{{ __('Total Users') }}</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-user bx-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Active Users') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $userActive }}</h4>
                <p class="text-success mb-0">(+{{ $percentActive }}%)</p>
              </div>
              <small class="mb-0">{{ __('Recent analytics') }} </small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-user-check bx-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Duplicate Users') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $userDuplicates }}</h4>
                <p class="text-success mb-0">({{ $percentDuplicate }}%)</p>
              </div>
              <small class="mb-0">{{ __('Recent analytics') }}</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="bx bx-group bx-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Inactive Users') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $notActive }}</h4>
                <p class="text-danger mb-0">(+{{ $percentNoActive }}%)</p>
              </div>
              <small class="mb-0">{{ __('Recent analytics') }}</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-user-voice bx-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <h5 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Users') }}</h5>

    <div class="card-datatable text-nowrap">
      <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">

        <div class="row">
          <div class="col-md-2">
            <div class="ms-n2">
              @include('livewire.includes.table-paginate')
            </div>
          </div>
          <div class="col-md-10">
            <div
              class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0">
              <div class="dt-buttons btn-group flex-wrap">
                <!--/ Dropdown with icon -->
                @can("create-users")
                  @include('livewire.includes.button-create')
                @endcan

                @can("edit-users")
                  @include('livewire.includes.button-edit')
                @endcan

                @can("delete-users")
                  @include('livewire.includes.button-delete', ['textButton' => __('Eliminar')])
                @endcan

                @can("export-users")
                <livewire:users.user-datatable-export />
                @endcan

                <div class="btn-group" role="group" aria-label="DataTable Actions">
                  <!-- Botón para Reiniciar Filtros -->
                    @include('livewire.includes.button-reset-filters')

                    <!-- Botón para Configurar Columnas -->
                    @include('livewire.includes.button-config-columns')
                </div>

                <!-- Renderizar el componente Livewire -->
                @livewire('components.datatable-settings', [
                'datatableName' => 'user-datatable',
                'availableColumns' => $this->columns,
                'perPage' => $this->perPage,
                ],
                key('user-datatable-config'))

              </div>
            </div>
          </div>
        </div>

        @can("view-users")
        <div class="card-datatable table-responsive">
          <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="users-table" style="width: 100%;">
            <thead>
              <tr>
                <th class="control sorting_disabled dtr-hidden" rowspan="1" colspan="1"
                  style="width: 0px; display: none;" aria-label="">
                </th>
                <th class="sorting_disabled dt-checkboxes-cell dt-checkboxes-select-all" rowspan="1" colspan="1"
                  style="width: 18px;" data-col="1" aria-label="">
                  <input type="checkbox" class="form-check-input" id="select-all" wire:click="toggleSelectAll">
                </th>

                @include('livewire.includes.headers', ['columns' => $this->columns])

              </tr>
              <!-- Fila de filtros -->
              <tr>
                @include('livewire.includes.filters', ['columns' => $this->columns])
              </tr>
            </thead>
            <tbody>
              @foreach ($users as $record)
              <tr wire:key='{{ $record->id }}' class="{{ $loop->odd ? 'odd' : 'even' }}">
                <td class="control" style="display: none;" tabindex="0"></td>
                <td class="dt-checkboxes-cell">
                  <input type="checkbox" class="form-check-input" wire:model.live="selectedIds"
                    value="{{ $record->id }}">
                </td>

                @include('livewire.includes.columns', ['columns' => $this->columns, 'record'=>$record])

                @php
                  /*
                <td>
                  @can("edit-users")
                  <button wire:click="edit({{ $record->id }})" wire:loading.attr="disabled"
                    wire:target="edit({{ $record->id }})" class="btn btn-icon item-edit" data-bs-toggle="tooltip"
                    data-bs-offset="0,8" data-bs-placement="top" data-bs-custom-class="tooltip-dark"
                    data-bs-original-title="{{ __('Edit') }}">

                    <!-- Ícono normal (visible cuando no está en loading) -->
                    <span wire:loading.remove wire:target="edit({{ $record->id }})">
                      <i class="bx bx-edit bx-md"></i>
                    </span>

                    <!-- Ícono de carga (visible cuando está en loading) -->
                    <span wire:loading wire:target="edit({{ $record->id }})">
                      <i class="spinner-border spinner-border-sm me-1" role="status"></i>
                    </span>
                  </button>
                  @endcan

                  <!-- Botón para Enviar Credenciales por Email -->
                  @can("edit-users")
                  <button wire:click.prevent="confirmarAccion(
                      {{ $record->id }},
                      'credentialSend',
                      '{{ __('Are you sure you want to send the login credentials to the system?') }}',
                      '{{ __("After confirmation a new password will be generated and sent to the user's email address") }}',
                      '{{ __('Yes, proceed') }}')" class="btn btn-icon item-trash text-primary" data-bs-toggle="tooltip"
                    data-bs-offset="0,8" data-bs-placement="top" data-bs-custom-class="tooltip-dark"
                    data-bs-original-title="{{ __('Enviar credenciales de acceso') }}">
                    <i class="bx bx-envelope"></i> <!-- Icono de Email -->
                  </button>
                  @endcan

                  @can("delete-users")
                  @if ($record->id != 1)
                  <button wire:click.prevent="confirmarAccion(
                              {{ $record->id }},
                              'delete',
                              '{{ __('Are you sure you want to delete this record') }} ?',
                              '{{ __('After confirmation, the record will be deleted') }}',
                              '{{ __('Yes, proceed') }}'
                            )" class="btn btn-icon item-trash text-danger" data-bs-toggle="tooltip"
                    data-bs-offset="0,8" data-bs-placement="top" data-bs-custom-class="tooltip-dark"
                    data-bs-original-title="{{ __('Delete') }}">
                    <i class="bx bx-trash bx-md"></i>
                  </button>
                  @endif
                  @endcan
                </td>
                */
                @endphp
              </tr>
              @endforeach
            </tbody>
          </table>

          <div class="row overflow-y-scroll" wire:scroll>
            {{ $users->links(data: ['scrollTo' => false]) }}
          </div>
        </div>
        @endcan
      </div>
      <div style="width: 1%;"></div>
    </div>
  </div>
  @endif

  @if($action == 'create' || $action == 'edit')
  @include('livewire.user-manager.partials.user-form')
  @endif
</div>

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('users-table').innerHTML;
      const originalContent = document.body.innerHTML;

      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
  }
</script>
@endpush
