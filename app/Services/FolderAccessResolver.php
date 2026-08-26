<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\FolderAccess;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Simple view+download folder access — a folder or file is only accessible
 * if it, specifically, was explicitly granted to the user or one of their
 * groups. Nothing is inherited from a parent or ancestor: each item needs
 * its own explicit grant. super-admin and admin always have full access.
 */
class FolderAccessResolver
{
    /**
     * Folder ids explicitly granted to this user, directly or via any group
     * they belong to.
     */
    public static function grantedFolderIds(User $user): array
    {
        try {
            $groupIds = $user->groups()->pluck('groups.id')->all();

            return FolderAccess::where(function ($q) use ($user, $groupIds) {
                $q->where('accessible_type', User::class)->where('accessible_id', $user->id);

                if (!empty($groupIds)) {
                    $q->orWhere(function ($q2) use ($groupIds) {
                        $q2->where('accessible_type', Group::class)->whereIn('accessible_id', $groupIds);
                    });
                }
            })->pluck('folder_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        } catch (\Exception $e) {
            Log::error('FolderAccessResolver::grantedFolderIds failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Does the user have view+download access to this folder/file. Both
     * folders and files need their own explicit grant — nothing is
     * inherited from a parent, so unchecking one specific file while its
     * folder stays granted actually excludes just that file.
     */
    public static function userCanAccessFolder(User $user, ?int $folderId): bool
    {
        try {
            if (!$folderId) {
                return false;
            }

            if ($user->hasAnyRole(['super-admin', 'admin'])) {
                return true;
            }

            $granted = self::grantedFolderIds($user);
            if (empty($granted)) {
                return false;
            }

            return in_array($folderId, $granted, true);
        } catch (\Exception $e) {
            Log::error('FolderAccessResolver::userCanAccessFolder failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * The folders to show as top-level "roots" in this user's Drive tree.
     * super-admin/admin see every real root folder, unrestricted. Everyone
     * else sees their explicitly granted folders standing in as their own
     * roots — regardless of where those folders actually sit in the real
     * tree, since they may not have access to the real ancestor chain.
     *
     * A granted folder is excluded from this root list if one of its real
     * ancestors is also granted — it'll be reached by descending from that
     * ancestor instead (see FolderController::loadChildrenRecursive), so
     * listing it again here would show it twice.
     */
    public static function visibleRootFolders(User $user): Collection
    {
        try {
            if ($user->hasAnyRole(['super-admin', 'admin'])) {
                return Folder::whereNull('parent_item_id')->get();
            }

            $granted = self::grantedFolderIds($user);
            if (empty($granted)) {
                return collect();
            }

            $grantedSet = array_flip($granted);
            $folders = Folder::whereIn('id', $granted)->get()->keyBy('id');

            return $folders->filter(function ($folder) use ($grantedSet, $folders) {
                $current = $folder;
                $visited = [];

                while ($current && $current->parent_item_id) {
                    if (in_array($current->parent_item_id, $visited, true)) {
                        break;
                    }
                    $visited[] = $current->parent_item_id;

                    if (isset($grantedSet[$current->parent_item_id])) {
                        return false;
                    }

                    $current = $folders->get($current->parent_item_id) ?? Folder::find($current->parent_item_id);
                }

                return true;
            })->values();
        } catch (\Exception $e) {
            Log::error('FolderAccessResolver::visibleRootFolders failed: ' . $e->getMessage());
            return collect();
        }
    }
}
