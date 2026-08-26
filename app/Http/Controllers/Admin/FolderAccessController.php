<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\FolderAccess;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FolderAccessController extends Controller
{
    public function edit(string $type, int $id)
    {
        try {
            abort_if(!auth()->user()->can('manage users_roles'), 403);

            $target = $this->resolveTarget($type, $id);
            abort_if(!$target, 404);

            $rootFolders = Folder::whereNull('parent_item_id')->where('type', 'folder')->get();
            $this->loadChildFoldersRecursive($rootFolders);

            $grantedFolderIds = $target->folderAccesses()->pluck('folder_id')->map(fn ($id) => (int) $id)->all();

            return view('admin.folder_access.edit', compact(
                'type',
                'id',
                'target',
                'rootFolders',
                'grantedFolderIds'
            ));
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('FolderAccessController::edit failed: ' . $e->getMessage());
            return null;
        }
    }

    public function update(Request $request, string $type, int $id)
    {
        try {
            abort_if(!auth()->user()->can('manage users_roles'), 403);

            $target = $this->resolveTarget($type, $id);
            abort_if(!$target, 404);

            $folderIds = array_filter((array) $request->input('folder_ids', []));

            $target->folderAccesses()->delete();

            foreach ($folderIds as $folderId) {
                FolderAccess::create([
                    'folder_id'       => (int) $folderId,
                    'accessible_id'   => $target->id,
                    'accessible_type' => get_class($target),
                ]);
            }

            return redirect()->back()->with('success', 'Folder access updated successfully');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('FolderAccessController::update failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Modal fragment — pick folders once to grant to every selected user at
     * once (loaded via AJAX into the Users page's existing modal).
     */
    public function bulkEditUsers(Request $request)
    {
        try {
            abort_if(!auth()->user()->can('manage users_roles'), 403);

            $userIds = array_filter((array) explode(',', (string) $request->query('user_ids', '')));
            abort_if(empty($userIds), 422, 'No users selected.');

            $users = User::whereIn('id', $userIds)->get();
            abort_if($users->isEmpty(), 404);

            $rootFolders = Folder::whereNull('parent_item_id')->where('type', 'folder')->get();
            $this->loadChildFoldersRecursive($rootFolders);

            // Starts fully unchecked — the selected users may each already
            // have different grants, so this replaces all of theirs with
            // whatever gets picked here rather than trying to merge/display
            // a mixed pre-checked state.
            $grantedFolderIds = [];

            return view('admin.folder_access._bulk_users_tree', compact(
                'users',
                'rootFolders',
                'grantedFolderIds'
            ));
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('FolderAccessController::bulkEditUsers failed: ' . $e->getMessage());
            return null;
        }
    }

    public function bulkUpdateUsers(Request $request)
    {
        try {
            if (!auth()->user()->can('manage users_roles')) {
                return response()->json(['status' => 'error', 'message' => 'Permission denied.'], 403);
            }

            $userIds = array_filter((array) $request->input('user_ids', []));
            if (empty($userIds)) {
                return response()->json(['status' => 'error', 'message' => 'No users selected.'], 422);
            }

            $folderIds = array_filter((array) $request->input('folder_ids', []));
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $user->folderAccesses()->delete();

                foreach ($folderIds as $folderId) {
                    FolderAccess::create([
                        'folder_id'       => (int) $folderId,
                        'accessible_id'   => $user->id,
                        'accessible_type' => User::class,
                    ]);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Folder access updated for ' . $users->count() . ' user' . ($users->count() === 1 ? '' : 's') . '.',
            ]);
        } catch (\Exception $e) {
            Log::error('FolderAccessController::bulkUpdateUsers failed: ' . $e->getMessage());
            return null;
        }
    }

    private function resolveTarget(string $type, int $id)
    {
        try {
            return match ($type) {
                'user'  => User::find($id),
                'group' => Group::find($id),
                default => null,
            };
        } catch (\Exception $e) {
            Log::error('FolderAccessController::resolveTarget failed: ' . $e->getMessage());
            return null;
        }
    }

    private function loadChildFoldersRecursive(Collection $nodes): void
    {
        try {
            if ($nodes->isEmpty()) {
                return;
            }

            // Files are included as leaf, non-cascading nodes too (see
            // _tree.blade.php) so admins can grant access file-by-file, not
            // just per folder. Folders sort before files for readability.
            $nodes->load(['children' => fn ($q) => $q
                ->orderByRaw("CASE WHEN type = 'folder' THEN 0 ELSE 1 END")
                ->orderBy('name'),
            ]);

            foreach ($nodes as $node) {
                if ($node->children->isNotEmpty()) {
                    $this->loadChildFoldersRecursive($node->children);
                }
            }
        } catch (\Exception $e) {
            Log::error('FolderAccessController::loadChildFoldersRecursive failed: ' . $e->getMessage());
        }
    }
}
