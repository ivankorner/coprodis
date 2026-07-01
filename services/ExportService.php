<?php

namespace App\Services;

use App\Core\Database;

class ExportService
{
    public static function toExcel(string $module, array $headers, array $data, string $filename = null): string
    {
        require_once BASE_PATH . '/vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
        ];
        $sheet->getStyle('A1:' . $col . '1')->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($item as $value) {
                $sheet->setCellValue($col . $row, $value ?? '');
                $col++;
            }
            $row++;
        }

        foreach (range('A', $col) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = $filename ?? "{$module}_" . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = BASE_PATH . "/storage/exports/{$filename}";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    public static function toCsv(string $module, array $headers, array $data, string $filename = null): string
    {
        $filename = $filename ?? "{$module}_" . date('Y-m-d_H-i-s') . '.csv';
        $filepath = BASE_PATH . "/storage/exports/{$filename}";

        $handle = fopen($filepath, 'w');

        // BOM for UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        fputcsv($handle, $headers, ';');

        // Data
        foreach ($data as $item) {
            fputcsv($handle, array_values((array)$item), ';');
        }

        fclose($handle);
        return $filepath;
    }

    public static function toPdf(string $view, array $data, string $filename = null): string
    {
        require_once BASE_PATH . '/vendor/autoload.php';

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('COPRODIS');
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle($data['title'] ?? 'Documento');

        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(true, 25);

        $pdf->AddPage();

        // Render view content
        $html = self::renderView($view, $data);
        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = $filename ?? "documento_" . date('Y-m-d_H-i-s') . '.pdf';
        $filepath = BASE_PATH . "/storage/exports/{$filename}";
        $pdf->Output($filepath, 'F');

        return $filepath;
    }

    private static function renderView(string $view, array $data): string
    {
        $viewPath = str_replace('.', '/', $view);
        $file = VIEWS_PATH . "/{$viewPath}.php";

        if (!file_exists($file)) {
            return '<h1>Vista no encontrada</h1>';
        }

        extract($data);
        ob_start();
        require $file;
        return ob_get_clean();
    }
}
