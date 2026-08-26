<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_template_items', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('permission_template_id')
                ->constrained('folders')->nullOnDelete();
        });

        $this->backfillFolderIds();
    }

    public function down(): void
    {
        Schema::table('permission_template_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
        });
    }

    /**
     * Existing items were created before folder_id existed and only have a
     * folder_name string. Resolve each one against the same is_template
     * source-folder logic PermissionTemplateController::distinctFirstLevelFolderNames()
     * uses, so already-built templates keep working with the new relation
     * instead of silently losing their folder link.
     */
    private function backfillFolderIds(): void
    {
        $folders = DB::table('folders')
            ->where('type', 'folder')
            ->get(['id', 'name', 'parent_id', 'parent_item_id', 'item_id', 'is_template']);

        $byId = $folders->keyBy('id');
        $byItemId = $folders->keyBy('item_id');

        $resolveParent = function ($folder) use ($byId, $byItemId) {
            if ($folder->parent_id && $byId->has($folder->parent_id)) {
                return $byId->get($folder->parent_id);
            }
            if ($folder->parent_item_id && $byItemId->has($folder->parent_item_id)) {
                return $byItemId->get($folder->parent_item_id);
            }
            return null;
        };

        $idByName = [];
        foreach ($folders as $folder) {
            $parent = $resolveParent($folder);
            if ($parent && $parent->is_template) {
                $idByName[strtolower(trim($folder->name))] = $folder->id;
            }
        }

        foreach (DB::table('permission_template_items')->whereNull('folder_id')->get(['id', 'folder_name']) as $item) {
            $folderId = $idByName[strtolower(trim($item->folder_name))] ?? null;
            if ($folderId) {
                DB::table('permission_template_items')->where('id', $item->id)->update(['folder_id' => $folderId]);
            }
        }
    }
};
