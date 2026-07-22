<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ResumePdfConverter
{
    public function convert(UploadedFile $resume): UploadedFile
    {
        $extension = strtolower($resume->getClientOriginalExtension());
        if (!in_array($extension, ['doc', 'docx'], true)) return $resume;

        $directory = storage_path('app/resume-conversions/'.Str::uuid());
        File::ensureDirectoryExists($directory);
        $input = $directory.'/resume.'.$extension;
        $output = $directory.'/resume.pdf';
        File::copy($resume->getRealPath(), $input);

        try {
            if (!$this->convertWithLibreOffice($input, $directory, $output)
                && !$this->convertWithMicrosoftWord($input, $output)) {
                throw new RuntimeException('DOC/DOCX resume conversion is unavailable.');
            }
            if (!is_file($output) || filesize($output) === 0) {
                throw new RuntimeException('The Word resume could not be converted to PDF.');
            }

            return new UploadedFile(
                $output,
                pathinfo($resume->getClientOriginalName(), PATHINFO_FILENAME).'.pdf',
                'application/pdf',
                UPLOAD_ERR_OK,
                true
            );
        } catch (\Throwable $e) {
            File::deleteDirectory($directory);
            throw $e;
        }
    }

    public function cleanup(UploadedFile $file): void
    {
        $root = str_replace('\\', '/', storage_path('app/resume-conversions'));
        $path = str_replace('\\', '/', $file->getPathname());
        if (str_starts_with($path, $root.'/')) File::deleteDirectory(dirname($path));
    }

    private function convertWithLibreOffice(string $input, string $directory, string $output): bool
    {
        $finder = new ExecutableFinder();
        $candidates = array_filter([
            config('services.resume_conversion.libreoffice_binary'),
            $finder->find('soffice'),
            $finder->find('libreoffice'),
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ]);

        foreach ($candidates as $binary) {
            if (str_contains($binary, DIRECTORY_SEPARATOR) && !is_file($binary)) continue;
            $process = new Process([$binary, '--headless', '--convert-to', 'pdf', '--outdir', $directory, $input]);
            $process->setTimeout(60);
            $process->run();
            if ($process->isSuccessful() && is_file($output)) return true;
        }
        return false;
    }

    private function convertWithMicrosoftWord(string $input, string $output): bool
    {
        if (PHP_OS_FAMILY !== 'Windows' || !class_exists('COM')) return false;
        $word = null;
        $document = null;
        try {
            $word = new \COM('Word.Application');
            $word->Visible = false;
            $word->DisplayAlerts = 0;
            $document = $word->Documents->Open(str_replace('/', '\\', $input), false, true);
            $document->SaveAs(str_replace('/', '\\', $output), 17);
            return is_file($output);
        } catch (\Throwable $e) {
            report($e);
            return false;
        } finally {
            if ($document) try { $document->Close(false); } catch (\Throwable $e) {}
            if ($word) try { $word->Quit(); } catch (\Throwable $e) {}
        }
    }
}
