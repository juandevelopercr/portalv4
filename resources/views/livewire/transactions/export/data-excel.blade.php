@php
  use App\Models\Transaction;
@endphp
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Consecutive') }}</th>
            <th>{{ __('No. Proforma') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Department') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('Transaction Date') }}</th>
            <th>{{ __('Application Date') }}</th>
            <th>{{ __('Issuer') }}</th>
            <th>{{ __('Accounting code') }}</th>
            <th>{{ __('Bank') }}</th>
            <th>{{ __('Currency') }}</th>
            <th>{{ __('Proforma Type') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query->cursor() as $key => $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->consecutive }}</td>
            <td>{{ $record->customer_name }}</td>
            <td>{{ $record->department_name }}</td>
            <td>{{ $record->user_name }}</td>
            <td>
                {{ \Carbon\Carbon::parse($record->transaction_date)
                ->locale('es')
                ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </td>
            <td>
                @if (!is_null($record->fecha_solicitud_factura))
                {{ \Carbon\Carbon::parse($record->fecha_solicitud_factura)
                ->locale('es')
                ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                @endif
            </td>
            <td>{{ $record->issuer_name ?? '' }}</td>
            <td>{{ $record->codigo_contable_code ?? '' }}</td>
            <td>{{ $record->bank_name ?? '' }}</td>
            <td>{{ $record->currency_name ?? '' }}</td>
            <td>{{ $record->proforma_type }}</td>
            <td>
                @if ($record->type == Transaction::PROFORMA)
                {{ $record->proforma_status }}
                @else
                {{ $record->status }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
