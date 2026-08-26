<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAuthLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): ?View
    {
        try {
            return view('auth.login');
        } catch (\Exception $e) {
            Log::error('AuthenticatedSessionController::create failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $request->email)->first();

            // ❌ USER NOT FOUND
            if (!$user) {
                return back()->withErrors([
                    'email' => 'Invalid credentials.',
                ]);
            }

            // 🔒 CHECK IF LOCKED
            if ($user->locked_until && Carbon::now()->lt($user->locked_until)) {

                $remainingSeconds = Carbon::now()->diffInSeconds($user->locked_until);

                $minutes = floor($remainingSeconds / 60);
                $seconds = $remainingSeconds % 60;

                return back()->withErrors([
                    'email' => "Account locked. Try again in {$minutes} min {$seconds} sec.",
                    'attempts' => "Account locked. Try again in {$minutes} min {$seconds} sec."
                ]);
            }

            // ✅ ATTEMPT LOGIN
            if (Hash::check($request->password, $user->password)) {

                // RESET ATTEMPTS ON SUCCESS
                $user->update([
                    'failed_attempts' => 0,
                    'locked_until' => null
                ]);

                Auth::guard('web')->login($user);

                $request->session()->regenerate();

                UserAuthLog::create([
                    'user_id'     => $user->id,
                    'logged_in'   => Carbon::now(),
                    'logon_type'  => 'login',
                    'device_info' => $request->userAgent(),
                ]);

                return redirect()->intended(route('dashboard', absolute: false));
            }

            // ❌ FAILED LOGIN
            $maxAttempts = config('auth.max_attempts', 5);
            $lockMinutes = config('auth.lock_time', 15);

            $user->failed_attempts += 1;

            if ($user->failed_attempts >= $maxAttempts) {
                $user->locked_until = Carbon::now()->addMinutes($lockMinutes);
                $user->failed_attempts = 0; // reset after lock
            }

            $user->save();
            $remaining = $maxAttempts - $user->failed_attempts;

            return back()->withErrors([
                'email' => "Invalid credentials.",
                'attempts' => "Invalid credentials. {$remaining} attempts left."
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('AuthenticatedSessionController::store failed: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): ?RedirectResponse
    {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect('/');
        } catch (\Exception $e) {
            Log::error('AuthenticatedSessionController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }
}
