<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class ResumeTextExtractor
{
    /**
     * Extract text from an already-saved file given its path and extension.
     * Used when the file has been downloaded to a temp location.
     */
    public function extractFromPath(string $path, string $ext): string
    {
        if (!is_readable($path)) {
            return '';
        }

        try {
            $text = match (strtolower($ext)) {
                'pdf'        => $this->fromPdf($path),
                'docx'       => $this->fromDocx($path),
                'xlsx', 'xls'=> $this->fromSpreadsheet($path),
                'rtf'        => $this->fromRtf(file_get_contents($path) ?: ''),
                'txt'        => (string)(file_get_contents($path) ?: ''),
                default      => '',
            };
        } catch (\Throwable) {
            return '';
        }

        return $this->normalizeWhitespace($text);
    }

    /**
     * Extract plain text from common resume file types (PDF, DOCX, XLS/XLSX, RTF).
     * Returns empty string on parse errors instead of throwing, so callers can
     * decide how to handle unreadable files gracefully.
     */
    public function extract(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        $path = $file->getRealPath();
        if ($path === false || ! is_readable($path)) {
            throw new \InvalidArgumentException(__('messages.resumeReadFailed'));
        }

        try {
            $text = match ($ext) {
                'pdf'              => $this->fromPdf($path),
                'docx'             => $this->fromDocx($path),
                'xlsx', 'xls'      => $this->fromSpreadsheet($path),
                'rtf'              => $this->fromRtf(file_get_contents($path) ?: ''),
                'txt'              => (string) (file_get_contents($path) ?: ''),
                'doc'              => throw new \InvalidArgumentException(__('messages.resumeDocBinaryNotSupported')),
                'png', 'jpg', 'jpeg', 'gif', 'webp' => throw new \InvalidArgumentException(__('messages.resumeImageNotSupported')),
                default            => throw new \InvalidArgumentException(__('messages.resumeFormatNotSupported')),
            };
        } catch (\InvalidArgumentException $e) {
            throw $e; // re-throw unsupported format errors
        } catch (\Throwable) {
            return ''; // silently return empty for parse errors (malformed PDF etc.)
        }

        return $this->normalizeWhitespace($text);
    }

   private function fromPdf(string $path): string
    {
        // PRIMARY: pdftotext (most reliable, handles encodings and edge cases)
        $pdftotext = shell_exec('which pdftotext 2>/dev/null');
        if ($pdftotext) {
            $output = shell_exec('pdftotext -layout -enc UTF-8 ' . escapeshellarg($path) . ' - 2>/dev/null');
            if ($output && strlen($output) > 50) {
                return $this->normalizeWhitespace($output);
            }
        }

        // FALLBACK: Smalot\PdfParser
        try {
            try {
                $config = new \Smalot\PdfParser\Config();
                $config->setRetainImageContent(false);
                $parser = new PdfParser(null, $config);
            } catch (\Throwable $e) {
                $parser = new PdfParser();
            }
            $pdf  = $parser->parseFile($path);
            $text = $pdf->getText();
            return is_string($text) ? $this->normalizeWhitespace($text) : '';
        } catch (\Throwable $e) {
            \Log::warning('PDF extraction failed for ' . $path . ': ' . $e->getMessage());
            return '';
        }
    }

    private function fromDocx(string $path): string
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return '';
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml === false || $xml === '') {
            return '';
        }
        $withBreaks = str_replace(['</w:p>', '</w:tab>'], "\n", $xml);
        $text = strip_tags($withBreaks);

        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function fromSpreadsheet(string $path): string
    {
        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Throwable) {
            return '';
        }
        $parts = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $v = $cell->getValue();
                    if ($v !== null && $v !== '') {
                        $cells[] = is_scalar($v) ? (string) $v : '';
                    }
                }
                if ($cells !== []) {
                    $parts[] = implode(' ', $cells);
                }
            }
        }

        return implode("\n", $parts);
    }

    private function fromRtf(string $raw): string
    {
        // Minimal RTF stripping — good enough for contact lines and headings.
        $t = preg_replace('/\{[^}]*\}/', ' ', $raw) ?? '';
        $t = preg_replace('/\\\\([a-z]+)(-?\d+)? ?/i', ' ', $t) ?? '';
        $t = preg_replace('/[{}\\\\]/', ' ', $t) ?? '';

        return $t;
    }

    private function normalizeWhitespace(string $text): string
    {
        $text = preg_replace("/[\x00-\x08\x0B\x0C\x0E-\x1F]/u", '', $text) ?? '';
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? '';
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? '';

        return trim($text);
    }
}
