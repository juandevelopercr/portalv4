<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionCommission;

class FacturacionReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Cédula del cliente', 'field' => 'identification', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],

      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Descuento', 'field' => 'descuento', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
      ['label' => 'Venta Neta', 'field' => 'ventaNeta', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto I.V.A', 'field' => 'totalTax', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Exoneración', 'field' => 'exoneracion', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Otro Cargos', 'field' => 'otrosCargos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Número de Nota', 'field' => 'numeroNotaCredito', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Total', 'field' => 'totalComprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Transaction::query()
    ->selectRaw("
        transactions.id,
        transactions.consecutivo,
        CASE
            WHEN transactions.transaction_date IS NULL THEN ''
            ELSE DATE_FORMAT(transactions.transaction_date, '%d-%m-%Y')
        END AS transaction_date,
        CASE
            WHEN transactions.contact_id = 16203 THEN transactions.customer_comercial_name
            ELSE transactions.customer_name
        END AS customer_name,
        CAST(c.identification AS CHAR) AS identification,
        emisor.name as nombreEmisor,
        cu.code as moneda,
        cu.symbol as monedasymbolo,
        transactions.proforma_change_type,

        CASE
            WHEN transactions.status = 'ANULADA' OR transactions.status = 'RECHAZADA' THEN 0
            ELSE COALESCE(transactions.totalVentaNeta, 0)
        END AS ventaNeta,

        CASE
            WHEN transactions.status = 'ANULADA' OR transactions.status = 'RECHAZADA' THEN 0
            ELSE COALESCE(transactions.totalDiscount, 0)
        END AS descuento,

        CASE
            WHEN transactions.status = 'ANULADA' OR transactions.status = 'RECHAZADA' THEN 0
            ELSE COALESCE(transactions.totalTax,0)
        END AS totalTax,

        CASE
            WHEN transactions.status = 'ANULADA' OR transactions.status = 'RECHAZADA' THEN 0
            ELSE COALESCE(transactions.totalExonerado,0)
        END AS exoneracion,

        CASE
            WHEN transactions.proforma_status = 'ANULADA' OR transactions.status = 'RECHAZADA' THEN 0
            ELSE COALESCE(transactions.totalOtrosCargos,0)
        END AS otrosCargos,

        CASE
            WHEN transactions.proforma_status = 'ANULADA' OR transactions.status = 'RECHAZADA' THEN 0
            ELSE COALESCE(transactions.totalComprobante,0)
        END AS totalComprobante,

        transactions.status,

        CASE
            WHEN transactions.status = 'ANULADA'
            THEN COALESCE((SELECT t1.consecutivo FROM transactions t1 WHERE t1.RefCodigo = transactions.key LIMIT 1), '')
            ELSE ''
        END AS numeroNotaCredito

    ")
    ->leftJoin('business_locations as emisor', 'transactions.location_id', '=', 'emisor.id')
    ->join('contacts as c', 'transactions.contact_id', '=', 'c.id')
    ->join('currencies as cu', 'transactions.currency_id', '=', 'cu.id')
    ->whereIn('transactions.document_type', ['FE','TE'])
    ->whereNull('transactions.deleted_at')
    ->whereIn('transactions.status', ['ACEPTADA','ANULADA'])
    ->orderBy('transactions.transaction_date', 'DESC');


    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);

        if (count($range) === 2) {
            try {
                // Convertir fechas a Carbon
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();

                // Filtro de rango (incluye todas las horas del día)
                $query->whereBetween('transactions.transaction_date', [$start, $end]);
            } catch (\Exception $e) {
                // manejar error
            }
        } else {
            try {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));

                // Comparar solo por la fecha, ignorando horas
                $query->whereDate('transactions.transaction_date', $singleDate->format('Y-m-d'));
            } catch (\Exception $e) {
                // manejar error
            }
        }
    }

    if (!empty($this->filters['filter_contact'])) {
      $query->where('transactions.contact_id', '=', $this->filters['filter_contact']);
    }

    if (!empty($this->filters['filter_type'])) {
      $query->where('transactions.document_type', '=', $this->filters['filter_type']);
    }

    if (!empty($this->filters['filter_currency'])) {
      $query->where('transactions.currency_id', '=', $this->filters['filter_currency']);
    }

    if (!empty($this->filters['filter_tax_type'])) {
      if ($this->filters['filter_tax_type'] == 'GRAVADO')
        $query->where('transactions.totalTax', '>', 0);
      else
        $query->where('transactions.totalExonerado', '>', 0);
    }

    if (!empty($this->filters['filter_status'])) {
      $query->where('transactions.status', '=', $this->filters['filter_status']);
    }

    return $query;
  }
}
