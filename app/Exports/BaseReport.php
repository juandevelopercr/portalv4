<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

abstract class BaseReport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected array $filters = [];
    protected $title;

    public function __construct(array $filters, $title)
    {
        $this->filters = $filters;
        $this->title = $title;
    }

    abstract protected function columns(): array;
    abstract public function query(): \Illuminate\Database\Eloquent\Builder;

    public function headings(): array
    {
        return array_column($this->columns(), 'label');
    }

    public function map($row): array
    {
        return collect($this->columns())->map(function ($col) use ($row) {
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
                    if (is_numeric($value)) return (float) $value;
                    $normalized = preg_replace('/[^0-9,\.\-]/', '', (string) $value);
                    if (strpos($normalized, ',') !== false && strpos($normalized, '.') !== false) {
                        $normalized = str_replace('.', '', $normalized);
                        $normalized = str_replace(',', '.', $normalized);
                    } else {
                        $normalized = str_replace(',', '.', $normalized);
                    }
                    return is_numeric($normalized) ? (float) $normalized : null;

                case 'integer':
                    if (is_null($value) || $value === '') return null;
                    return is_numeric($value) ? (int) $value : null;

                case 'string':
                    $value = (string) $value;
                    return trim(strip_tags($value));

                default:
                    return $value;
            }
        })->toArray();
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

    public function columnFormats(): array
    {
        $formats = [];
        foreach ($this->columns() as $index => $col) {
            $letter = $this->columnLetter($index);
            switch ($col['type'] ?? 'string') {
                case 'date':
                    $formats[$letter] = NumberFormat::FORMAT_DATE_DDMMYYYY;
                    break;
                case 'currency':
                    $formats[$letter] = '#,##0.00_-';
                    break;
                case 'decimal':
                    $formats[$letter] = '#,##0.00';
                    break;
                case 'integer':
                    $formats[$letter] = '#,##0';
                    break;
                default:
                    $formats[$letter] = NumberFormat::FORMAT_TEXT;
            }
        }
        return $formats;
    }

    public function columnWidths(): array
    {
        $widths = [];
        foreach ($this->columns() as $index => $col) {
            $letter = $this->columnLetter($index);
            $widths[$letter] = $col['width'] ?? 20;
        }
        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $alignments = [];
        foreach ($this->columns() as $index => $col) {
            $letter = $this->columnLetter($index);
            $align = $col['align'] ?? 'left';

            $horizontal = match ($align) {
                'center' => Alignment::HORIZONTAL_CENTER,
                'right'  => Alignment::HORIZONTAL_RIGHT,
                default  => Alignment::HORIZONTAL_LEFT,
            };

            $alignments[$letter] = [
                'alignment' => ['horizontal' => $horizontal],
            ];
        }
        return $alignments;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = intdiv($index, 26) - 1;
        }
        return $letter;
    }


    /*
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
                    $columnLetter = $this->columnLetter($index);
                    $sheet->setCellValue("{$columnLetter}3", $heading);
                    $sheet->getStyle("{$columnLetter}3")->getFont()->setBold(true);
                    $sheet->getStyle("{$columnLetter}3")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setWrapText(true);
                }
                $sheet->getRowDimension(3)->setRowHeight(-1);

                // --- COLUMNAS ---
                foreach ($this->columnWidths() as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                $lastRow = $sheet->getHighestRow();
                $totalsRow = $lastRow + 1;

                // --- identification como texto ---
                foreach ($this->columns() as $index => $col) {
                    if (($col['field'] ?? '') === 'identification') {
                        $colLetter = $this->columnLetter($index);
                        $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                              ->getNumberFormat()
                              ->setFormatCode(NumberFormat::FORMAT_TEXT);
                        break;
                    }
                }

                // --- TOTALES ---
                foreach ($this->columns() as $index => $col) {
                    $letter = $this->columnLetter($index);

                    if (in_array($col['type'] ?? 'string', ['decimal','currency','integer'])) {

                        // Forzar NUMERO en celdas para SUM
                        for ($row = 4; $row <= $lastRow; $row++) {
                            $cellValue = $sheet->getCell("{$letter}{$row}")->getValue();
                            if (!is_numeric($cellValue)) {
                                $numericValue = floatval(str_replace([',', '$', ' '], ['', '', ''], $cellValue));
                                $sheet->setCellValueExplicit("{$letter}{$row}", $numericValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            }
                        }

                        // SUMA
                        $sheet->setCellValue("{$letter}{$totalsRow}", "=SUM({$letter}4:{$letter}{$lastRow})");

                        // Formato
                        $sheet->getStyle("{$letter}{$totalsRow}")
                              ->getNumberFormat()->setFormatCode('#,##0.00');
                        $sheet->getStyle("{$letter}{$totalsRow}")
                              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("{$letter}{$totalsRow}")->getFont()->setBold(true);
                    }
                }

                $sheet->setCellValue("A{$totalsRow}", 'TOTALES');
                $sheet->getStyle("A{$totalsRow}")->getFont()->setBold(true);

                // Ajuste de texto y altura
                foreach ($this->columns() as $index => $col) {
                    if (($col['type'] ?? 'string') === 'string') {
                        $colLetter = $this->columnLetter($index);
                        $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                              ->getAlignment()->setWrapText(true);
                    }
                }
                for ($row = 4; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
    */

    protected function getLogoPath(): ?string
    {
        $business = \App\Models\Business::find(1);
        $logoFileName = $business?->logo;
        $logoPath = public_path("storage/assets/img/logos/{$logoFileName}");
        if (!file_exists($logoPath)) {
            return public_path("storage/assets/default-image.png");
        }
        return $logoPath;
    }
}
