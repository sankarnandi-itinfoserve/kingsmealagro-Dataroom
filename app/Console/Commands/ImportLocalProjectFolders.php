<?php

namespace App\Console\Commands;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Console\Command;

class ImportLocalProjectFolders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'folders:import-local
        {--fresh : Delete all existing folders before importing}
        {--only=* : Only import these specific top-level entries under project_folders (by exact name), instead of the whole directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recursively import storage/app/public/project_folders into the folders table, building parent_item_id relationships';

    public function handle(): int
    {
        $root = storage_path('app/public/project_folders');

        if (!is_dir($root)) {
            $this->error("Directory not found: {$root}");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            Folder::withTrashed()->forceDelete();
            $this->info('Cleared existing folders table.');
        }

        $createdBy = User::whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))->value('id')
            ?? User::orderBy('id')->value('id');

        $folders = 0;
        $files = 0;

        $only = $this->option('only');
        if (!empty($only)) {
            $this->info('Importing only: ' . implode(', ', $only));
        }

        $this->importDirectory($root, null, $createdBy, $folders, $files, $only ?: null);

        $this->info("Imported {$folders} folders and {$files} files.");

        return self::SUCCESS;
    }

    protected function importDirectory(string $path, ?int $parentId, ?int $createdBy, int &$folders, int &$files, ?array $onlyNames = null): void
    {
        $entries = scandir($path);
        if ($entries === false) {
            return;
        }

        natcasesort($entries);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            // Only applies at this exact call — used to restrict the very
            // first (root) level to specific named entries without having
            // to touch anything already imported elsewhere in the tree.
            if ($onlyNames !== null && !in_array($entry, $onlyNames, true)) {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($fullPath)) {
                $folder = Folder::create([
                    'name' => $entry,
                    'type' => 'folder',
                    'parent_item_id' => $parentId,
                    'created_by' => $createdBy,
                ]);

                $folders++;

                $this->importDirectory($fullPath, $folder->id, $createdBy, $folders, $files);
            } else {
                Folder::create([
                    'name' => $entry,
                    'type' => 'file',
                    'parent_item_id' => $parentId,
                    'created_by' => $createdBy,
                    'size' => filesize($fullPath) ?: 0,
                ]);

                $files++;
            }
        }
    }
}
