<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('Please fix the following errors:') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row g-6">
      <div class="col-md-4">
          <label class="form-label" for="identification_type_id">{{ __('Identification Type') }}</label>
          <div wire:ignore>
          <select
              class="form-select identification_type-select"
              id="identification_type_id"
              wire:model="identification_type_id">
              <option value="">{{ __('Select...') }}</option>
              @foreach ($this->identificationTypes as $identificationType)
                <option value="{{ $identificationType->id }}">{{ $identificationType->code.'-'.$identificationType->name }}</option>
              @endforeach
          </select>
          </div>
          @error('identification_type_id')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
      <div class="col-md-4 fv-plugins-icon-container">
          <label class="form-label" for="identification">{{ __('Identification') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-user"></i></span>
              <input type="text" wire:model="identification" id="identification"
                    class="form-control @error('identification') is-invalid @enderror" placeholder="{{ __('Identification') }}"
                    aria-label="{{ __('Identification') }}">

              <!-- Botón con spinner -->
              <button type="button" class="btn btn-primary" wire:click="searchClient('cedula')" wire:loading.attr="disabled">
                  <!-- Spinner visible mientras se ejecuta la acción -->
                  <span wire:loading.remove wire:target="searchClient('cedula')">
                      {{ __('Find') }}
                  </span>
                  <span wire:loading wire:target="searchClient('cedula')">
                      <div class="spinner-border spinner-border-sm" role="status"></div>
                  </span>
              </button>
          </div>
          @error('identification')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
          <label class="form-label" for="name">{{ __('Name') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-user"></i></span>
              <input type="text" wire:model="name" id="name"
                    class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}"
                    aria-label="{{ __('Name') }}">

              <!-- Botón con spinner -->
              <button type="button" class="btn btn-primary" wire:click="searchClient('name')" wire:loading.attr="disabled">
                  <!-- Spinner visible mientras se ejecuta la acción -->
                  <span wire:loading.remove wire:target="searchClient('name')">
                      {{ __('Find') }}
                  </span>
                  <span wire:loading wire:target="searchClient('name')">
                      <div class="spinner-border spinner-border-sm" role="status"></div>
                  </span>
              </button>
          </div>
          @error('name')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="commercial_name">{{ __('Commercial Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span id="spancommercialname" class="input-group-text"><i class="bx bx-store"></i></span>
          <input type="text" wire:model="commercial_name" name="commercial_name"
            class="form-control @error('commercial_name') is-invalid @enderror"
            placeholder="{{ __('Commercial Name') }}" aria-label="{{ __('Commercial Name') }}"
            aria-describedby="spancommercialname">
        </div>
        @error('commercial_name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="email">{{ __('Email') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-envelope"></i></span>
          <input type="text" wire:model="email" id="email" name="email"
            class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email') }}"
            aria-label="{{ __('Email') }}">
        </div>
        @error('email')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">
          {{ __('You can use letters and numbers') }}
        </div>
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'condition_sale_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="condition_sale_id">{{ __('Condition Sale') }}</label>
        <select x-ref="select" id="condition_sale_id"
                class="select2 form-select @error('condition_sale_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->conditionSeles as $conditionSele)
            <option value="{{ $conditionSele->id }}">{{ $conditionSele->code.'-'.$conditionSele->name }}</option>
          @endforeach
        </select>
        @error('condition_sale_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'pay_term_number',
            postUpdate: true
          })"
          x-init="init($refs.select)">
        <label class="form-label" for="pay_term_number">{{ __('Credit Days') }}</label>
        <select x-ref="select" id="pay_term_number"
                {{ $condition_sale_id != '02' ? 'disabled' : '' }}
                class="select2 form-select @error('pay_term_number') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          <option value="8">8</option>
          <option value="15">15</option>
          <option value="22">22</option>
          <option value="30">30</option>
          <option value="45">45</option>
          <option value="60">60</option>
        </select>
        @error('pay_term_number')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-8 fv-plugins-icon-container"
          x-data="select2LivewireMultipleWithToggle({
            wireModelName: 'economicActivities',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="economicActivities">{{ __('Economic Activity') }}</label>
        <select x-ref="select" id="economicActivities"
                class="form-select"
                multiple>
          @foreach ($this->listEconomicActivities as $activities)
            <option value="{{ $activities->id }}">{{ $activities->code.'-'.$activities->name }}</option>
          @endforeach
        </select>
        @error('economicActivities')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
          <label class="form-label" for="invoice_type">{{ __('Invoice Type') }}</label>
          <div wire:ignore>
          <select
              class="form-select invoice_type-select"
              id="invoice_type"
              wire:model="invoice_type">
              <option value="">{{ __('Seleccione...') }}</option>
              @foreach ($this->invoicesTypes as $type)
                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
              @endforeach
          </select>
          </div>
          @error('invoice_type')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 my-7">

          <input type="checkbox" class="form-check-input" id="aplicarImpuesto" wire:model.defer="aplicarImpuesto" {{ $aplicarImpuesto==1
            ? 'checked' : '' }} />

          <label for="future-billing" class="switch-label">{{ __('Aplicar impuesto') }}</label>
        </div>
      </div>
    </div>

    <br>
    <h6>2. {{ __('Address') }}</h6>
    <div class="row g-6">
      <div class="col-md-12 fv-plugins-icon-container">
        <label class="form-label" for="address">{{ __('Address') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span id="spancommercialname" class="input-group-text"><i class="bx bx-store"></i></span>
          <input type="text" wire:model="address" name="address"
            class="form-control @error('address') is-invalid @enderror"
            placeholder="{{ __('address Name') }}" aria-label="{{ __('Address') }}">
        </div>
        @error('address')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'country_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="country_id">{{ __('Country') }}</label>
        <select x-ref="select" id="country_id"
                class="select2 form-select @error('country_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->countries as $country)
            <option value="{{ $country->id }}">{{ $country->name }}</option>
          @endforeach
        </select>
        @error('country_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'province_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="province_id">{{ __('Province') }}</label>
        <select x-ref="select" id="province_id"
                class="select2 form-select @error('province_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->provinces as $province)
            <option value="{{ $province->id }}">{{ $province->code.'-'.$province->name }}</option>
          @endforeach
        </select>
        @error('province_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>


      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'canton_id',
            postUpdate: true
          })"
          x-init="init($refs.select)">
        <label class="form-label" for="canton_id">{{ __('Cantón') }}</label>
        <select x-ref="select" id="canton_id"
                class="select2 form-select @error('canton_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->cantons as $canton)
            <option value="{{ $canton->id }}">{{ $canton->name }}</option>
          @endforeach
        </select>
        @error('canton_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'district_id',
            postUpdate: true
          })"
          x-init="init($refs.select)">
        <label class="form-label" for="district_id">{{ __('District') }}</label>
        <select x-ref="select" id="district_id"
                class="select2 form-select @error('district_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->districts as $district)
            <option value="{{ $district->id }}">{{ $district->code.'-'.$district->name }}</option>
          @endforeach
        </select>
        @error('district_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="phone">{{ __('Phone') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-phone"></i></span>
          <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror"
            placeholder="{{ __('Phone') }}" aria-label="{{ __('Phone') }}">
        </div>
        @error('phone')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="other_signs">{{ __('Other Signs') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-flag"></i></span>
          <input type="text" wire:model="other_signs" id="other_signs" name="other_signs"
            class="form-control @error('other_signs') is-invalid @enderror" placeholder="{{ __('Other Signs') }}"
            aria-label="{{ __('Other Signs') }}">
        </div>
        @error('other_signs')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-8 fv-plugins-icon-container">
        <label class="form-label" for="email_cc">{{ __('Email CC') }}</label>
        <textarea class="form-control @if(count($invalidEmails)) is-invalid @endif"
          wire:model.live.debounce.600ms="email_cc" name="email_cc" id="email_cc" rows="2"
          placeholder="{{ __('Email CC') }}">
              </textarea>
        @error('email_cc')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <!-- Mostrar correos inválidos -->
        @if(count($invalidEmails))
        <div class="mt-1 text-danger form-text">
          <strong>{{ __('Invalid Emails') }}:</strong>
          <ul>
            @foreach ($invalidEmails as $email)
            <li>{{ $email }}</li>
            @endforeach
          </ul>
        </div>
        @endif
      </div>

    </div>
    <br>
    <div class="row g-6">
      <div class="col-md-6 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 my-2">

          <input type="checkbox" class="form-check-input" id="active" wire:model.defer="active" {{ $active==1
            ? 'checked' : '' }} />

          <label for="future-billing" class="switch-label">{{ __('Active') }}</label>
        </div>
      </div>
    </div>

    <br>
    <div class="row g-6">
      <div class="pt-6">
        {{-- Incluye botones de guardar y guardar y cerrar --}}
        @include('livewire.includes.button-saveAndSaveAndClose')

        <!-- Botón Cancel -->
        <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="cancel"
          wire:loading.attr="disabled" wire:target="cancel">
          <span wire:loading.remove wire:target="cancel">
            <span class="fa fa-remove bx-18px me-2"></span>{{ __('Cancel') }}
          </span>
          <span wire:loading wire:target="cancel">
            <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Cancelling...') }}
          </span>
        </button>
      </div>
    </div>
  </form>
</div>

<!-- Modal de resultados -->
@if ($showModalCedula)
    <div class="modal fade show" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Seleccionar Cliente</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModalCedula', false)" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- DataTable with Buttons -->
                    <div class="card">
                        <div class="card-datatable table-responsive">
                            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                                <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="identification-table" style="width: 100%;">
                                    <thead>
                                        <tr>
                                          <th>#</th>
                                          <th>{{ __('Name') }}</th>
                                          <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($searchResults as $index => $client)
                                        <tr wire:key='{{ $index }}' class="{{ $loop->odd ? 'odd' : 'even' }}">
                                            <td>{{ $index+1 }}</td>
                                            <td>{{ $client['fullname'] }}</td>
                                            <td>
                                                <div class="action-icons d-flex align-items-center">
                                                    <button wire:click="selectClient({{ $index }})"
                                                        class="btn btn-primary btn-sm">
                                                        {{ __('Select') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div style="width: 1%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
