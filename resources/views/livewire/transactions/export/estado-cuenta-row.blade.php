@php
    use App\Models\Currency;
@endphp

<tr>
    <td align="center" colspan="2">
        <span style="font-size: 10px; font-weight: normal;">
            {{ $transaction->consecutivo }}
        </span>
    </td>
    <td align="left">
        <span style="font-size: 10px; font-weight: normal;">
            {{ $transaction->location->name }}
        </span>
    </td>
    <td align="center">
        <span style="font-size: 10px; font-weight: normal;">
            {{ \Carbon\Carbon::parse($transaction->transaction_date)->addDays($transaction->pay_term_number)->format('d-m-Y') }}
        </span>
    </td>
    <td align="center">
        <span style="font-size: 10px; font-weight: normal;">
            {{ $transaction->currency->code }}
        </span>
    </td>
    <td align="center">
        <span style="font-size: 10px; font-weight: normal;">
            {{ \Helper::formatDecimal($transaction->proforma_change_type) }}
        </span>
    </td>
    <td align="center">
        <span style="font-size: 10px; font-weight: normal;">
            CRC&nbsp;
            @if ($transaction->currency_id == Currency::COLONES)
                {{ \Helper::formatDecimal($transaction->totalComprobante) }}
            @else
                {{ \Helper::formatDecimal($transaction->totalComprobante * $transaction->proforma_change_type) }}
            @endif
        </span>
    </td>
    <td align="center">
        <span style="font-size: 10px; font-weight: normal;">
            USD&nbsp;
            @if ($transaction->currency_id == Currency::DOLARES)
                {{ \Helper::formatDecimal($transaction->totalComprobante) }}
            @else
                {{ \Helper::formatDecimal($transaction->totalComprobante / $transaction->proforma_change_type) }}
            @endif
        </span>
    </td>
</tr>
