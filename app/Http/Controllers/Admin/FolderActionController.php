<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use App\Models\FolderShare;
use Illuminate\Support\Facades\Log;

class FolderActionController extends Controller
{
    public function rename(Request $request, $id)
    {
        try {
            $folder = Folder::findOrFail($id);

            // ✅ Access check
            if (
                $folder->creator_id !== auth()->id() &&
                !$folder->shares()->where('email', auth()->user()->email)->exists()
            ) {
                abort(403);
            }

            $request->validate([
                'name' => 'required'
            ]);

            Folder::findOrFail($id)->update([
                'name' => $request->name
            ]);

            return back()->with('success', 'Folder renamed');
        } catch (\Exception $e) {
            Log::error('FolderActionController::rename failed: ' . $e->getMessage());
            return null;
        }
    }
    public function move(Request $request, $id)
    {
        try {
            // parent_item_id is a self-referential FK to folders.id.
            Folder::findOrFail($id)->update([
                'parent_item_id' => $request->parent_id
            ]);


            return back()->with('success', 'Folder moved');
        } catch (\Exception $e) {
            Log::error('FolderActionController::move failed: ' . $e->getMessage());
            return null;
        }
    }
    public function delete($id)
    {
        try {
            $folder = Folder::with('children')->findOrFail($id);

            $folder->delete(); // cascade handles children if FK set

            return back()->with('success', 'Folder deleted');
        } catch (\Exception $e) {
            Log::error('FolderActionController::delete failed: ' . $e->getMessage());
            return null;
        }
    }
    public function copy($id)
    {
        try {
            $folder = Folder::with('children')->findOrFail($id);

            $new = $folder->replicate();
            $new->name = $folder->name . ' (Copy)';
            $new->push();

            return back()->with('success', 'Folder copied');
        } catch (\Exception $e) {
            Log::error('FolderActionController::copy failed: ' . $e->getMessage());
            return null;
        }
    }
    public function downloadZip($id)
    {
        try {
            $folder = Folder::findOrFail($id);

            // ✅ Access check
            if (
                $folder->creator_id !== auth()->id() &&
                !$folder->shares()->where('email', auth()->user()->email)->exists()
            ) {
                abort(403);
            }
            $folder = Folder::with('children')->findOrFail($id);

            $zip = new ZipArchive;
            $fileName = "folder_$id.zip";
            $zipPath = storage_path($fileName);

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {

                $this->addFolderToZip($folder, $zip);

                $zip->close();
            }

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('FolderActionController::downloadZip failed: ' . $e->getMessage());
            return null;
        }
    }
    private function addFolderToZip($folder, $zip, $path = '')
    {
        try {
            $folderPath = $path . $folder->name . '/';

            foreach ($folder->children as $child) {
                $this->addFolderToZip($child, $zip, $folderPath);
            }
        } catch (\Exception $e) {
            Log::error('FolderActionController::addFolderToZip failed: ' . $e->getMessage());
        }
    }

    public function downloadMultiple(Request $request)
    {
        try {

            $ids = explode(',', $request->ids);

            $folders = \App\Models\Folder::with('childrenRecursive')
                ->whereIn('id', $ids)
                ->get();

            if ($folders->isEmpty()) {
                return back()->with('error', 'No folders selected');
            }

            // ✅ Ensure directory exists
            Storage::makeDirectory('temp');

            $zipFileName = 'folders_' . time() . '.zip';
            $zipRelativePath = 'temp/' . $zipFileName;
            $zipFullPath = storage_path('app/' . $zipRelativePath);

            $zip = new ZipArchive;

            if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'Failed to create zip');
            }

            foreach ($folders as $folder) {
                $this->addFolderToZipNew($folder, $zip, $folder->name);
            }

            $zip->close();

            return response()->download($zipFullPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('FolderActionController::downloadMultiple failed: ' . $e->getMessage());
            return null;
        }
    }




    private function addFolderToZipNew($folder, $zip, $path)
    {
        try {
            $added = false;

            $zip->addEmptyDir($path);
            $added = true;

            // Add children recursively
            foreach ($folder->childrenRecursive as $child) {
                $childAdded = $this->addFolderToZip(
                    $child,
                    $zip,
                    $path . '/' . $child->name
                );

                if ($childAdded) $added = true;
            }

            return $added;
        } catch (\Exception $e) {
            Log::error('FolderActionController::addFolderToZipNew failed: ' . $e->getMessage());
            return null;
        }
    }
}
