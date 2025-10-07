<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionCommission;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RegistroReport extends BaseReport implements WithEvents
{
  protected array $processedTransactions = [];

  public function __construct(array $filters, $title)
  {
      parent::__construct($filters, $title);
      $this->processedTransactions = []; // reset en cada exportación
  }

  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 45],
      ['label' => 'Deudor', 'field' => 'deudor', 'type' => 'string', 'align' => 'left', 'width' => 45],
      ['label' => 'O.C', 'field' => 'oc', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'MIGO', 'field' => 'migo', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],

      ['label' => 'Linea de detalle', 'field' => 'detail', 'type' => 'decimal', 'align' => 'right', 'width' => 60],
      ['label' => 'Centro de Costo', 'field' => 'centroCosto', 'type' => 'decimal', 'align' => 'right', 'width' => 45],

      ['label' => 'Monto de Gastos', 'field' => 'gastos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto Honorarios Menos Descuento', 'field' => 'honorariosConDescuento', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total', 'field' => 'totalComprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Banco', 'field' => 'banco', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Departamento', 'field' => 'departamento', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Mensaje', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
      ['label' => 'Notas', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
      ['label' => 'Fecha de Pago', 'field' => 'fecha_deposito_pago', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Número de Depósito', 'field' => 'numero_deposito_pago', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Fecha de traslado de gasto', 'field' => 'fecha_traslado_gasto', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Usuario', 'field' => 'usuario', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Número de Proforma', 'field' => 'proforma_no', 'type' => 'string', 'align' => 'left', 'width' => 30],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Transaction::withoutGlobalScopes()
      ->from('transactions as t')
      ->selectRaw("
          t.id,
          t.consecutivo,
          CASE
              WHEN t.transaction_date IS NULL THEN ''
              ELSE DATE_FORMAT(t.transaction_date, '%d-%m-%Y')
          END AS transaction_date,
          t.customer_name,
          CAST(c.identification AS CHAR) AS identification,
          emisor.name as nombreEmisor,
          b.name as banco,
          cu.code as moneda,
          cu.symbol as monedasymbolo,
          t.proforma_change_type,
          d.name as departamento,
          CASE
              WHEN t.proforma_status = 'ANULADA' THEN 0
              ELSE (COALESCE(t.totalTimbres,0))
          END AS gastos,

          CASE
              WHEN t.proforma_status = 'ANULADA' THEN 0
              ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0))
          END AS honorariosConDescuento,

          CASE
              WHEN t.proforma_status = 'ANULADA' THEN 0
              ELSE COALESCE(t.totalTax,0)
          END AS totalTax,

          CASE
              WHEN t.proforma_status = 'ANULADA' THEN 0
              ELSE COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0)
          END AS honorariosConIva,

          CASE
              WHEN t.proforma_status = 'ANULADA' THEN 0
              ELSE COALESCE(t.totalOtrosCargos,0)
          END AS totalOtrosCargos,

          CASE
              WHEN t.proforma_status = 'ANULADA' THEN 0
              ELSE COALESCE(t.totalComprobante,0)
          END AS totalComprobante,

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
      ->join('transactions_commissions as tc', 'tc.transaction_id', '=', 't.id')
      ->leftJoin('centro_costos as cc', 'tc.centro_costo_id', '=', 'cc.id')
      ->join('business_locations as emisor', 't.location_id', '=', 'emisor.id')
      ->join('contacts as c', 't.contact_id', '=', 'c.id')
      ->join('departments as d', 't.department_id', '=', 'd.id')
      ->join('banks as b', 't.bank_id', '=', 'b.id')
      ->join('currencies as cu', 't.currency_id', '=', 'cu.id')
      ->leftJoin('users as u', 't.created_by', '=', 'u.id')
      ->leftJoin('casos as ca', 't.caso_id', '=', 'ca.id')
      ->whereNull('t.deleted_at')
      ->whereIn('t.document_type', ['PR','FE','TE'])
      ->whereIn('t.proforma_status', ['FACTURADA'])
      ->whereNotNull('t.fecha_deposito_pago')
      ->whereNotNull('t.numero_deposito_pago')
      ->where('t.proforma_type', 'GASTO')
      ->whereExists(function($q) {
        $q->selectRaw(1)
          ->from('transactions_lines as tl')
          ->whereColumn('tl.transaction_id', 't.id')
          ->whereNull('tl.fecha_pago_registro');
      })
      ->orderBy('t.id', 'desc');


    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);

        try {
            if (count($range) === 2) {
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();

                $query->whereBetween('t.transaction_date', [$start, $end]);
            } else {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));
                $query->whereDate('t.transaction_date', $singleDate->format('Y-m-d'));
            }
        } catch (\Exception $e) {
            // Opcional: podrías registrar el error para depurar
            //\Log::error("Error en filtro de fechas: ".$e->getMessage());
        }
    }

    if (!empty($this->filters['filter_department'])) {
      $query->where('t.department_id', '=', $this->filters['filter_department']);
    }

    if (!empty($this->filters['filter_centroCosto'])) {
      $query->whereIn('cc.id', $this->filters['filter_centroCosto']);
    }

    return $query;
  }

  public function registerEvents(): array
  {
      return [
          AfterSheet::class => function (AfterSheet $event) {
              $sheet = $event->sheet->getDelegate();

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
                  $colLetter = $this->columnLetter($index);
                  $sheet->setCellValue("{$colLetter}3", $heading);
                  $sheet->getStyle("{$colLetter}3")->getFont()->setBold(true);
                  $sheet->getStyle("{$colLetter}3")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setWrapText(true);
              }
              $sheet->getRowDimension(3)->setRowHeight(-1);

              // --- COLUMNAS ---
              foreach ($this->columnWidths() as $col => $width) {
                  $sheet->getColumnDimension($col)->setWidth($width);
              }

              $lastRow = $sheet->getHighestRow();

              // --- RESALTAR FILAS PRINCIPALES ---
              $lastRow = $sheet->getHighestRow();
              $lastTransactionId = null;
              for ($row = 4; $row <= $lastRow; $row++) {
                  $transactionId = $sheet->getCell("A{$row}")->getValue();
                  if ($transactionId && $transactionId !== $lastTransactionId) {
                      $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFCCFFCC'); // verde
                      $lastTransactionId = $transactionId;
                  }
              }

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
              $totalsRow = $lastRow + 1;
              foreach ($this->columns() as $index => $col) {
                  $letter = $this->columnLetter($index);
                  if (in_array($col['type'] ?? 'string', ['decimal','currency','integer'])) {
                      $sheet->setCellValue("{$letter}{$totalsRow}", "=SUM({$letter}4:{$letter}{$lastRow})");
                      $sheet->getStyle("{$letter}{$totalsRow}")
                            ->getNumberFormat()->setFormatCode('#,##0.00');
                      $sheet->getStyle("{$letter}{$totalsRow}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                      $sheet->getStyle("{$letter}{$totalsRow}")->getFont()->setBold(true);
                  }
              }
              $sheet->setCellValue("A{$totalsRow}", 'TOTALES');
              $sheet->getStyle("A{$totalsRow}")->getFont()->setBold(true);

              // Ajuste de altura
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

  public function map($row): array
  {
      $mapped = [];

      // --- Fila principal ---
      $mainRow = collect($this->columns())->map(function ($col) use ($row) {
          $field = $col['field'];
          $value = $row->{$field} ?? null;
          $type = $col['type'] ?? 'string';

          switch ($type) {
              case 'date':
                  if ($value instanceof \DateTimeInterface) {
                      return \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($value->format('Y-m-d'));
                  }
                  if (is_string($value) && !empty($value)) {
                      try {
                          $dt = \Carbon\Carbon::createFromFormat('d-m-Y', $value);
                      } catch (\Exception $e) {
                          try {
                              $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $value);
                          } catch (\Exception $e2) {
                              return null;
                          }
                      }
                      return \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($dt->format('Y-m-d'));
                  }
                  return null;

              case 'currency':
              case 'decimal':
                  if (is_null($value) || $value === '') return null;
                  return is_numeric($value) ? (float)$value : null;

              case 'integer':
                  if (is_null($value) || $value === '') return null;
                  return is_numeric($value) ? (int)$value : null;

              case 'string':
                  if ($field === 'identification') {
                      // Forzar string y evitar notación científica
                      $val = (string)trim(strip_tags((string)$value));
                      if (is_numeric($val) && strlen($val) > 12) {
                          $val = "'".$val; // PhpSpreadsheet no lo convierte a científico
                      }
                      return $val;
                  }
                  return trim(strip_tags((string)$value));

              default:
                  return $value;
          }

          // --- Ajuste específico para id ---
          if ($field === 'id' && is_numeric($value)) {
              return (int)$value;
          }

      })->toArray();

      $mapped[] = $mainRow;

      // --- Líneas de detalle ---
      if (!in_array($row->id, $this->processedTransactions))
      {
        $this->processedTransactions[] = $row->id;

          foreach ($this->filters['idsCostos'] as $ccId) {

              // Obtengo la transacción con las líneas SIN fecha_pago_registro
              $transaction = Transaction::with([
                  'lines' => function($q) {
                      $q->whereNull('fecha_pago_registro');
                  },
                  'bank',
                  'area'
              ])->find($row->id);

              // Busco el centro de costo de la transacción
              $facturaCentro = TransactionCommission::where('transaction_id', $row->id)
                                ->where('centro_costo_id', $ccId)
                                ->with('centroCosto')
                                ->first();

              // Moneda
              $moneda = $transaction->currency_id == 16 ? 'CRC' : 'USD';

              if ($transaction && $transaction->lines && $facturaCentro) {
                  foreach ($transaction->lines as $line) {

                      // Cálculos proporcionales
                      $total_timbres    = $line->timbres * $facturaCentro->percent / 100;
                      $total_honorarios = $line->honorarios * $facturaCentro->percent / 100;
                      $total_detalle    = $line->total * $facturaCentro->percent / 100;

                      $mapped[] = [
                          '', '', '', '','',
                          $transaction->oc,
                          $transaction->migo,
                          $moneda,
                          number_format($transaction->proforma_change_type, 2, ".", ","),
                          $line->detail,
                          $facturaCentro->centroCosto->descrip ?? '',
                          number_format($total_timbres, 2, ".", ","),
                          number_format($total_honorarios, 2, ".", ","),
                          number_format($total_detalle, 2, ".", ","),
                          $transaction->bank->name ?? '',
                          $transaction->area->name ?? '',
                          '', '', '', '',
                          $line->fecha_traslado_gasto ? Carbon::parse($line->fecha_traslado_gasto)->format('d-m-Y') : '',
                          'is_main' => false
                      ];
                  }
              }
          }
      }
      return $mapped;
  }
}
