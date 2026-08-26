<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectTemplate;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{
    public function index()
    {
        try {
            $templates = ProjectTemplate::with('creator')->latest()->get();

            // Root-level folders: parent_item_id is a self-referential FK to folders.id.
            $folders = \App\Models\Folder::whereNull('parent_item_id')->orderBy('name')->get();

            return view('admin.templates.index', compact('templates', 'folders'));
        } catch (\Exception $e) {
            Log::error('TemplateController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'             => 'required|string|max:255',
                'description'      => 'nullable|string',
                'source_folder_id' => 'nullable|integer|exists:folders,id',
            ]);

            $folderStructure = null;

            if ($request->filled('source_folder_id')) {
                $sourceFolder = Folder::find($request->source_folder_id);

                if ($sourceFolder) {
                    $folderStructure = $this->buildStructure($sourceFolder->childrenRecursive);
                }
            }

            ProjectTemplate::create([
                'name'             => $request->name,
                'description'      => $request->description,
                'folder_structure' => $folderStructure,
                'source_folder_id' => $request->source_folder_id,
                'created_by'       => auth()->id(),
            ]);

            return back()->with('success', 'Template saved.');
        } catch (\Exception $e) {
            Log::error('TemplateController::store failed: ' . $e->getMessage());
            return null;
        }
    }

    public function destroy($id)
    {
        try {
            $template = ProjectTemplate::findOrFail($id);
            $template->delete();

            return back()->with('success', 'Template deleted.');
        } catch (\Exception $e) {
            Log::error('TemplateController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }

    private function buildStructure($folders): ?array
    {
        try {
            $structure = [];

            foreach ($folders as $folder) {
                $structure[] = [
                    'name'     => $folder->name,
                    'children' => $this->buildStructure($folder->childrenRecursive),
                ];
            }

            return $structure;
        } catch (\Exception $e) {
            Log::error('TemplateController::buildStructure failed: ' . $e->getMessage());
            return null;
        }
    }
}
