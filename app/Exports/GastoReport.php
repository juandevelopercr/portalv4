<?php

namespace App\Exports;

use App\Models\Comprobante;
use Carbon\Carbon;

class GastoReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Fecha emisión comprobante', 'field' => 'fecha_emision_formateada', 'type' => 'string', 'align' => 'center', 'width' => 30],
      ['label' => 'Fecha de recepción', 'field' => 'fecha_recepcion', 'type' => 'string', 'align' => 'center', 'width' => 30],
      ['label' => 'Clave', 'field' => 'key', 'type' => 'string', 'align' => 'center', 'width' => 60],
      ['label' => 'Consecutivo de factura', 'field' => 'consecutivo_factura', 'type' => 'string', 'align' => 'center', 'width' => 30],
      ['label' => 'Consecutivo de gasto', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 30],

      ['label' => 'Receptor', 'field' => 'receptor_nombre', 'type' => 'string', 'align' => 'left', 'width' => 50],
      ['label' => 'Emisor', 'field' => 'emisor_nombre', 'type' => 'string', 'align' => 'left', 'width' => 50],
      ['label' => 'Tipo identificación emisor', 'field' => 'tipo_identificacion', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Identificación del emisor', 'field' => 'emisor_numero_identificacion', 'type' => 'string', 'align' => 'left', 'width' => 25],


      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'T.C', 'field' => 'tipo_cambio', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
      ['label' => 'Tipo de comprobante', 'field' => 'tipo_comprobante', 'type' => 'string', 'align' => 'right', 'width' => 15],
      ['label' => 'Descuento', 'field' => 'total_descuentos', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Subtotal', 'field' => 'subtotal', 'type' => 'decimal', 'align' => 'right', 'width' => 25],
      ['label' => 'Tipo de impuesto', 'field' => 'tipo_impuesto', 'type' => 'string', 'align' => 'right', 'width' => 25],
      ['label' => 'Monto I.V.A', 'field' => 'total_impuestos', 'type' => 'decimal', 'align' => 'right', 'width' => 25],
      ['label' => 'Total', 'field' => 'total_comprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 25],
      ['label' => 'Estado', 'field' => 'status', 'type' => 'string', 'align' => 'right', 'width' => 15],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Comprobante::query()
        ->selectRaw("
            comprobantes.id,
            comprobantes.key,
            SUBSTRING(comprobantes.key, 22, 20) as consecutivo_factura,
            COALESCE(comprobantes.consecutivo, '') as consecutivo,

            CASE
                WHEN comprobantes.fecha_emision IS NULL THEN ''
                ELSE DATE_FORMAT(comprobantes.fecha_emision, '%d-%m-%Y')
            END AS fecha_emision_formateada,

            CASE
                WHEN comprobantes.created_at IS NULL THEN ''
                ELSE DATE_FORMAT(comprobantes.created_at, '%d-%m-%Y')
            END AS fecha_recepcion,

            COALESCE(comprobantes.receptor_nombre, '') AS receptor_nombre,
            COALESCE(comprobantes.emisor_nombre, '') AS emisor_nombre,

            CASE
                WHEN COALESCE(comprobantes.emisor_tipo_identificacion, '') = '01' THEN 'Cédula Física'
                WHEN COALESCE(comprobantes.emisor_tipo_identificacion, '') = '02' THEN 'Cédula Jurídica'
                WHEN COALESCE(comprobantes.emisor_tipo_identificacion, '') = '03' THEN 'DIMEX'
                WHEN COALESCE(comprobantes.emisor_tipo_identificacion, '') = '04' THEN 'NITE'
                WHEN COALESCE(comprobantes.emisor_tipo_identificacion, '') = '05' THEN 'Extranjero No Domiciliado'
                WHEN COALESCE(comprobantes.emisor_tipo_identificacion, '') = '06' THEN 'No Contribuyente'
                ELSE '-'
            END AS tipo_identificacion,

            CASE
                WHEN COALESCE(comprobantes.tipo_documento, '') = '01' THEN 'FACTURA'
                WHEN COALESCE(comprobantes.tipo_documento, '') = '02' THEN 'NOTA DE DEBITO'
                WHEN COALESCE(comprobantes.tipo_documento, '') = '03' THEN 'NOTA DE CREDITO'
                WHEN COALESCE(comprobantes.tipo_documento, '') = '04' THEN 'TIQUETE'
                WHEN COALESCE(comprobantes.tipo_documento, '') = '08' THEN 'FACTURA DE COMPRA'
                WHEN COALESCE(comprobantes.tipo_documento, '') = '09' THEN 'FACTURA DE EXPORTACION'
                WHEN COALESCE(comprobantes.tipo_documento, '') = '10' THEN 'RECIBO DE PAGO'
                ELSE '-'
            END AS tipo_comprobante,

            COALESCE(comprobantes.emisor_numero_identificacion, '') AS emisor_numero_identificacion,
            COALESCE(comprobantes.moneda, '') AS moneda,
            COALESCE(comprobantes.tipo_cambio, 0) AS tipo_cambio,
            COALESCE(comprobantes.total_descuentos, 0) AS total_descuentos,
            COALESCE(comprobantes.total_impuestos, 0) AS total_impuestos,

            (COALESCE(comprobantes.total_gravado, 0) + COALESCE(comprobantes.total_exento, 0) - COALESCE(comprobantes.total_descuentos, 0)) AS subtotal,

            CASE
                WHEN COALESCE(comprobantes.total_gravado, 0) > 0 THEN 'GRAVADO'
                ELSE 'EXENTO'
            END AS tipo_impuesto,

            COALESCE(comprobantes.total_comprobante, 0) AS total_comprobante,
            COALESCE(comprobantes.status, '') AS status
        ")
        ->orderBy('comprobantes.created_at', 'DESC')
        ->orderBy('comprobantes.emisor_nombre', 'ASC');

    // Filtro por rango de fechas o fecha única
    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);

        try {
            if (count($range) === 2) {
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();
                $query->whereBetween('comprobantes.fecha_emision', [$start, $end]);
            } else {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));
                $query->whereDate('comprobantes.fecha_emision', $singleDate->format('Y-m-d'));
            }
        } catch (\Exception $e) {
            // manejar error si la fecha no tiene el formato esperado
        }
    }

    // Filtro por emisor
    if (!empty($this->filters['filter_emisor'])) {
        $query->where('comprobantes.emisor_nombre', '=', $this->filters['filter_emisor']);
    }

    // Filtro por tipo de documento
    if (!empty($this->filters['filter_type'])) {
        $query->where('comprobantes.tipo_documento', '=', $this->filters['filter_type']);
    }

    // Filtro por moneda
    if (!empty($this->filters['filter_currency'])) {
        $query->where('comprobantes.moneda', '=', $this->filters['filter_currency']);
    }

    // Filtro por tipo de impuesto
    if (!empty($this->filters['filter_tax_type'])) {
        if ($this->filters['filter_tax_type'] === 'GRAVADO') {
            $query->where('comprobantes.total_gravado', '>', 0);
        } else {
            $query->where('comprobantes.total_exento', '>', 0);
        }
    }

    // Filtro por estado
    if (!empty($this->filters['filter_status'])) {
        $query->where('comprobantes.status', '=', $this->filters['filter_status']);
    }

    return $query;
  }


}
