<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    public function index()
    {
        try {
            abort_if(!auth()->user()->can('view groups'), 403);

            $groups = Group::withTrashed()->withCount('users')->with('creator')->latest()->get();
            $users  = User::whereNull('deleted_at')->orderBy('fname')->get();

            return view('admin.groups.index', compact('groups', 'users'));
        } catch (\Exception $e) {
            Log::error('GroupController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('manage groups')) {
                return redirect()
                    ->route('groups.index')->with('error', 'Permission denied. Please contact the administrator.');
            }

            $request->validate([
                'name'        => 'required|string|max:255',
                'user_ids'    => 'nullable|array',
                'user_ids.*'  => 'exists:users,id',
            ]);

            $group = Group::create([
                'name'        => $request->name,
                'created_by'  => auth()->id(),
            ]);

            $group->users()->sync($request->input('user_ids', []));

            return redirect()->back()
                ->with('success', 'Group Created Successfully');
        } catch (\Exception $e) {
            Log::error('GroupController::store failed: ' . $e->getMessage());
            return null;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('manage groups')) {
                return redirect()
                    ->route('groups.index')->with('error', 'Permission denied. Please contact the administrator.');
            }

            $request->validate([
                'name'        => 'required|string|max:255',
                'user_ids'    => 'nullable|array',
                'user_ids.*'  => 'exists:users,id',
            ]);

            $group = Group::findOrFail($id);

            $group->update([
                'name' => $request->name,
            ]);

            $group->users()->sync($request->input('user_ids', []));

            return redirect()->back()
                ->with('success', 'Group Updated Successfully');
        } catch (\Exception $e) {
            Log::error('GroupController::update failed: ' . $e->getMessage());
            return null;
        }
    }

    public function destroy($id)
    {
        try {
            if (!auth()->user()->can('manage groups')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Permission denied. Please contact the administrator.'
                ]);
            }

            $group = Group::findOrFail($id);
            $group->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Group Deleted Successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('GroupController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }

    public function restore($id)
    {
        try {
            if (!auth()->user()->can('manage groups')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Permission denied. Please contact the administrator.'
                ]);
            }

            $group = Group::withTrashed()->findOrFail($id);
            $group->restore();

            return response()->json([
                'status'  => true,
                'message' => 'Group Restored Successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('GroupController::restore failed: ' . $e->getMessage());
            return null;
        }
    }
}
