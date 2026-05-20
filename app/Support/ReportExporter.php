<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExporter
{
    public static function toExcel(string $filename, string $sheetTitle, array $headers, array $rows): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        $colIndex = 1;
        foreach ($headers as $header) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $cell = $col . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $colIndex++;
        }

        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($row as $value) {
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($col . $rowIndex, $value);
                $colIndex++;
            }
            $rowIndex++;
        }

        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
