<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Transaction;
use App\Models\TransactionCommission;

class GeneralReport extends BaseReport
{
  protected function columns(): array
  {
    $columns = $this->getColumns($this->filters['filter_type']);
    return $columns;
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = TransactionCommission::query()
        ->from('transactions_commissions as tc')
        ->selectRaw("
            t.id,
            CASE
                WHEN cc.codigo IS NULL OR codcontable.codigo IS NULL THEN '-'
                ELSE REPLACE(REPLACE(codcontable.codigo, 'XX', cc.codigo), 'YYY', emisor.code)
            END AS codcont,
            t.consecutivo,
            CASE
                WHEN t.transaction_date IS NULL THEN ''
                ELSE DATE_FORMAT(t.transaction_date, '%d-%m-%Y')
            END AS transaction_date,
            t.customer_name,
            CAST(c.identification AS CHAR) AS identification,
            emisor.name as nombreEmisor,
            d.name as departamento,
            b.name as banco,
            ca.numero,
            ca.deudor,
            cc.descrip as centroCosto,
            cu.code as moneda,
            cu.symbol as monedasymbolo,
            t.proforma_change_type,

            -- Base con percent
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE (COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0) / 100)
            END AS gastos,

            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
            END AS honorariosConDescuento,

            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100)
            END AS totalTax,

            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                      + (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100))
            END AS honorariosConIva,

            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE (COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100)
            END AS totalOtrosCargos,

            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE (COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100)
            END AS totalComprobante,

            -- USD
            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100
                        ELSE (COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100)
                            / NULLIF(COALESCE(t.proforma_change_type,0),0)
                    END
                END,
            0) AS gastosUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
                        ELSE ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                            / NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
                END,
            0) AS honorariosConDescuentoUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100
                        ELSE (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100) / NULLIF(COALESCE(t.proforma_change_type,0),0)
                    END
                END,
            0) AS totalTaxUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN ((COALESCE(t.totalHonorarios,0)*COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                                      + (COALESCE(t.totalTax,0)*COALESCE(tc.percent,0)/100))
                        ELSE (((COALESCE(t.totalHonorarios,0)*COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                              + (COALESCE(t.totalTax,0)*COALESCE(tc.percent,0)/100)) / NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
                END,
            0) AS honorariosConIvaUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100
                        ELSE (COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END,
            0) AS totalOtrosCargosUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100
                        ELSE (COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END,
            0) AS totalComprobanteUSD,

            -- CRC
            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100
                        ELSE (COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100) * COALESCE(t.proforma_change_type,0)
                    END
                END,
            0) AS gastosCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN ((COALESCE(t.totalHonorarios,0)*COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
                        ELSE (((COALESCE(t.totalHonorarios,0)*COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
                              * COALESCE(t.proforma_change_type,0))
                    END
                END,
            0) AS honorariosConDescuentoCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100
                        ELSE (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100) * COALESCE(t.proforma_change_type,0)
                    END
                END,
            0) AS totalTaxCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN ((COALESCE(t.totalHonorarios,0)*COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                                      + (COALESCE(t.totalTax,0)*COALESCE(tc.percent,0)/100))
                        ELSE (((COALESCE(t.totalHonorarios,0)*COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                              + (COALESCE(t.totalTax,0)*COALESCE(tc.percent,0)/100)) * COALESCE(t.proforma_change_type,0))
                    END
                END,
            0) AS honorariosConIvaCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100
                        ELSE COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100 * COALESCE(t.proforma_change_type,0)
                    END
                END,
            0) AS totalOtrosCargosCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100
                        ELSE COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100 * COALESCE(t.proforma_change_type,0)
                    END
                END,
            0) AS totalComprobanteCRC,

            t.proforma_status,

            CASE
                WHEN t.proforma_status = 'ANULADA'
                THEN COALESCE((SELECT t1.consecutivo FROM transactions t1 WHERE t1.RefCodigo = t.key LIMIT 1), '')
                ELSE ''
            END AS numeroNotaCredito,

            t.oc AS ordenCompra,
            t.migo AS migo,
            t.or AS ordenRequisicion,
            t.prebill AS prebill,
            CASE
                WHEN t.fecha_deposito_pago IS NULL THEN ''
                ELSE DATE_FORMAT(t.fecha_deposito_pago, '%d-%m-%Y')
            END AS fecha_deposito_pago,
            CASE
                WHEN t.fecha_traslado_gasto IS NULL THEN ''
                ELSE DATE_FORMAT(t.fecha_traslado_gasto, '%d-%m-%Y')
            END AS fecha_traslado_gasto,
            t.numero_deposito_pago,
            t.message AS message,
            t.notes AS notes,
            t.proforma_no,
            u.name as usuario,
            ca.deudor
        ")
        ->join('transactions as t', 'tc.transaction_id', '=', 't.id')
        ->leftJoin('centro_costos as cc', 'tc.centro_costo_id', '=', 'cc.id')
        ->leftJoin('codigo_contables as codcontable', 't.codigo_contable_id', '=', 'codcontable.id')
        ->leftJoin('business_locations as emisor', 't.location_id', '=', 'emisor.id')
        ->leftJoin('users as u', 't.created_by', '=', 'u.id')
        ->join('contacts as c', 't.contact_id', '=', 'c.id')
        ->join('departments as d', 't.department_id', '=', 'd.id')
        ->join('banks as b', 't.bank_id', '=', 'b.id')
        ->leftJoin('casos as ca', 't.caso_id', '=', 'ca.id')
        ->join('currencies as cu', 't.currency_id', '=', 'cu.id')
        ->whereIn('t.document_type', ['PR','FE','TE']);

    switch ($this->filters['filter_type']) {
      case 1:
        // Con deposito
        $query->whereNotNull('t.fecha_deposito_pago');
        break;
      case 2:
        // Sin deposito
        $query->whereNull('t.fecha_deposito_pago');
        break;
      case 3:
        // Honorarios
        $query->whereNotNull('t.fecha_deposito_pago')
              ->whereNull('t.fecha_traslado_honorario')
              ->whereNotNull('t.numero_deposito_pago')
              ->where('t.proforma_type', 'HONORARIO')
              ->whereNotIn('t.bank_id', [Bank::TERCEROS]);
        break;
      case 4:
        // Gastos
        $query->whereNotNull('t.fecha_deposito_pago')
              ->whereNotNull('t.numero_deposito_pago')
              ->whereNull('t.fecha_traslado_gasto')
              ->where('t.proforma_type', 'GASTO')
              ->whereNotIn('t.bank_id', [Bank::TERCEROS]);
        break;
    }

    $query->whereIn('t.proforma_status', ['FACTURADA'])
          ->orderBy('t.transaction_date', 'DESC');

    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);

        if (count($range) === 2) {
            try {
                // Convertir fechas a Carbon
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();

                // Filtro de rango (incluye todas las horas del día)
                $query->whereBetween('t.transaction_date', [$start, $end]);
            } catch (\Exception $e) {
                // manejar error
            }
        } else {
            try {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));

                // Comparar solo por la fecha, ignorando horas
                $query->whereDate('t.transaction_date', $singleDate->format('Y-m-d'));
            } catch (\Exception $e) {
                // manejar error
            }
        }
    }

    if (!empty($this->filters['filter_centroCosto'])) {
      $query->whereIn('cc.id', $this->filters['filter_centroCosto']);
    }

    if (!empty($this->filters['filter_department'])) {
      $query->where('t.department_id', '=', $this->filters['filter_department']);
    }
    return $query;
  }

  public function getColumns($type)
  {
      $columns = [];
      switch ($type) {
        case 1:
          // Con depósito
          $columns = [
              ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
              ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'Deudor', 'field' => 'deudor', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'O.C', 'field' => 'oc', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'MIGO', 'field' => 'migo', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Centro de Costo', 'field' => 'centroCosto', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
              ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
              ['label' => 'Monto de Gastos', 'field' => 'gastos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Monto Honorarios Menos Descuento', 'field' => 'honorariosConDescuento', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Monto I.V.A', 'field' => 'totalTax', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Total', 'field' => 'totalComprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Banco', 'field' => 'banco', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Departamento', 'field' => 'departamento', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Mensaje', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Notas', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Fecha de Pago', 'field' => 'fecha_deposito_pago', 'type' => 'string', 'align' => 'left', 'width' => 35],
              ['label' => 'Número de Depósito', 'field' => 'numero_deposito_pago', 'type' => 'string', 'align' => 'left', 'width' => 35],
              ['label' => 'Usuario', 'field' => 'usuario', 'type' => 'string', 'align' => 'left', 'width' => 35],
              ['label' => 'Número de Proforma', 'field' => 'proforma_no', 'type' => 'string', 'align' => 'left', 'width' => 30],
          ];
          break;
        case 2:
          // Sin Deposito
          $columns = [
              ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
              ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'Deudor', 'field' => 'deudor', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'O.C', 'field' => 'oc', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'MIGO', 'field' => 'migo', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Centro de Costo', 'field' => 'centroCosto', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
              ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
              ['label' => 'Monto de Gastos', 'field' => 'gastos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Monto Honorarios Menos Descuento', 'field' => 'honorariosConDescuento', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Monto I.V.A', 'field' => 'totalTax', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Total', 'field' => 'totalComprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Banco', 'field' => 'banco', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Departamento', 'field' => 'departamento', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Mensaje', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Notas', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Usuario', 'field' => 'usuario', 'type' => 'string', 'align' => 'left', 'width' => 35],
              ['label' => 'Número de Proforma', 'field' => 'proforma_no', 'type' => 'string', 'align' => 'left', 'width' => 30],
          ];
          break;
        case 3:
          // Honorarios
          $columns = [
              ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
              ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'Deudor', 'field' => 'deudor', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'O.C', 'field' => 'oc', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'MIGO', 'field' => 'migo', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Centro de Costo', 'field' => 'centroCosto', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
              ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
              ['label' => 'Monto Honorarios Menos Descuento', 'field' => 'honorariosConDescuento', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Monto I.V.A', 'field' => 'totalTax', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Total Honorario Mas IVA', 'field' => 'honorariosConIva', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Otros Gastos', 'field' => 'totalOtrosCargos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Total', 'field' => 'totalComprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Banco', 'field' => 'banco', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Departamento', 'field' => 'departamento', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Mensaje', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Notas', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Usuario', 'field' => 'usuario', 'type' => 'string', 'align' => 'left', 'width' => 35],
              ['label' => 'Número de Proforma', 'field' => 'proforma_no', 'type' => 'string', 'align' => 'left', 'width' => 30],
          ];
          break;
        case 4:
          // Gastos
          $columns = [
              ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
              ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
              ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'Deudor', 'field' => 'deudor', 'type' => 'string', 'align' => 'left', 'width' => 45],
              ['label' => 'O.C', 'field' => 'oc', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'MIGO', 'field' => 'migo', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Centro de Costo', 'field' => 'centroCosto', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
              ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
              ['label' => 'Monto de Gastos', 'field' => 'gastos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
              ['label' => 'Banco', 'field' => 'banco', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Departamento', 'field' => 'departamento', 'type' => 'string', 'align' => 'left', 'width' => 25],
              ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],
              ['label' => 'Mensaje', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Notas', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
              ['label' => 'Usuario', 'field' => 'usuario', 'type' => 'string', 'align' => 'left', 'width' => 35],
              ['label' => 'Número de Proforma', 'field' => 'proforma_no', 'type' => 'string', 'align' => 'left', 'width' => 30],
          ];
          break;
      }
      return $columns;
  }
}
