@foreach ($columns as $index => $column)
  @if ($column['visible'])
  <td wire:key="col-{{ $column['field'] }}-{{ $index }}"
    @if (isset($column['columnAlign'])) align="{{ $column['columnAlign'] }}" @endif
    @if (isset($column['columnClass'])) class="{{ $column['columnClass'] }}" @endif>


    @if ($column['field'] === '__expand')
      @php
          $relation = $column['expand_condition'] ?? null;
          $hasData = $relation && method_exists($record, $relation) && $record->{$relation}->isNotEmpty();
          $cantidad = $record->{$relation}->count();
      @endphp

      @if ($hasData && $cantidad >= 2)
          <button type="button"
                  class="btn btn-sm btn-link px-1"
                  wire:click="toggleExpand({{ $record->id }})"
                  aria-expanded="{{ in_array($record->id, $expandedRows ?? []) ? 'true' : 'false' }}">
              <i class="bx {{ in_array($record->id, $expandedRows ?? []) ? 'bx-chevron-down' : 'bx-chevron-right' }}"></i>
          </button>
      @endif
    @else
    {!! $column['openHtmlTab'] !!}
      @php
        /*
      @if ($index == 0 && isset($canedit) && $canedit)
      <div class="d-flex align-items-center">
        <a href="javascript:void(0)" wire:click="edit({{ $record->id }})" class="text-primary"
            wire:loading.attr="disabled">

        <!-- Ícono normal (visible cuando no está en loading) -->
        <span wire:loading.remove wire:target="edit({{ $record->id }})" class="d-flex align-items-center">
          <i class="bx bx-edit-alt"></i>
      @endif
      */
      @endphp
      @if(!empty($column['function']) && $column['columnType'] != 'action')
        {!! call_user_func_array([$record, trim($column['function'])], $column['parameters'] ?? []) !!}
      @elseif ($column['columnType'] == 'date')
        {{ isset($record->{$column['field']}) ? \Carbon\Carbon::parse($record->{$column['field']})->format('d/m/Y') : '' }}
      @elseif ($column['columnType'] == 'decimal')
        {{ Helper::formatDecimal($record->{$column['field']}) }}
      @elseif ($column['columnType'] == 'integer')
        {{ (int)$record->{$column['field']} ?? '' }}
      @elseif ($column['columnType'] == 'action' && !empty($column['function']))
        {!! call_user_func_array([$record, trim($column['function'])], $column['parameters'] ?? []) !!}
      @else
        {{ $record->{$column['field']} ?? '' }}
      @endif
      @php
        /*
      @if ($index == 0)
            </span>
            <!-- Ícono de carga (visible cuando está en loading) -->
            <span wire:loading wire:target="edit({{ $record->id }})">
              {{ $record->{$column['field']} ?? '' }} <i class="spinner-border spinner-border-sm me-1"
                role="status"></i>
            </span>
          </a>
        </div>
      @endif
      */
      @endphp
      {!! $column['closeHtmlTab'] !!}
    @endif
  </td>
  @endif
@endforeach
