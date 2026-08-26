<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        try {
            abort_if(!auth()->user()->can('view users_roles'), 403);
            $roles = Role::latest()->get();

            return view('admin.roles.index', compact('roles'));
        } catch (\Exception $e) {
            Log::error('RoleController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(Request $request)
    {
        try {
        $user = auth()->user();
        if (!$user->can('manage users_roles')) {
            return redirect()
                ->route('roles.index')->with('error', 'Permission denied. Please contact the administrator.');
        }
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);
        // Assign permissions
        $role->givePermissionTo([
            'view dashboard',
        ]);

        return redirect()->back()
            ->with('success', 'Role Created Successfully');
        } catch (\Exception $e) {
            Log::error('RoleController::store failed: ' . $e->getMessage());
            return null;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (!$user->can('manage users_roles')) {
                return redirect()
                    ->route('roles.index')->with('error', 'Permission denied. Please contact the administrator.');
            }
            $request->validate([
                'name' => 'required|unique:roles,name,' . $id,
            ]);

            $role = Role::findOrFail($id);

            $role->update([
                'name' => $request->name,
            ]);

            return redirect()->back()
                ->with('success', 'Role Updated Successfully');
        } catch (\Exception $e) {
            Log::error('RoleController::update failed: ' . $e->getMessage());
            return null;
        }
    }
    public function setDefault(Request $request, $id)
    {
        try {
        $user = auth()->user();
        if (!$user->can('manage users_roles')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied. Please contact the administrator.'
            ]);
        }

        $role = Role::findOrFail($id);

        if (in_array($role->name, ['super-admin', 'admin'])) {
            return response()->json([
                'status' => false,
                'message' => 'Super Admin and Admin cannot be set as the default role.'
            ]);
        }

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            // Only one role can be default at a time.
            Role::where('id', '!=', $role->id)->update(['is_default' => false]);
        }

        $role->update(['is_default' => $isDefault]);

        return response()->json([
            'status' => true,
            'message' => $isDefault ? 'Default role updated successfully' : 'Default role unset',
        ]);
        } catch (\Exception $e) {
            Log::error('RoleController::setDefault failed: ' . $e->getMessage());
            return null;
        }
    }

    public function destroy($id)
    {
        try {
        $user = auth()->user();

        if (!$user->can('manage users_roles')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied. Please contact the administrator.'
            ]);
        }

        $role = Role::findOrFail($id);

        if ($role->name == 'super-admin') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete Super Admin'
            ]);
        }

        $role->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role Deleted Successfully'
        ]);
        } catch (\Exception $e) {
            Log::error('RoleController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }

    /* public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->can('manage users_roles')) {
            return redirect()
                ->route('roles.index')->with('error', 'Permission denied. Please contact the administrator.');
            
        }
        
        $role = Role::findOrFail($id);

        if ($role->name == 'super-admin') {

            return redirect()->back()
                ->with('error', 'Cannot Delete Super Admin');
        }

        $role->delete();

        return redirect()->back()
            ->with('success', 'Role Deleted Successfully');
    }*/

    public function permissions($id)
    {
        try {

        abort_if(
            !auth()->user()->can('manage roles_permissions') &&
                !auth()->user()->can('manage users_roles') &&
                !auth()->user()->hasRole('super-admin'),
            403
        );
        $role = Role::findOrFail($id);

        $permissions = Permission::all()
            ->groupBy(function ($permission) {

                return explode(' ', $permission->name)[1];
            });

        return view(
            'admin.roles.permissions',
            compact('role', 'permissions')
        );
        } catch (\Exception $e) {
            Log::error('RoleController::permissions failed: ' . $e->getMessage());
            return null;
        }
    }


    public function permissionsUpdate(Request $request, $id)
    {
        try {
        // dd('asdas');
        $user = auth()->user();
        if (!($user->can('manage users_roles') || $user->can('manage roles_permissions') || $user->hasRole('super-admin'))) {
            return redirect()
                ->route('roles.index')->with('error', 'Permission denied. Please contact the administrator.');
        }




        $role = Role::findOrFail($id);

        // Get permissions from request (or empty array)
        $permissions = $request->permissions ?? [];

        // Always include these permissions
        $defaultPermissions = [1];

        // "Invite Users" is no longer editable from this form, so preserve
        // whatever the role already has for it instead of letting the full
        // syncPermissions() below silently strip it on save.
        $preservedInvitePermissions = $role->permissions()
            ->whereIn('name', ['view invite_users', 'manage invite_users'])
            ->pluck('id')
            ->toArray();

        // Merge both arrays
        $permissions = array_merge($permissions, $defaultPermissions, $preservedInvitePermissions);

        // Remove duplicates
        $permissions = array_unique($permissions);

        // Sync permissions
        $role->syncPermissions($permissions);

        return redirect()
            ->back()
            ->with('success', 'Permissions Updated Successfully');
        } catch (\Exception $e) {
            Log::error('RoleController::permissionsUpdate failed: ' . $e->getMessage());
            return null;
        }
    }
}
