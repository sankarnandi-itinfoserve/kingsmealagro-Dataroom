<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): ?View
    {
        try {
            $roles = Role::all();

            return view('auth.register', compact('roles'));
        } catch (\Exception $e) {
            Log::error('RegisteredUserController::create failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): ?RedirectResponse
    {
        try {
            $request->validate([
                'fname'    => ['required', 'string', 'max:255'],
                'lname'    => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'fname'        => $request->fname,
                'lname'        => $request->lname,
                'email'        => $request->email,
                'password'     => Hash::make($request->password),
                'emp_id'       => 'EMP' . str_pad(User::withTrashed()->max('id') + 1, 3, '0', STR_PAD_LEFT),
                'username'     => $request->fname . '_' . $request->lname,
                'replay_email' => $request->email,
                'displayName'  => $request->fname . ' ' . $request->lname,
                'role'         => 'user',
            ]);

            $user->assignRole(Role::where('is_default', true)->value('name') ?? 'user');

            event(new Registered($user));
            Mail::to($user->email)->send(new WelcomeMail($user));

            return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('RegisteredUserController::store failed: ' . $e->getMessage());
            return null;
        }
    }
}
