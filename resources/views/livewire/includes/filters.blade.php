<th></th>
@foreach ($this->columns as $index => $column)
  @if ($column['visible'])
    <th wire:key="header-{{ $column['field'] }}-{{ $index }}" style="border-left: medium;">
        @if (isset($column['filter']) && !empty($column['filter']))
          @switch($column['filter_type'])
            @case('html')
               {!! $column['filter'] !!}
            @break

            @case('input')
              <input type="text" wire:model.live.debounce.300ms="filters.{{ $column['filter'] }}" class="form-control">
              @break

            @case('date')
              <input
                  type="text"
                  wire:model="filters.{{ $column['filter'] }}"
                  class="form-control range-picker"
                  id="{{ $column['filter'] }}"
                  x-data="rangePickerLivewire({ wireEventName: 'dateRangeSelected' })"
                  x-init="init($el)"
                  wire:ignore>
            @break

            @case('select')
              @php
              // Obtiene la variable pública de Livewire según el nombre en 'filter_sources'
              $options = $this->{$column['filter_sources']} ?? [];
              $fieldname = $column['filter_source_field'] ?? '';
              @endphp
              <div class="select2-primary fv-plugins-icon-container"
                  x-data="select2Livewire({
                    wireModelName: 'filters.{{ $column['filter'] }}',                    
                    postUpdate: true
                  })"
                  x-init="init($refs.select)"
                  wire:ignore>
                <select x-ref="select" id="{{ $column['filter'] }}"
                        class="select2 form-select">
                  <option value="">{{ __('Seleccione...') }}</option>
                  @foreach ($options as $option)
                    <option value="{{ $option['id'] }}">{{ $option[$fieldname] }}</option>
                  @endforeach
                </select>
              </div>
            @break
          @endswitch
        @endif
    </th>
  @endif
@endforeach
