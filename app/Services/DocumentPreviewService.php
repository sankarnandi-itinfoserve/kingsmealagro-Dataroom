<?php

namespace App\Services;

use App\Models\Folder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

/**
 * Turns a file into something the browser can view natively as a PDF — no
 * document server involved. Actual PDFs are served as-is; office documents
 * (docx/xlsx/pptx/doc/xls/ppt) are converted once via LibreOffice headless
 * and the result is cached on the public disk, keyed by the file's own id.
 */
class DocumentPreviewService
{
    private const CONVERTIBLE_EXTENSIONS = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

    /**
     * Public-disk-relative path to a viewable PDF for this file, or null if
     * this file type can't be previewed at all.
     */
    public static function previewPath(Folder $file): ?string
    {
        try {
            $ext = strtolower(pathinfo($file->name ?? '', PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                $path = $file->localDiskPath();
                return ($path && Storage::disk('public')->exists($path)) ? $path : null;
            }

            if (!in_array($ext, self::CONVERTIBLE_EXTENSIONS, true)) {
                return null;
            }

            return self::convertToPdf($file);
        } catch (\Exception $e) {
            Log::error('DocumentPreviewService::previewPath failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function convertToPdf(Folder $file): ?string
    {
        try {
            $sourceRelativePath = $file->localDiskPath();
            if (!$sourceRelativePath || !Storage::disk('public')->exists($sourceRelativePath)) {
                return null;
            }

            $cachedRelativePath = 'preview_cache/' . $file->id . '.pdf';
            $cachedAbsolutePath = Storage::disk('public')->path($cachedRelativePath);
            $sourceAbsolutePath = Storage::disk('public')->path($sourceRelativePath);

            // Reuse the cached PDF as long as it's not older than the source
            // (a re-upload/OnlyOffice-free "update file" bumps the source's
            // mtime, so this naturally invalidates on content changes).
            if (file_exists($cachedAbsolutePath) && filemtime($cachedAbsolutePath) >= filemtime($sourceAbsolutePath)) {
                return $cachedRelativePath;
            }

            $sofficeBin = self::sofficeBinary();
            if (!$sofficeBin) {
                Log::error('DocumentPreviewService: soffice binary not found — is LibreOffice installed?');
                return null;
            }

            $outDir = dirname($cachedAbsolutePath);
            if (!is_dir($outDir)) {
                mkdir($outDir, 0755, true);
            }

            // A unique profile dir per conversion avoids "soffice already
            // running" lock conflicts when two previews happen close together.
            $profileDir = storage_path('app/tmp_lo_profile_' . uniqid());

            $result = Process::timeout(60)->run([
                $sofficeBin,
                '--headless',
                '--norestore',
                '--convert-to', 'pdf',
                '--outdir', $outDir,
                '-env:UserInstallation=file:///' . str_replace('\\', '/', $profileDir),
                $sourceAbsolutePath,
            ]);

            if (!$result->successful()) {
                Log::error('DocumentPreviewService: soffice conversion failed', [
                    'file_id' => $file->id,
                    'output'  => $result->errorOutput(),
                ]);
            }

            // soffice names the output after the source filename, not our
            // desired {id}.pdf — move it into place.
            $producedPath = $outDir . DIRECTORY_SEPARATOR . pathinfo($sourceAbsolutePath, PATHINFO_FILENAME) . '.pdf';
            if (file_exists($producedPath)) {
                rename($producedPath, $cachedAbsolutePath);
            }

            self::cleanupProfileDir($profileDir);

            return file_exists($cachedAbsolutePath) ? $cachedRelativePath : null;
        } catch (\Exception $e) {
            Log::error('DocumentPreviewService::convertToPdf failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function sofficeBinary(): ?string
    {
        try {
            $isWindows = PHP_OS_FAMILY === 'Windows';

            // Ask the OS to resolve it via PATH first — works regardless of
            // which distro/package manager put it there (apt, dnf, snap,
            // flatpak's wrapper, a custom /opt install, etc.), so this alone
            // covers the vast majority of real Linux servers without needing
            // to guess every possible install location.
            $result = Process::run(($isWindows ? 'where' : 'which') . ' soffice');
            if ($result->successful()) {
                $resolved = trim(strtok($result->output(), "\n"));
                if ($resolved !== '' && file_exists($resolved)) {
                    return $resolved;
                }
            }

            // Fall back to the well-known default install locations in case
            // PATH doesn't include it (common on Windows, and on some Linux
            // setups where soffice is only reachable via a full path).
            $candidates = $isWindows ? [
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            ] : [
                '/usr/bin/soffice',
                '/usr/bin/libreoffice',
                '/opt/libreoffice/program/soffice',
                '/snap/bin/libreoffice',
            ];

            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    return $candidate;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('DocumentPreviewService::sofficeBinary failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function cleanupProfileDir(string $dir): void
    {
        try {
            if (!is_dir($dir)) {
                return;
            }

            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($items as $item) {
                $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
            }

            @rmdir($dir);
        } catch (\Exception $e) {
            Log::error('DocumentPreviewService::cleanupProfileDir failed: ' . $e->getMessage());
        }
    }
}
