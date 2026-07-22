<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
                && !$this->convertWithMicrosoftWord($input, $output)
                && !$this->convertWithPowerShellWord($input, $output, $directory)) {
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
        $profileDirectory = $directory.'/libreoffice-profile';
        File::ensureDirectoryExists($profileDirectory);
        $profileUri = 'file:///'.ltrim(str_replace('\\', '/', $profileDirectory), '/');
        $candidates = array_filter([
            config('services.resume_conversion.libreoffice_binary'),
            $finder->find('soffice'),
            $finder->find('libreoffice'),
            '/usr/bin/libreoffice',
            '/usr/bin/soffice',
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ]);

        foreach ($candidates as $binary) {
            if (str_contains($binary, DIRECTORY_SEPARATOR) && !is_file($binary)) continue;
            $process = new Process([
                $binary,
                '--headless',
                '--nologo',
                '--nodefault',
                '--nofirststartwizard',
                '-env:UserInstallation='.$profileUri,
                '--convert-to', 'pdf',
                '--outdir', $directory,
                $input,
            ]);
            $process->setTimeout(60);
            $process->run();
            if ($process->isSuccessful() && is_file($output)) return true;

            Log::warning('LibreOffice resume conversion failed.', [
                'binary' => $binary,
                'exit_code' => $process->getExitCode(),
                'stdout' => trim($process->getOutput()),
                'stderr' => trim($process->getErrorOutput()),
                'output_created' => is_file($output),
            ]);
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
            $document->ExportAsFixedFormat(str_replace('/', '\\', $output), 17);
            clearstatcache(true, $output);
            return is_file($output) && filesize($output) > 0;
        } catch (\Throwable $e) {
            report($e);
            return false;
        } finally {
            if ($document) try { $document->Close(false); } catch (\Throwable $e) {}
            if ($word) try { $word->Quit(); } catch (\Throwable $e) {}
        }
    }

    private function convertWithPowerShellWord(string $input, string $output, string $directory): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') return false;

        $script = $directory.'/convert-resume.ps1';
        File::put($script, <<<'POWERSHELL'
param(
    [Parameter(Mandatory = $true)][string]$InputPath,
    [Parameter(Mandatory = $true)][string]$OutputPath
)

$ErrorActionPreference = 'Stop'
$word = $null
$document = $null

try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($InputPath, $false, $true)
    $document.ExportAsFixedFormat($OutputPath, 17)
} finally {
    if ($null -ne $document) {
        $document.Close($false)
        [void][Runtime.InteropServices.Marshal]::FinalReleaseComObject($document)
    }
    if ($null -ne $word) {
        $word.Quit()
        [void][Runtime.InteropServices.Marshal]::FinalReleaseComObject($word)
    }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
POWERSHELL);

        try {
            $process = new Process([
                'powershell.exe',
                '-NoProfile',
                '-NonInteractive',
                '-ExecutionPolicy', 'Bypass',
                '-File', $script,
                str_replace('/', '\\', $input),
                str_replace('/', '\\', $output),
            ]);
            $process->setTimeout(90);
            $process->run();
            clearstatcache(true, $output);

            if (!$process->isSuccessful()) {
                report(new RuntimeException('PowerShell Word conversion failed: '.$process->getErrorOutput()));
            }

            return $process->isSuccessful() && is_file($output) && filesize($output) > 0;
        } catch (\Throwable $e) {
            report($e);
            return false;
        } finally {
            File::delete($script);
        }
    }
}
