<div id="content-centro-costo" data-rows-count="{{ count($rows) }}">
    @foreach($rows as $index => $row)
        <div class="row mb-2">
            <input type="hidden" wire:model="rows.{{ $index }}.id">
            <div class="col-md-3 fv-plugins-icon-container">
              <label class="form-label" for="amount_{{ $index }}">{{ __('Amount') }}</label>
              <div
                x-data="cleaveLivewire({
                  initialValue: '{{ data_get($this->rows, $index . '.amount', '') }}',
                  wireModelName: 'rows.{{ $index }}.amount',
                  postUpdate: false,
                  decimalScale: 2,
                  allowNegative: true,
                  maxLength: 15,
                  rawValueCallback: (val) => {
                    const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
                    if (component && val.length <= 15) {
                      component.set('rows.{{ $index }}.amount', val);
                    }
                  }
                })"
                x-init="init($refs.cleaveInput)"
              >
                <div class="input-group input-group-merge has-validation">
                  <span class="input-group-text">
                    <i class="bx bx-dollar-circle"></i>
                  </span>
                  <input type="text"
                        id="amount_{{ $index }}"
                        x-ref="cleaveInput"
                        class="form-control numeral-mask inputMontoCentroCosto"
                        placeholder="{{ __('Amount') }}"
                        wire:ignore />
                </div>
              </div>
              @error('rows.'.$index.'.amount')
                <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3 select2-primary fv-plugins-icon-container"
                x-data="select2Livewire({
                    wireModelName: 'rows.{{ $index }}.centro_costo_id',
                    postUpdate: true
                })"
                x-init="init($refs.select)"
                wire:ignore>
                <label class="form-label" for="centro_costo_{{ $index }}">Centro de Costo</label>

                <select x-ref="select"
                        id="centro_costo_{{ $index }}"
                        class="select2 form-select @error('rows.' . $index . '.centro_costo_id') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->listcentrosCosto as $cc)
                        <option value="{{ $cc->id }}">{{ $cc->codigo.'-'.$cc->descrip }}</option>
                    @endforeach
                </select>

                @error('rows.' . $index . '.centro_costo_id')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 select2-primary fv-plugins-icon-container"
                x-data="select2Livewire({
                    wireModelName: 'rows.{{ $index }}.codigo_contable_id',
                    postUpdate: true
                })"
                x-init="init($refs.select)"
                wire:ignore>
                <label class="form-label" for="codigo_contable_id_{{ $index }}">Centro de Costo</label>

                <select x-ref="select"
                        id="codigo_contable_id_{{ $index }}"
                        class="select2 form-select @error('rows.' . $index . '.codigo_contable_id') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->listcatalogoCuentas as $cc)
                        <option value="{{ $cc->id }}">{{ $cc->codigo.'-'.$cc->descrip }}</option>
                    @endforeach
                </select>

                @error('rows.' . $index . '.codigo_contable_id')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-auto pt-7">
                <button type="button" wire:click="removeRow({{ $index }})" class="btn btn-danger btn-sm" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    @endforeach

    <button type="button" wire:click="addRow" class="btn btn-primary btn-sm">Agregar Centro de Costo</button>
</div>
