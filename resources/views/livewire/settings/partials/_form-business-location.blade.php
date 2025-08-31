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
      <div class="col-md-2 fv-plugins-icon-container">
          <label class="form-label" for="code">{{ __('Code') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-code"></i></span>
              <input type="text" wire:model="code" id="code"
                  class="form-control @error('code') is-invalid @enderror" placeholder="{{ __('Code') }}">
          </div>
          @error('code')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
      <div class="col-md-5 fv-plugins-icon-container">
          <label class="form-label" for="name">{{ __('Name') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-user"></i></span>
              <input type="text" wire:model="name" id="name"
                  class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}">
          </div>
          @error('name')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>

      <div class="col-md-5 fv-plugins-icon-container">
        <label class="form-label" for="commercial_name">{{ __('Commercial Name') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-user"></i></span>
            <input type="text" wire:model="commercial_name" id="commercial_name"
                class="form-control @error('commercial_name') is-invalid @enderror" placeholder="{{ __('Commercial Name') }}">
        </div>
        @error('commercial_name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>
    <br>
    <div class="row g-6">
      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'identification_type_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="identification_type_id">{{ __('Identification Type') }}</label>
        <select x-ref="select" id="identification_type_id"
                class="select2 form-select @error('identification_type_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->identificationTypes as $identificationType)
          <option value="{{ $identificationType->id }}">{{ $identificationType->code.'-'.$identificationType->name }}</option>
          @endforeach
        </select>
        @error('identification_type_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="identification">{{ __('Identification') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span id="spanidentification" class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="identification" name="identification"
            class="form-control @error('identification') is-invalid @enderror" placeholder="{{ __('Identification') }}"
            aria-label="{{ __('Identification') }}" aria-describedby="spanidentification">
        </div>
        @error('identification')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="numero_sucursal">{{ __('Número de Sucursal') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $numero_sucursal ?? '' }}',
            wireModelName: 'numero_sucursal',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('numero_sucursal', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="numero_sucursal" x-ref="cleaveInput" wire:ignore class="form-control js-input-numero_sucursal" />
          </div>
        </div>
        @error('numero_sucursal')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="numero_punto_venta">{{ __('Número de caja') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $numero_punto_venta ?? '' }}',
            wireModelName: 'numero_punto_venta',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('numero_punto_venta', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-dollar"></i></span>
            <input type="text" id="numero_punto_venta" x-ref="cleaveInput" wire:ignore class="form-control js-input-numero_punto_venta" />
          </div>
        </div>
        @error('numero_punto_venta')
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

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="registrofiscal8707">{{ __('Tax Registration 8707') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-user"></i></span>
            <input type="text" wire:model="registrofiscal8707" id="registrofiscal8707"
                class="form-control @error('registrofiscal8707') is-invalid @enderror" placeholder="{{ __('Tax Registration 8707') }}">
        </div>
        @error('registrofiscal8707')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>
    <br>
    <h6>2. {{ __('Address') }}</h6>
    <div class="row g-6">
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
            <option value="{{ $province->id }}">{{ $province->name }}</option>
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
        <label class="form-label" for="canton_id">{{ __('District') }}</label>
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
        <label class="form-label" for="other_signs">{{ __('Other Signs') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-envelope"></i></span>
          <input type="text" wire:model="other_signs" id="other_signs" name="other_signs"
            class="form-control @error('other_signs') is-invalid @enderror" placeholder="{{ __('Other Signs') }}"
            aria-label="{{ __('Other Signs') }}">
        </div>
        @error('other_signs')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <br>
      <h6>3. {{ __('Hacienda Data') }}</h6>
        <div class="row">
          <div class="col-md-6">
            <div class="card-body">
              <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">
                {{-- Mostrar un icono de descarga si se ha subido un archivo .p12 --}}
                @if ($certificate_digital_file && method_exists($certificate_digital_file, 'temporaryUrl'))
                    {{-- Icono de descarga para archivos temporales (nuevos) --}}
                    {{--
                    <a href="{{ $certificate_digital_file->temporaryUrl() }}" download class="d-block w-px-100 h-px-100 rounded text-decoration-none">
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light rounded">
                            <i class="bx bx-download text-primary" style="font-size: 2.5rem;"></i>
                            <span class="mt-2">{{ __('Download') }}</span>
                        </div>
                    </a>
                    --}}
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light rounded">
                            <i class="bx bx-download text-primary" style="font-size: 2.5rem;"></i>
                            <span class="mt-2">{{ __('Archivo Cargado') }}</span>
                        </div>

                @elseif ($certificate_digital_file)
                    {{-- Icono de descarga para archivos ya guardados --}}
                    <a href="{{ asset('storage/assets/certificates/' . $certificate_digital_file) }}" download class="d-block w-px-100 h-px-100 rounded text-decoration-none">
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light rounded">
                            <i class="bx bx-download text-primary" style="font-size: 2.5rem;"></i>
                            <span class="mt-2">{{ __('Download') }}</span>
                        </div>
                    </a>
                @else
                    {{-- Imagen por defecto si no hay archivo --}}
                    <img class="d-block w-px-100 h-px-100 rounded" src="{{ asset('storage/assets/default-image.png') }}"
                        alt="{{ __('Certificate') }}">
                @endif

                <div class="button-wrapper">
                    {{-- Botón para subir el archivo --}}
                    <label for="certificate_digital_file_input" class="btn btn-primary me-3 mb-4" tabindex="0">
                        <span class="d-none d-sm-block">{{ __('Upload Certificate') }}</span>
                        <i class="bx bx-upload d-block d-sm-none"></i>
                    </label>

                    {{-- Input de archivo oculto --}}
                    <input wire:model.live="certificate_digital_file" id="certificate_digital_file_input"
                          class="account-file-input" style="display: none;" accept=".pem,application/x-pem-file" type="file" />

                    {{-- Mensaje de error --}}
                    @error('certificate_digital_file')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror

                    {{-- Botón para resetear el archivo --}}
                    <button type="button" class="btn btn-label-secondary account-image-reset mb-4" wire:click="resetCertificate">
                        <i class="bx bx-reset d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{ __('Reset') }}</span>
                    </button>
                </div>

                {{-- Indicador de carga --}}
                <div class="col" wire:loading.delay wire:target="certificate_digital_file">
                    <!-- Grid -->
                    <div class="sk-grid sk-primary">
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                        <div class="sk-grid-cube"></div>
                    </div>
                    <span>{{ __('Loading, please wait...') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6 fv-plugins-icon-container">
            <label class="form-label" for="certificate_pin">{{ __('Certificate Pin') }}</label>
            <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
              <input type="text" wire:model="certificate_pin" id="certificate_pin" name="certificate_pin"
                class="form-control @error('certificate_pin') is-invalid @enderror" placeholder="{{ __('Certificate Pin') }}"
                aria-label="{{ __('Certificate Pin') }}">
            </div>
            @error('certificate_pin')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>


          <div class="col-md-4 fv-plugins-icon-container">
            <label class="form-label" for="api_user_hacienda">{{ __('User api Hacienda') }}</label>
            <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-user"></i></span>
              <input type="text" wire:model="api_user_hacienda" id="api_user_hacienda" name="api_user_hacienda"
                class="form-control @error('api_user_hacienda') is-invalid @enderror" placeholder="{{ __('User api Hacienda') }}"
                aria-label="{{ __('User api Hacienda') }}">
            </div>
            @error('api_user_hacienda')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4 fv-plugins-icon-container">
            <label class="form-label" for="api_password">{{ __('Password api Hacienda') }}</label>
            <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-key"></i></span>
              <input type="text" wire:model="api_password" id="api_user_hacienda" name="api_password"
                class="form-control @error('api_password') is-invalid @enderror" placeholder="{{ __('Password api Hacienda') }}"
                aria-label="{{ __('Password api Hacienda') }}">
            </div>
            @error('api_password')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4 select2-primary fv-plugins-icon-container"
              x-data="select2Livewire({
                wireModelName: 'environment',
                postUpdate: true
              })"
              x-init="init($refs.select)"
              wire:ignore>
            <label class="form-label" for="environment">{{ __('Api Environment') }}</label>
            <select x-ref="select" id="environment"
                    class="select2 form-select @error('environment') is-invalid @enderror">
              <option value="">{{ __('Seleccione...') }}</option>
              <option value="prueba">{{ __('Test') }}</option>
              <option value="produccion">{{ __('Production') }}</option>
            </select>
            @error('environment')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>
    </div>
    <br>
    <br>
    <h6>4. {{ __('Consecutivo de documentos') }}</h6>
    <div class="row">
      <!-- Sección de Secuencias -->
      @foreach($documentTypes as $typeCode => $typeLabel)
        <div class="col-md-3 fv-plugins-icon-container">
            <label class="form-label">{{ $typeLabel }}</label>
            <div class="input-group input-group-merge has-validation">
                <span class="input-group-text"><i class="bx bx-key"></i></span>
                <input
                    type="text"
                    wire:model="sequences.{{ $typeCode }}"
                    class="form-control @error("sequences.$typeCode") is-invalid @enderror"
                    placeholder="Consecutivo actual"
                >
            </div>
            @error("sequences.$typeCode")
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
      @endforeach
    </div>

    <br>
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
</form>
