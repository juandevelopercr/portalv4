<div class="table-wrapper" style="display: flex; justify-content: flex-end; align-items: flex-start;">
@php
//if (!is_null($this->transaction_id))
  //dd($this);
@endphp
    <table class="table" style="width: auto; border-collapse: collapse; text-align: left;">
        <thead>
            <tr>
                <th style="padding: 4px 8px; font-weight: bold;">{{ __('TOTAL VENTA') }}</th>
                <th style="padding: 4px 8px; font-weight: bold;">{{ $this->currencyCode }} {{
                    Helper::formatDecimal($this->totalVenta) }}</th>
            </tr>
            <tr>
                <th style="padding: 4px 8px; font-weight: bold;">{{ __('TOTAL DESCUENTOS') }}</th>
                <th style="padding: 4px 8px; font-weight: bold;">{{ $this->currencyCode }} {{
                    Helper::formatDecimal($this->totalDiscount) }}</th>
            </tr>
            <tr>
                <th style="padding: 4px 8px; font-weight: bold;">{{ __('TOTAL VENTA NETA') }}</th>
                <th style="padding: 4px 8px; font-weight: bold;">{{ $this->currencyCode }} {{
                    Helper::formatDecimal($this->totalVentaNeta) }}</th>
            </tr>
            <tr>
                <th style="padding: 4px 8px; font-weight: bold;">{{ __('TOTAL IVA') }}</th>
                <th style="padding: 4px 8px; font-weight: bold;">{{ $this->currencyCode }} {{
                    Helper::formatDecimal($this->totalImpuesto) }}</th>
            </tr>
            <tr>
                <th style="padding: 4px 8px; font-weight: bold;">{{ __('TOTAL IVA ASUMIDO EMISOR') }}</th>
                <th style="padding: 4px 8px; font-weight: bold;">{{ $this->currencyCode }} {{
                    Helper::formatDecimal($this->totalImpAsumEmisorFabrica) }}</th>
            </tr>
            <tr>
                <th style="padding: 4px 8px; font-weight: bold;">{{ __('TOTAL OTROS CARGOS') }}</th>
                <th style="padding: 4px 8px; font-weight: bold;">{{ $this->currencyCode }} {{
                    Helper::formatDecimal($this->totalOtrosCargos) }}
                </th>
            </tr>
            <tr>
                <th style="padding: 4px 8px; font-weight: bold;">{{ __('TOTAL COMPROBANTE') }}</th>
                <th style="padding: 4px 8px; font-weight: bold;">{{ $this->currencyCode }} {{
                    Helper::formatDecimal($this->totalComprobante) }}
                </th>
            </tr>
        </thead>
    </table>
</div>
