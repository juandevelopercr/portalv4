<?php
use App\Models\Transaction;
?>
<div class="row">
    <div class="col-md-2">
        <label class="form-label" for="facturas-consecutivo">{{ __('Consecutivo') }}</label>
        <div id="facturas-consecutivo">{{ $this->consecutivo }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-cliente_id">{{ __('Cliente') }}</label>
        <div id="facturas-cliente_id">{{ $this->customer_name }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-banco_id">{{ __('Banco') }}</label>
        <div id="facturas-banco_id">{{ $this->bank_name }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-moneda_id">{{ __('Moneda') }}</label>
        <div id="facturas-moneda_id">{{ $this->currency_code }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-tipo_cambio">{{ __('Tipo de Cambio') }}</label>
        <div id="facturas-tipo_cambio">{{ number_format($this->proforma_change_type, 2, '.', ',') }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-emisor_id">{{ __('Emisor') }}</label>
        <div id="facturas-emisor_id">{{ $this->issuer_name }}</div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label class="form-label" for="facturas-codigo_contable_id">{{ __('Código Contable') }}</label>
        <div id="facturas-codigo_contable_id">{{ $this->codigo_contable_descrip }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-usuario_id">{{ __('Usuario') }}</label>
        <div id="facturas-usuario_id">{{ $this->user_name }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-fecha_emision">{{ __('Fecha Emisión') }}</label>
        <div id="facturas-fecha_emision">{{ \Carbon\Carbon::parse($this->transaction_date)->format('d-m-Y') }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-estado_id">{{ __('Estado') }}</label>
        <div id="facturas-estado_id">{{ $this->proforma_status }}</div>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="facturas-reporte">Reportes</label>
        <div id="facturas-reporte">
            @php
                $esFacturada = $this->proforma_status === 'FACTURADA';
                $iconSize = 'fs-4'; // Ajusta este valor si tienes un tamaño de ícono definido
            @endphp

            @can('download-pdf-proformas')
              <div class="d-flex align-items-center flex-nowrap">

              @if ($this->proforma_status == Transaction::FACTURADA)
                {{-- RECIBO SENCILLO --}}
                <button type="button"
                    class="btn btn-link text-secondary p-0 me-2"
                    title="Recibo Sencillo"
                    wire:click="downloadReciboSencillo({{ $this->recordId }})"
                    wire:loading.attr="disabled"
                    wire:target="downloadReciboSencillo">
                    <i class="bx bx-loader bx-spin {{ $iconSize }}"
                      wire:loading
                      wire:target="downloadReciboSencillo"></i>
                    <i class="bx bxs-receipt {{ $iconSize }}"
                      wire:loading.remove
                      wire:target="downloadReciboSencillo"></i>
                </button>

                {{-- RECIBO DETALLADO --}}
                <button type="button"
                    class="btn btn-link text-secondary p-0 me-2"
                    title="Recibo Detallado"
                    wire:click="downloadReciboDetallado({{ $this->recordId }})"
                    wire:loading.attr="disabled"
                    wire:target="downloadReciboDetallado">
                    <i class="bx bx-loader bx-spin {{ $iconSize }}"
                      wire:loading
                      wire:target="downloadReciboDetallado"></i>
                    <i class="bx bxs-receipt {{ $iconSize }}"
                      wire:loading.remove
                      wire:target="downloadReciboDetallado"></i>
                </button>
              @else
                {{-- PROFORMA SENCILLA --}}
                <button type="button"
                    class="btn btn-link text-danger p-0 me-2"
                    title="PROFORMA SENCILLA"
                    wire:click="downloadProformaSencilla({{ $this->recordId }})"
                    wire:loading.attr="disabled"
                    wire:target="downloadProformaSencilla">
                    <i class="bx bx-loader bx-spin {{ $iconSize }}"
                      wire:loading
                      wire:target="downloadProformaSencilla"></i>
                    <i class="bx bxs-file-pdf {{ $iconSize }}"
                      wire:loading.remove
                      wire:target="downloadProformaSencilla"></i>
                </button>

                {{-- PROFORMA DETALLADO --}}
                <button type="button"
                    class="btn btn-link text-danger p-0 me-2"
                    title="PROFORMA DETALLADO"
                    wire:click="downloadProformaDetallada({{ $this->recordId }})"
                    wire:loading.attr="disabled"
                    wire:target="downloadProformaDetallada">
                    <i class="bx bx-loader bx-spin {{ $iconSize }}"
                      wire:loading
                      wire:target="downloadProformaDetallada"></i>
                    <i class="bx bxs-file-pdf {{ $iconSize }}"
                      wire:loading.remove
                      wire:target="downloadProformaDetallada"></i>
                </button>
              @endif

              <button type="button"
                  class="btn btn-link text-secondary p-0 me-2"
                  title="Reporte Recibo de Gasto"
                  wire:click="downloadCalculoReciboDeGastos({{ $this->recordId }})"
                  wire:loading.attr="disabled"
                  wire:target="downloadCalculoReciboDeGastos({{ $this->recordId }})">
                  <i class="bx bx-loader bx-spin {{ $iconSize }}"
                    wire:loading
                    wire:target="downloadCalculoReciboDeGastos({{ $this->recordId }})"></i>
                  <i class="bx bx-printer {{ $iconSize }}"
                    wire:loading.remove
                    wire:target="downloadCalculoReciboDeGastos({{ $this->recordId }})"></i>
              </button>
            </div>
            @endcan
        </div>
    </div>
</div>

<div class="row mt-3">
    @if($this->infoCaso)
        <div class="col-md-4">
            <label class="form-label" for="caso">{{ __('Número de caso') }}</label>
            <div id="facturas-caso"><strong>{{ $this->infoCaso }}</strong></div>
        </div>
    @endif

    <div class="col-md-4">
        <label class="form-label" for="total_comprobante">{{ __('Total Comprobante') }}</label>
        <div id="facturas-total_comprobante"><strong>{{ $this->currency_code }} {{ number_format($this->totalComprobante, 2, '.', ',') }}</strong></div>
    </div>
</div>

<div class="row mt-3 p-3">
    <div class="col-md-12">
        <div id="facturas-detalle">
            <textarea wire:model="message" rows="5" class="form-control">{{ $this->message }}</textarea>
        </div>
    </div>
</div>
