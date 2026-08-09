<?php

namespace App\Services;

use SimpleXMLElement;
use ZipArchive;

class ExcelService
{
    /**
     * Export neat, styled Excel document (.xls) with custom title, headers, sample rows, and instructions.
     */
    public static function downloadStyledExcel(string $filename, string $title, array $headers, array $sampleRows, array $instructions = [])
    {
        $headerBg = '#0284C7';
        $headerColor = '#FFFFFF';

        $html = '<!DOCTYPE html>';
        $html .= '<html><head><meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; font-family: Calibri, sans-serif; font-size: 11pt; }';
        $html .= 'th { background-color: ' . $headerBg . '; color: ' . $headerColor . '; font-weight: bold; text-align: center; border: 1px solid #0369a1; padding: 10px 15px; }';
        $html .= 'td { border: 1px solid #cbd5e1; padding: 8px 12px; vertical-align: middle; }';
        $html .= '.title-row { font-size: 14pt; font-weight: bold; color: #0f172a; margin-bottom: 10px; }';
        $html .= '.note-box { background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px; font-size: 9pt; color: #334155; margin-bottom: 15px; }';
        $html .= '.zebra { background-color: #f8fafc; }';
        $html .= '</style></head><body>';

        $html .= '<div class="title-row">' . htmlspecialchars($title) . '</div>';

        if (!empty($instructions)) {
            $html .= '<div class="note-box"><strong>PANDUAN PENGISIAN TEMPLATE EXCEL:</strong><ul>';
            foreach ($instructions as $inst) {
                $html .= '<li>' . htmlspecialchars($inst) . '</li>';
            }
            $html .= '</ul></div>';
        }

        $html .= '<table><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars(strtoupper($h)) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($sampleRows as $index => $row) {
            $bgClass = ($index % 2 === 1) ? ' class="zebra"' : '';
            $html .= '<tr' . $bgClass . '>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Read uploaded Excel file (.xlsx, .xls, .csv) into array of rows.
     */
    public static function parseUploadedFile($file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getRealPath();

        if ($extension === 'xlsx') {
            return self::parseXLSX($filePath);
        }

        // For CSV or HTML/XML .xls
        return self::parseCSVOrHTML($filePath);
    }

    /**
     * Parse native XLSX using PHP ZipArchive + SimpleXML.
     */
    private static function parseXLSX(string $filePath): array
    {
        $rows = [];
        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            return self::parseCSVOrHTML($filePath);
        }

        // Load shared strings
        $sharedStrings = [];
        if (($sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string)$val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $run) {
                            $text .= (string)$run->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // Load sheet 1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $type = (string)$cell['t'];
                $val = (string)$cell->v;

                if ($type === 's' && isset($sharedStrings[(int)$val])) {
                    $rowData[] = trim($sharedStrings[(int)$val]);
                } else {
                    $rowData[] = trim($val);
                }
            }
            if (!empty(array_filter($rowData))) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    /**
     * Parse CSV or HTML table based .xls file.
     */
    private static function parseCSVOrHTML(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $rows = [];

        // Check if content is HTML table
        if (str_contains($content, '<table') || str_contains($content, '<tr')) {
            // Load DOM Document
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($content);
            libxml_clear_errors();

            $tableRows = $dom->getElementsByTagName('tr');
            foreach ($tableRows as $tr) {
                $rowData = [];
                $cells = $tr->getElementsByTagName('td');
                if ($cells->length === 0) {
                    $cells = $tr->getElementsByTagName('th');
                }
                foreach ($cells as $cell) {
                    $rowData[] = trim($cell->nodeValue);
                }
                if (!empty(array_filter($rowData))) {
                    $rows[] = $rowData;
                }
            }
            return $rows;
        }

        // Fallback: Parse standard CSV
        $handle = fopen($filePath, 'r');
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 1000, ',');
        $delimiter = ',';
        if (!$header || count($header) < 2) {
            rewind($handle);
            if ($bom === "\xEF\xBB\xBF") fread($handle, 3);
            $header = fgetcsv($handle, 1000, ';');
            $delimiter = ';';
        }
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") fread($handle, 3);

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
            if (!empty(array_filter($data))) {
                $rows[] = array_map('trim', $data);
            }
        }
        fclose($handle);

        return $rows;
    }
}
