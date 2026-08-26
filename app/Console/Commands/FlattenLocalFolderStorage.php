<?php

namespace App\Console\Commands;

use App\Models\Folder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * One-time migration of the originally-imported nested, name-based physical
 * layout under storage/app/public/project_folders into the flat, id-based
 * layout the app now uses (project_folders/{id}.{ext} — see
 * Folder::localDiskPath()). Safe to re-run: files already in place are
 * counted and skipped.
 */
class FlattenLocalFolderStorage extends Command
{
    protected $signature = 'folders:flatten-local-storage';

    protected $description = 'Move already-imported project files from their nested name-based paths to the flat id-based layout';

    public function handle(): int
    {
        $root = storage_path('app/public/project_folders');

        if (!is_dir($root)) {
            $this->error("Directory not found: {$root}");
            return self::FAILURE;
        }

        $moved = 0;
        $alreadyInPlace = 0;
        $missing = [];
        $failed = [];

        foreach (Folder::where('type', 'file')->get() as $folder) {
            $newRelative = $folder->localDiskPath();
            if (!$newRelative) {
                continue;
            }

            $newPath = storage_path('app/public/' . $newRelative);
            $oldPath = $root . DIRECTORY_SEPARATOR . $this->originalRelativePath($folder);

            if (is_file($newPath)) {
                $alreadyInPlace++;
                $this->line("  = already in place [{$folder->id}] {$folder->name}");
                continue;
            }

            if (!is_file($oldPath)) {
                $missing[] = "[{$folder->id}] {$folder->name} — expected at {$oldPath}";
                $this->warn("  ! not found  [{$folder->id}] {$oldPath}");
                continue;
            }

            try {
                File::ensureDirectoryExists(dirname($newPath));
                File::move($oldPath, $newPath);
                $moved++;
                $this->line("  + moved      [{$folder->id}] {$folder->name}");
            } catch (\Throwable $e) {
                $failed[] = "[{$folder->id}] {$folder->name} — " . $e->getMessage();
                $this->error("  x failed     [{$folder->id}] {$folder->name}: " . $e->getMessage());
            }
        }

        $removedDirs = $this->removeEmptyDirectories($root, true);

        $this->newLine();
        $this->info('── Summary ─────────────────────────────');
        $this->info("Files moved:                {$moved}");
        $this->info("Files already in place:     {$alreadyInPlace}");
        $this->info('Files not found at expected original location: ' . count($missing));
        $this->info('Files that failed to move:  ' . count($failed));
        $this->info("Empty directories removed:  {$removedDirs}");

        if ($missing) {
            $this->newLine();
            $this->warn('Not found at expected original location:');
            foreach ($missing as $line) {
                $this->warn('  - ' . $line);
            }
        }

        if ($failed) {
            $this->newLine();
            $this->error('Failed to move:');
            foreach ($failed as $line) {
                $this->error('  - ' . $line);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Rebuild the row's ORIGINAL nested path (relative to project_folders) by
     * walking the parent_item_id chain up to the root and reversing it.
     */
    protected function originalRelativePath(Folder $folder): string
    {
        $parts   = [$folder->name];
        $visited = [$folder->id];
        $parentId = $folder->parent_item_id;

        while ($parentId) {
            if (in_array($parentId, $visited, true)) {
                break; // cycle guard
            }
            $visited[] = $parentId;

            $parent = Folder::find($parentId);
            if (!$parent) {
                break;
            }

            $parts[]  = $parent->name;
            $parentId = $parent->parent_item_id;
        }

        return implode(DIRECTORY_SEPARATOR, array_reverse($parts));
    }

    /**
     * Post-order walk: children first, then the directory itself if nothing
     * is left in it. The root ($isRoot) is descended into but never removed.
     */
    protected function removeEmptyDirectories(string $dir, bool $isRoot = false): int
    {
        $removed = 0;

        $entries = @scandir($dir);
        if ($entries === false) {
            return 0;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $removed += $this->removeEmptyDirectories($path);
            }
        }

        if (!$isRoot) {
            $after = @scandir($dir);
            if ($after !== false && count($after) === 2) {
                if (@rmdir($dir)) {
                    $removed++;
                    $this->line("  - rmdir      {$dir}");
                }
            }
        }

        return $removed;
    }
}
