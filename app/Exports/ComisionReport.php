<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionCommission;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ComisionReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Factura', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 45],
      ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Código del Emisor', 'field' => 'codigoEmisor', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
      ['label' => 'Total Honorario', 'field' => 'honorariosConDescuento', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Fecha de despósito', 'field' => 'fecha_deposito_pago', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Comisionista', 'field' => 'comisionista', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Porcentaje de comisión', 'field' => 'commission_percent', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'estadoComision', 'field' => 'estadoComision', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Fecha de Pago', 'field' => 'comision_pagada_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Total Comisión', 'field' => 'montoComision', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = TransactionCommission::query()
    ->from('transactions_commissions as tc')
    ->selectRaw("
        t.id,
        tc.id as transaction_commision_id,
        CASE
            WHEN cc.codigo IS NULL OR codcontable.codigo IS NULL THEN '-'
            ELSE REPLACE(REPLACE(codcontable.codigo, 'XX', cc.codigo), 'YYY', emisor.code)
        END AS codcont,
        t.consecutivo,
        CASE
            WHEN t.transaction_date IS NULL THEN ''
            ELSE DATE_FORMAT(t.transaction_date, '%d-%m-%Y')
        END AS transaction_date,
        CASE
            WHEN t.fecha_deposito_pago IS NULL THEN ''
            ELSE DATE_FORMAT(t.fecha_deposito_pago, '%d-%m-%Y')
        END AS fecha_deposito_pago,
        t.customer_name,
        CAST(c.identification AS CHAR) AS identification,
        emisor.name as nombreEmisor,
        emisor.code as codigoEmisor,
        com.nombre as comisionista,
        d.name as departamento,
        b.name as banco,
        ca.numero,
        cu.code as moneda,
        cu.symbol as monedasymbolo,
        t.proforma_change_type,
        tc.commission_percent,
        CASE
            WHEN tc.comision_pagada_date IS NULL THEN ''
            ELSE DATE_FORMAT(tc.comision_pagada_date, '%d-%m-%Y')
        END AS comision_pagada_date,

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
            WHEN COALESCE(t.totalHonorarios,0) = 0 OR COALESCE(tc.percent,0) = 0 OR COALESCE(tc.commission_percent,0) = 0 THEN 0
            ELSE ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0)) * tc.commission_percent / 100
        END AS montoComision,

        CASE
            WHEN tc.comision_pagada = 1 THEN 'PAGADA'
            ELSE 'NO PAGADA'
        END AS estadoComision,

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
        t.numero_deposito_pago,
        t.message AS message,
        t.proforma_no,
        ca.deudor
    ")
    ->join('transactions as t', 'tc.transaction_id', '=', 't.id')
    ->leftJoin('centro_costos as cc', 'tc.centro_costo_id', '=', 'cc.id')
    ->leftJoin('codigo_contables as codcontable', 't.codigo_contable_id', '=', 'codcontable.id')
    ->leftJoin('business_locations as emisor', 't.location_id', '=', 'emisor.id')
    ->leftJoin('comisionistas as com', 'tc.comisionista_id', '=', 'com.id')
    ->join('contacts as c', 't.contact_id', '=', 'c.id')
    ->join('departments as d', 't.department_id', '=', 'd.id')
    ->join('banks as b', 't.bank_id', '=', 'b.id')
    ->join('currencies as cu', 't.currency_id', '=', 'cu.id')
    ->leftJoin('casos as ca', 't.caso_id', '=', 'ca.id')
    ->whereIn('t.proforma_status', ['FACTURADA'])
    ->whereNotNull('t.fecha_deposito_pago')
    ->whereNotNull('t.numero_deposito_pago')
    ->whereIn('t.document_type', ['FE','TE'])
    ->where('t.proforma_type', 'HONORARIO')
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

    if (!empty($this->filters['filter_abogado'])) {
      $query->where('tc.comisionista_id', '=', $this->filters['filter_abogado']);
    }

    if (!empty($this->filters['filter_department'])) {
      $query->where('t.department_id', '=', $this->filters['filter_department']);
    }

    if (!empty($this->filters['filter_currency'])) {
      $query->where('t.currency_id', '=', $this->filters['filter_currency']);
    }

    if (!empty($this->filters['filter_type'])) {
      if ($this->filters['filter_type'] == 1)
        $comision = 0;
      else
        $comision = 1;
      $query->where('tc.comision_pagada', '=', $comision);
    }

    return $query;
  }

  public function registerEvents(): array
  {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                if (!empty($this->filters['filter_pagar']) && $this->filters['filter_pagar'] == 1) {
                  // Obtener los IDs que se exportaron
                  $ids = $this->query()->pluck('transaction_commision_id');

                  // Actualización masiva
                  TransactionCommission::whereIn('id', $ids)
                      ->where('comision_pagada', 0)
                      ->update([
                          'comision_pagada' => 1,
                          'comision_pagada_date' => now(),
                      ]);
                }

                // --- LOGO ---
                $logoPath = public_path('storage/assets/default-image.png');
                if (method_exists($this, 'getLogoPath')) {
                    $customLogo = $this->getLogoPath();
                    if ($customLogo && file_exists($customLogo)) {
                        $logoPath = $customLogo;
                    }
                }
                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo de la empresa');
                $drawing->setPath($logoPath);
                $drawing->setHeight(50);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);

                // --- TÍTULO ---
                $lastColumnLetter = $this->columnLetter(count($this->columns()) - 1);
                $sheet->mergeCells("B1:{$lastColumnLetter}1");
                $sheet->setCellValue('B1', $this->title);
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(60);

                // --- ENCABEZADOS ---
                $headings = $this->headings();
                foreach ($headings as $index => $heading) {
                    $columnLetter = $this->columnLetter($index);
                    $sheet->setCellValue("{$columnLetter}3", $heading);
                    $sheet->getStyle("{$columnLetter}3")->getFont()->setBold(true);
                    $sheet->getStyle("{$columnLetter}3")->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                          ->setWrapText(true);
                }
                $sheet->getRowDimension(3)->setRowHeight(-1);

                // --- ANCHO DE COLUMNAS ---
                foreach ($this->columnWidths() as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                $lastRow = $sheet->getHighestRow();
                $totalsRow = $lastRow + 1;

                // --- IDENTIFICATION como TEXTO ---
                foreach ($this->columns() as $index => $col) {
                    if (($col['field'] ?? '') === 'identification') {
                        $colLetter = $this->columnLetter($index);
                        for ($row = 4; $row <= $lastRow; $row++) {
                            $sheet->setCellValueExplicit(
                                "{$colLetter}{$row}",
                                (string)$sheet->getCell("{$colLetter}{$row}")->getValue(),
                                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                            );
                        }
                        $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                              ->getNumberFormat()
                              ->setFormatCode(NumberFormat::FORMAT_TEXT);
                        break;
                    }
                }

                // --- TOTALES ---
                foreach ($this->columns() as $index => $col) {
                    $colLetter = $this->columnLetter($index);

                    if (in_array($col['type'] ?? 'string', ['decimal','currency','integer'])) {
                        $sheet->setCellValue("{$colLetter}{$totalsRow}", "=SUM({$colLetter}4:{$colLetter}{$lastRow})");

                        $sheet->getStyle("{$colLetter}{$totalsRow}")
                              ->getNumberFormat()->setFormatCode('#,##0.00');
                        $sheet->getStyle("{$colLetter}{$totalsRow}")
                              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("{$colLetter}{$totalsRow}")->getFont()->setBold(true);
                    }
                }

                $sheet->setCellValue("A{$totalsRow}", 'TOTALES');
                $sheet->getStyle("A{$totalsRow}")->getFont()->setBold(true);

                // --- AJUSTE DE TEXTO ---
                foreach ($this->columns() as $index => $col) {
                    if (($col['type'] ?? 'string') === 'string') {
                        $colLetter = $this->columnLetter($index);
                        $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                              ->getAlignment()->setWrapText(true);
                    }
                }

                // --- ALTURA AUTOMÁTICA FILAS DATOS ---
                for ($row = 4; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }

                // --- FORZAR ID COMO ENTERO ---
                foreach ($this->columns() as $index => $col) {
                    if (($col['field'] ?? '') === 'id') {
                        $colLetter = $this->columnLetter($index);

                        // Asegurar que cada celda se trate como número entero
                        for ($row = 4; $row <= $lastRow; $row++) {
                            $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();
                            if (is_numeric($cellValue)) {
                                $sheet->setCellValueExplicit(
                                    "{$colLetter}{$row}",
                                    (int)$cellValue,
                                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                                );
                            }
                        }

                        // Formato de celda entero (sin decimales)
                        $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                              ->getNumberFormat()->setFormatCode('0');
                        break;
                    }
                }
            },
        ];
    }
}
