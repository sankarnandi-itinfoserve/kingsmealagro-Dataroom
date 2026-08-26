<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteUserMail;
use App\Mail\WelcomeMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
        abort_if(!auth()->user()->can('view users'), 403);

        if ($request->ajax()) {

            $users = User::withTrashed();

            $userFilter = $request->input('user_filter', 'all');
            if ($userFilter === 'inactive') {
                $users->whereNotNull('deleted_at');
            }

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('avatar', function ($user) {
                    $viewUrl = url('/analytics/users/' . $user->id);

                    if ($user->avatar) {
                        $avatarHtml = '<img src="' . asset('storage/' . $user->avatar) . '" class="usr-avatar" width="34" height="34">';
                    } else {
                        $avatarHtml = '<i class="fa-solid fa-circle-user usr-avatar-default"></i>';
                    }

                    $lockedBadge = ($user->locked_until && now()->lt($user->locked_until))
                        ? '<span class="usr-locked-chip"><i class="fa fa-lock me-1"></i>Locked</span>'
                        : '';

                    $jobTitle = $user->job_title
                        ? '<div class="usr-job-title">' . e($user->job_title) . '</div>'
                        : '';

                    return '
                        <div class="d-flex align-items-center gap-2">
                            <a href="' . $viewUrl . '" target="_blank" rel="noopener" title="View analytics">' . $avatarHtml . '</a>
                            <div>
                                <div class="usr-name"><a href="' . $viewUrl . '" target="_blank" rel="noopener" class="usr-name-link">' . e($user->fname) . ' ' . e($user->lname) . '</a> ' . $lockedBadge . '</div>
                                ' . $jobTitle . '
                            </div>
                        </div>
                    ';
                })
                ->addColumn('role', function ($user) {
                    return $user->getRoleNames()->first();
                })
                ->addColumn('is_locked', function ($user) {
                    return $user->locked_until && now()->lt($user->locked_until);
                })
                ->addColumn('locked_until', function ($user) {
                    return $user->locked_until;
                })
                ->addColumn('status', function ($user) {
                    if ($user->deleted_at) {
                        return '<span class="usr-status-badge usr-status-inactive"><i class="fa-solid fa-circle-minus me-1"></i>Inactive</span>';
                    }
                    return '<span class="usr-status-badge usr-status-active"><i class="fa-solid fa-circle-check me-1"></i>Active</span>';
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $statuses = array_filter(explode('|', $keyword));
                    if (!empty($statuses)) {
                        $query->where(function ($q) use ($statuses) {
                            foreach ($statuses as $status) {
                                if ($status === 'active') {
                                    $q->orWhereNull('deleted_at');
                                } elseif ($status === 'inactive') {
                                    $q->orWhereNotNull('deleted_at');
                                }
                            }
                        });
                    }
                })
                ->filterColumn('avatar', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('fname', 'like', "%{$keyword}%")
                            ->orWhere('lname', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('avatar', function ($query, $direction) {
                    $query->orderBy('fname', $direction)->orderBy('lname', $direction);
                })
                ->filterColumn('role', function ($query, $keyword) {
                    $roles = array_filter(explode('|', $keyword));
                    if (!empty($roles)) {
                        $query->whereHas('roles', function ($q) use ($roles) {
                            $q->whereIn('name', $roles);
                        });
                    }
                })
                ->orderColumn('DT_RowIndex', function ($query, $direction) {
                    $query->orderBy('id', $direction);
                })
                ->rawColumns(['avatar', 'status', 'actions'])
                ->make(true);
        }
        $roles = Role::all();

        return view('admin.users.index', compact('roles'));
        } catch (\Exception $e) {
            Log::error('UserController::index failed: ' . $e->getMessage());
            return null;
        }
    }


    public function create()
    {
        try {
            abort_if(!auth()->user()->can('manage users'), 403);
            $roles = Role::all();
            return view('admin.users.create', compact('roles'));
        } catch (\Exception $e) {
            Log::error('UserController::create failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(Request $request)
    {

        $user = auth()->user();
        if (!$user->can('manage users')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission denied. Please contact the administrator.',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|exists:roles,name', // ✅ validate role
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            // ✅ Avatar Upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // ✅ Generate EMP ID safely
            $nextId = (User::max('id') ?? 0) + 1;

            $user = User::create([
                'fname' => $request->fname,
                'lname' => $request->lname,
                'displayName' => $request->fname . ' ' . $request->lname,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),

                'avatar' => $avatarPath,

                'active' => 1,
                'user_type' => 'user',

                // ✅ FIXED (removed extra space)
                'emp_id' => 'EMP' . str_pad($nextId, 3, '0', STR_PAD_LEFT),

                'replay_email' => $request->email,
                'role' => $request->role,
            ]);
            $user->assignRole([$request->role]);

            Mail::to($user->email)->send(new WelcomeMail($user, $request->password));

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            abort_if(!auth()->user()->can('view users'), 403);
            $user = User::withTrashed()->findOrFail($id);
            return view('admin.users.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('UserController::show failed: ' . $e->getMessage());
            return null;
        }
    }

    public function edit(User $user)
    {
        try {
            abort_if(!auth()->user()->can('manage users'), 403);
            $roles = Role::all();
            return view('admin.users.edit', compact('user', 'roles'));
        } catch (\Exception $e) {
            Log::error('UserController::edit failed: ' . $e->getMessage());
            return null;
        }
    }

    public function update(Request $request, User $user)
    {
        try {
        $authUser = auth()->user();
        if (!$authUser->can('manage users')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission denied. Please contact the administrator.',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->displayName = $request->fname . ' ' . $request->lname;
        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }
        $user->save();
        $this->applyRoleFromRequest($request, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully'
        ]);
        } catch (\Exception $e) {
            Log::error('UserController::update failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Applies a role change submitted from the Edit User modal — same
     * "can't reassign an admin/super-admin away from that role" guard the
     * old inline Role-column dropdown enforced client-side (disabled
     * attribute), now enforced server-side since the dropdown only exists
     * here. Silently no-ops if the field is absent (Create/other forms that
     * reuse update() logic don't send it) or the requester lacks
     * manage roles_permissions.
     */
    private function applyRoleFromRequest(Request $request, User $user): void
    {
        try {
            if (!$request->filled('role') || !auth()->user()->can('manage roles_permissions')) {
                return;
            }

            $currentRole = $user->getRoleNames()->first();
            if (in_array($currentRole, ['admin', 'super-admin'], true)) {
                return;
            }

            if (Role::where('name', $request->role)->exists()) {
                $user->syncRoles([$request->role]);
            }
        } catch (\Exception $e) {
            Log::error('UserController::applyRoleFromRequest failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $authUser = auth()->user();
            if (!$authUser->can('manage users')) {
                return response()->json(['status' => 'error', 'message' => 'Permission denied.'], 403);
            }

            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['status' => 'success', 'message' => 'User deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('UserController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            if (!auth()->user()->can('manage users')) {
                return response()->json(['status' => 'error', 'message' => 'Permission denied.'], 403);
            }

            $ids = array_filter((array) $request->input('ids', []));
            if (empty($ids)) {
                return response()->json(['status' => 'error', 'message' => 'No users selected.'], 422);
            }

            $count = User::whereIn('id', $ids)->get()->each->delete()->count();

            return response()->json([
                'status' => 'success',
                'message' => $count . ' user' . ($count === 1 ? '' : 's') . ' deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('UserController::bulkDestroy failed: ' . $e->getMessage());
            return null;
        }
    }

    public function restore($id)
    {
        try {
            if (!auth()->user()->can('manage users')) {
                return response()->json(['status' => 'error', 'message' => 'Permission denied.'], 403);
            }

            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            return response()->json(['status' => 'success', 'message' => 'User restored successfully.']);
        } catch (\Exception $e) {
            Log::error('UserController::restore failed: ' . $e->getMessage());
            return null;
        }
    }
    public function unlock($id)
    {
        try {
            $user = User::findOrFail($id);
            User::where('id', $user->id)->update([
                'failed_attempts' => 0,
                'locked_until' => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User unlocked successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('UserController::unlock failed: ' . $e->getMessage());
            return null;
        }
    }



    // 📌 Show invite form
    public function invititioncreate()
    {
        try {
            abort_if(!auth()->user()->can('view invite_users'), 403);
            return view('admin.invitations.create');
        } catch (\Exception $e) {
            Log::error('UserController::invititioncreate failed: ' . $e->getMessage());
            return null;
        }
    }
    // DataTable API
    public function invitationList()
    {
        try {
        $data = Invitation::latest();
        $authUser = auth()->user();

        return DataTables::of($data)
            ->addIndexColumn()

            // Email + Status Badge
            ->editColumn('email', function ($row) {

                if ($row->accepted) {
                    $badge = '<span class="badge bg-success ms-2">
                                <i class="fas fa-check-circle"></i>
                            </span>';
                } elseif ($row->expires_at && $row->expires_at->isPast()) {
                    $badge = '<span class="badge bg-danger ms-2">
                                <i class="fas fa-times-circle"></i>
                            </span>';
                } else {
                    $badge = '<span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-clock"></i>
                            </span>';
                }

                return '<div class="d-flex align-items-center">'
                    . $row->email . ' ' . $badge .
                    '</div>';
            })

            // Date format
            ->editColumn('expires_at', function ($row) {
                return $row->expires_at
                    ? $row->expires_at->format('m/d/Y H:i')
                    : '-';
            })

            // Actions
            ->addColumn('action', function ($row) use ($authUser) {

                $resendBtn = '';
                $deleteBtn = '';

                // Resend Button
                if ($row->accepted) {
                    $resendBtn = '<button class="inv-dt-btn inv-dt-btn--done" disabled title="Already accepted">
                                    <i class="fas fa-check"></i>
                                </button>';
                } elseif ($authUser->can('manage invite_users')) {
                    $resendBtn = '<button class="inv-dt-btn inv-dt-btn--resend resendBtn" data-id="' . $row->id . '" title="Resend invite">
                                    <i class="fas fa-paper-plane"></i>
                                </button>';
                }

                // Delete / Restore
                if ($authUser->can('manage invite_users')) {
                    if ($row->deleted_at) {
                        $deleteBtn = '<button class="inv-dt-btn inv-dt-btn--restore restoreBtn" data-id="' . $row->id . '" title="Restore">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>';
                    } else {
                        $deleteBtn = '<button class="inv-dt-btn inv-dt-btn--delete deleteBtn" data-id="' . $row->id . '" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                    }
                }
                return '<div class="d-flex gap-2">' . $resendBtn . $deleteBtn . '</div>';
            })

            ->rawColumns(['email', 'action'])
            ->make(true);
        } catch (\Exception $e) {
            Log::error('UserController::invitationList failed: ' . $e->getMessage());
            return null;
        }
    }
    public function resend($id)
    {
        $authUser = auth()->user();
        if (!$authUser->can('manage invite_users')) {
            return response()->json(['status' => 'error', 'message' => 'Permission denied. Please contact the administrator.']);
        }
        try {
            $invite = Invitation::findOrFail($id);
            $token = Str::random(64);

            // regenerate token & expiry
            $invite->token = $token;
            $invite->expires_at = now()->addDays(1);
            $invite->accepted = false;
            $invite->save();


            $link = route('invite.accept', $token);

            Mail::to($invite->email)->send(new InviteUserMail($link));


            return response()->json(['status' => 'success', 'message' => 'Invite resent successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to resend invite']);
        }
    }
    public function destroyinvite($id)
    {
        $authUser = auth()->user();
        if (!$authUser->can('manage invite_users')) {
            return response()->json(['status' => 'error', 'message' => 'Permission denied. Please contact the administrator.']);
        }
        try {
            Invitation::findOrFail($id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Invite deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete invite']);
        }
    }


    // 📌 Send invite
    public function invititionstore(Request $request)
    {
        try {
            $authUser = auth()->user();
            if (!$authUser->can('manage invite_users')) {
                return back()->with('error', 'Permission denied. Please contact the administrator.');
            }

            $request->validate([
                'email' => 'required|email|unique:users,email|unique:invitations,email'
            ]);

            $token = Str::random(64);

            $invitation = Invitation::create([
                'email' => $request->email,
                'token' => $token,
                'expires_at' => Carbon::now()->addDays(2),
            ]);
            $link = route('invite.accept', $token);

            Mail::to($request->email)->send(new InviteUserMail($link));

            return back()->with('success', 'Invitation sent successfully');
        } catch (\Exception $e) {
            Log::error('UserController::invititionstore failed: ' . $e->getMessage());
            return null;
        }
    }

    // 📌 Accept invite page
    public function invititionaccept($token)
    {
        try {
            $invitation = Invitation::where('token', $token)->firstOrFail();

            if ($invitation->expires_at && now()->gt($invitation->expires_at)) {
                abort(403, 'Invitation expired');
            }

            return view('admin.invitations.accept-invite', compact('invitation'));
        } catch (\Exception $e) {
            Log::error('UserController::invititionaccept failed: ' . $e->getMessage());
            return null;
        }
    }

    // 📌 Complete registration
    public function invititioncomplete(Request $request, $token)
    {
        try {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        // ✅ 1. CHECK IF ALREADY USED
        if ($invitation->accepted) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invitation already used.'
            ]);
        }

        // ✅ 2. CHECK EXPIRY
        if ($invitation->expires_at && now()->gt($invitation->expires_at)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invitation link has expired.'
            ]);
        }

        // ✅ Validation
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'password' => 'required|confirmed|min:6',
            'nda' => 'required'
        ]);

        // ✅ Create user
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'displayName' => $request->fname . ' ' . $request->lname,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'username' =>  $request->fname . '_' . $request->lname,

            // optional defaults
            'active' => 1,
            'user_type' => 'user',
            'emp_id' => 'EMP' . str_pad(User::max('id') + 1, 3, '0', STR_PAD_LEFT),
            'replay_email' => $invitation->email,
            'role' => 'user',
        ]);
        $user->assignRole(['user']);

        // ✅ Mark invitation accepted
        $invitation->update([
            'accepted' => true
        ]);

        // ✅ Auto login
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Account created successfully');
        } catch (\Exception $e) {
            Log::error('UserController::invititioncomplete failed: ' . $e->getMessage());
            return null;
        }
    }



    public function changeRole(Request $request, $id)
    {
        try {

        $authUser = auth()->user();
        if (!$authUser->can('manage roles_permissions')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission denied. Please contact the administrator.'
            ]);
        }


        $user = User::findOrFail($id);

        // ❌ prevent assigning restricted roles
        // if (in_array($request->role, ['admin', 'super-admin'])) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unauthorized role'
        //     ]);
        // }

        $user->syncRoles([$request->role]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully'
        ]);
        } catch (\Exception $e) {
            Log::error('UserController::changeRole failed: ' . $e->getMessage());
            return null;
        }
    }

}
