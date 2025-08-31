<form wire:submit.prevent="update" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>
    <div class="row">
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

      <div class="col-md-12">
        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">

            {{-- Mostrar la imagen temporal si se ha subido una nueva --}}

            @if ($logo && method_exists($logo, 'temporaryUrl'))
                <img
                    class="d-block rounded"
                    src="{{ $logo->temporaryUrl() }}"
                    alt="{{ __('Photo') }}"
                    id="uploadedAvatar"
                    style="height: 100px; width: auto;"
                >
            @elseif ($oldlogo)
                <img
                    class="d-block rounded"
                    src="{{ asset('storage/assets/img/logos/' . $oldlogo) }}"
                    alt="{{ __('Photo') }}"
                    id="uploadedAvatar"
                    style="height: 100px; width: auto;"
                >
            @else
                <img
                    class="d-block rounded"
                    src="{{ asset('storage/assets/default-image.png') }}"
                    alt="{{ __('Photo') }}"
                    id="uploadedAvatar"
                    style="height: 100px; width: auto;"
                >
            @endif


            <div class="button-wrapper">
              <label for="logo" class="btn btn-primary me-3 mb-4" tabindex="0">
                <span class="d-none d-sm-block">{{ __('Upload Logo') }}</span>
                <i class="bx bx-upload d-block d-sm-none"></i>
                <input wire:model.live='logo' id="logo" hidden class="account-file-input"
                  accept="image/png, image/jpeg" type="file" />
                @error('logo')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
              </label>

              <button type="button" class="btn btn-label-secondary account-image-reset mb-4" wire:click="resetPhoto">
                <i class="bx bx-reset d-block d-sm-none"></i>
                <span class="d-none d-sm-block">{{ __('Reset') }}</span>
              </button>
            </div>

            <div class="col" wire:loading.delay wire:target="logo">
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
    </div>

    <div class="row g-6">
        <div class="col-md-4 fv-plugins-icon-container">
            <label class="form-label" for="name">{{ __('Business Name') }}</label>
            <div class="input-group input-group-merge has-validation">
                <span class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" wire:model="name" id="name"
                    class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}">
            </div>
            @error('name')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 select2-primary fv-plugins-icon-container"
            x-data="select2Livewire({
              wireModelName: 'currency_id',
              postUpdate: true
            })"
            x-init="init($refs.select)"
            wire:ignore>
          <label class="form-label" for="currency_id">{{ __('Currency') }}</label>
          <select x-ref="select" id="currency_id"
                  class="select2 form-select @error('currency_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->currencies as $currency)
              <option value="{{ $currency->id }}">{{ $currency->code }}</option>
            @endforeach
          </select>
          @error('currency_id')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4 fv-plugins-icon-container">
            <label class="form-label" for="proveedorSistemas">{{ __('Systems Provider') }}</label>
            <div class="input-group input-group-merge has-validation">
                <span class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" wire:model="proveedorSistemas" id="proveedorSistemas"
                    class="form-control @error('proveedorSistemas') is-invalid @enderror" placeholder="{{ __('Systems Provider') }}">
            </div>
            @error('proveedorSistemas')
            <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
    @php
    /*
    <br>
    <h6>2. {{ __('Emails Setting') }}</h6>
    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
          <label class="form-label" for="host_smpt">{{ __('Host smtp') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-server"></i></span>
              <input type="text" wire:model="host_smpt"
                  class="form-control @error('host_smpt') is-invalid @enderror" placeholder="{{ __('Host smtp') }}">
          </div>
          @error('host_smpt')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="user_smtp">{{ __('User smtp') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-user"></i></span>
            <input type="text" wire:model="user_smtp"
                class="form-control @error('user_smtp') is-invalid @enderror" placeholder="{{ __('User smtp') }}">
        </div>
        @error('user_smtp')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="pass_smtp">{{ __('Password smtp') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
            <input type="text" wire:model="pass_smtp"
                class="form-control @error('pass_smtp') is-invalid @enderror" placeholder="{{ __('Password smtp') }}">
        </div>
        @error('pass_smtp')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="puerto_smpt">{{ __('Port smtp') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
            <input type="text" wire:model="puerto_smpt"
                class="form-control @error('puerto_smpt') is-invalid @enderror" placeholder="{{ __('Port smtp') }}">
        </div>
        @error('puerto_smpt')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="smtp_encryptation">{{ __('Encryptation smtp') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-shield-alt-2"></i></span>
            <input type="text" wire:model="smtp_encryptation"
                class="form-control @error('smtp_encryptation') is-invalid @enderror" placeholder="{{ __('Encryptation smtp') }}">
        </div>
        @error('smtp_encryptation')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-12 fv-plugins-icon-container">
        <label class="form-label" for="notification_email">{{ __('Email notification CC') }}</label>
        <textarea class="form-control @if(count($invalidEmails)) is-invalid @endif"
          wire:model.live.debounce.600ms="notification_email" name="notification_email" id="notification_email" rows="1"
          placeholder="{{ __('Email notification CC') }}">
        </textarea>
        @error('notification_email')
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
    <h6>4. {{ __('Emails Setting for Imap') }}</h6>
    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
          <label class="form-label" for="host_imap">{{ __('Host imap') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-server"></i></span>
              <input type="text" wire:model="host_imap"
                  class="form-control @error('host_imap') is-invalid @enderror" placeholder="{{ __('Host imap') }}">
          </div>
          @error('host_imap')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="user_imap">{{ __('User imap') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-user"></i></span>
            <input type="text" wire:model="user_imap"
                class="form-control @error('user_imap') is-invalid @enderror" placeholder="{{ __('User imap') }}">
        </div>
        @error('user_imap')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="pass_imap">{{ __('Password imap') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
            <input type="text" wire:model="pass_imap"
                class="form-control @error('pass_imap') is-invalid @enderror" placeholder="{{ __('Password imap') }}">
        </div>
        @error('pass_imap')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="puerto_imap">{{ __('Port imap') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
            <input type="text" wire:model="puerto_imap"
                class="form-control @error('puerto_imap') is-invalid @enderror" placeholder="{{ __('Port imap') }}">
        </div>
        @error('puerto_imap')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="imap_encryptation">{{ __('Encryptation imap') }}</label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-shield-alt-2"></i></span>
            <input type="text" wire:model="imap_encryptation"
                class="form-control @error('imap_encryptation') is-invalid @enderror" placeholder="{{ __('Encryptation imap') }}">
        </div>
        @error('imap_encryptation')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>
    */
    @endphp

    <br>
    <h6>2. {{ __('Medication Registry') }}</h6>
    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
          <label class="form-label" for="registro_medicamento">{{ __('Medication Registry') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-file"></i></span>
              <input type="text" wire:model="registro_medicamento"
                  class="form-control @error('registro_medicamento') is-invalid @enderror" placeholder="{{ __('Medication Registry') }}">
          </div>
          @error('registro_medicamento')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
      <div class="col-md-4 fv-plugins-icon-container">
          <label class="form-label" for="forma_farmaceutica">{{ __('Pharmaceutical Form') }}</label>
          <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-capsule"></i></span>
              <input type="text" wire:model="forma_farmaceutica"
                  class="form-control @error('forma_farmaceutica') is-invalid @enderror" placeholder="{{ __('Medication Registry') }}">
          </div>
          @error('forma_farmaceutica')
          <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
    </div>
    <br>
    <div class="row g-6">
        <div class="pt-6">
            <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1 mt-5" wire:loading.attr="disabled"
                wire:target="update">
                <span wire:loading.remove wire:target="update">
                    <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Save') }}
                </span>
                <span wire:loading wire:target="update">
                    <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Saving...') }}
                </span>
            </button>
        </div>
    </div>
</form>

@script()
<script>

  window.select2Config = {
    centro_costo_calculo_registro_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    emisor_gasto_id: {
      fireEvent: false,
      wireIgnore: false,
    }
  };


  $(document).ready(function() {
    Object.entries(select2Config).forEach(([id, config]) => {
      const $select = $('#' + id);
      if (!$select.length) return;

      $select.select2();

      // Default values
      const fireEvent = config.fireEvent ?? false;
      const wireIgnore = config.wireIgnore ?? false;
      //const allowClear = config.allowClear ?? false;
      //const placeholder = config.placeholder ?? 'Seleccione una opción';

      $select.on('change', function() {
        let data = $(this).val();
        $wire.set(id, data, fireEvent);
        $wire.id = data;
        //@this.department_id = data;
        console.log(data);
      });
    });

    window.initSelect2 = () => {
      Object.entries(select2Config).forEach(([id, config]) => {
        const $select = $('#' + id);
        if (!$select.length) return;

        const wireIgnore = config.wireIgnore ?? false;

        if (!wireIgnore) {
          $select.select2();
          console.log("Se reinició el select2 " + id);
        }
      });
    }

    initSelect2();

    Livewire.on('select2', () => {
      setTimeout(() => {
        initSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });
  })
</script>
@endscript


@script()
<script>
  Livewire.on('setSelect2Values', ({ id, options = [] }) => {
    console.log("El id es: " + id);
    console.log("El options es: " + options);
    if (!Array.isArray(options)) return;

    console.log("Si llega aqui es bueno");

    const $select = $('#' + id);
    options.forEach(({ id, text }) => {
      if ($select.find(`option[value="${id}"]`).length === 0) {
        const newOption = new Option(text, id, true, true);
        $select.append(newOption);
      }
    });

    const values = options.map(opt => opt.id);
    $select.val(values).trigger('change');
  });

</script>
@endscript
