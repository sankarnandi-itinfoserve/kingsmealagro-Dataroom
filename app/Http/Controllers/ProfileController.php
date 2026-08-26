<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): ?View
    {
        try {
            return view('profile.edit', [
                'user' => $request->user(),
            ]);
        } catch (\Exception $e) {
            Log::error('ProfileController::edit failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update the user's profile information.
     */
    // public function update(ProfileUpdateRequest $request): RedirectResponse
    // {
    //     dd($request->all()  );
        
    //     $request->user()->fill($request->validated());

    //     if ($request->user()->isDirty('email')) {
    //         $request->user()->email_verified_at = null;
    //     }

    //     $request->user()->save();

    //     return Redirect::route('profile.edit')->with('status', 'profile-updated');
    // }
    public function update(Request $request): ?RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'fname' => ['required', 'string', 'max:255'],
                'lname' => ['required', 'string', 'max:255'],
            ]);

            $user->fill($validated);
            $user->save();

            return redirect()
                ->route('profile.edit')
                ->with('status', 'profile-updated');
        } catch (\Exception $e) {
            Log::error('ProfileController::update failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): ?RedirectResponse
    {
        try {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);

            $user = $request->user();

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/');
        } catch (\Exception $e) {
            Log::error('ProfileController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }
}
