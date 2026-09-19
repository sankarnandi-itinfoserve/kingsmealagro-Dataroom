<?php

namespace App\Services;

use App\Models\Folder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Extracts an uploaded zip and recreates its folder/file tree as Folder rows
 * nested under a given parent — same parent_item_id relation shape, and same
 * physical storage convention (Folder::localDiskPath(), the flat
 * project_folders/{id}.{ext} layout on the "public" disk) that manual folder
 * creation and the drag-and-drop file uploader already use.
 *
 * Shared by ProjectController (importing into a brand-new project root) and
 * FolderController (importing into any existing folder/sub-folder).
 */
class ZipFolderImporter
{
    public static function importInto(UploadedFile $zipFile, int $parentId, ?int $createdBy = null): void
    {
        set_time_limit(0);

        $createdBy = $createdBy ?? auth()->id();
        $extractPath = storage_path('app/tmp_zip_import_' . uniqid());

        $zip = new ZipArchive();
        if ($zip->open($zipFile->getRealPath()) !== true) {
            throw new \RuntimeException('Could not open the uploaded zip file.');
        }

        File::ensureDirectoryExists($extractPath);
        $zip->extractTo($extractPath);
        $zip->close();

        try {
            static::importExtractedDirectory(static::resolveImportRoot($extractPath), $parentId, $createdBy);
        } finally {
            File::deleteDirectory($extractPath);
        }
    }

    /**
     * Most zip tools wrap a zipped folder's contents in a single top-level
     * directory (zipping a folder named "test" produces test.zip containing
     * just test/...). If extraction produced exactly one real entry and it's
     * a directory, import its contents directly instead of nesting an extra
     * — often duplicate-named — wrapper folder for it.
     */
    protected static function resolveImportRoot(string $extractPath): string
    {
        $entries = static::listRealEntries($extractPath);

        if (count($entries) === 1 && is_dir($extractPath . DIRECTORY_SEPARATOR . $entries[0])) {
            return $extractPath . DIRECTORY_SEPARATOR . $entries[0];
        }

        return $extractPath;
    }

    /**
     * Directory entries with '.', '..', and common zip/OS junk filtered out.
     */
    protected static function listRealEntries(string $path): array
    {
        $entries = scandir($path);
        if ($entries === false) {
            return [];
        }

        return array_values(array_filter($entries, function ($entry) {
            return $entry !== '.' && $entry !== '..'
                && $entry !== '__MACOSX' && $entry !== '.DS_Store' && $entry !== 'Thumbs.db'
                && !str_starts_with($entry, '.');
        }));
    }

    protected static function importExtractedDirectory(string $path, int $parentId, ?int $createdBy): void
    {
        $entries = static::listRealEntries($path);
        natcasesort($entries);

        foreach ($entries as $entry) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($fullPath)) {
                $childFolder = Folder::create([
                    'name'           => $entry,
                    'type'           => 'folder',
                    'parent_item_id' => $parentId,
                    'created_by'     => $createdBy,
                ]);

                static::importExtractedDirectory($fullPath, $childFolder->id, $createdBy);
            } else {
                $childFile = Folder::create([
                    'name'           => $entry,
                    'type'           => 'file',
                    'parent_item_id' => $parentId,
                    'size'           => filesize($fullPath) ?: 0,
                    'created_by'     => $createdBy,
                ]);

                $diskPath = $childFile->localDiskPath();
                if ($diskPath) {
                    Storage::disk('public')->put($diskPath, file_get_contents($fullPath));
                }
            }
        }
    }
}
