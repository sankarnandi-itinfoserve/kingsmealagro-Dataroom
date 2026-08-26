<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View|null
    {
        try {
            return $request->user()->hasVerifiedEmail()
                        ? redirect()->intended(route('dashboard', absolute: false))
                        : view('auth.verify-email');
        } catch (\Exception $e) {
            Log::error('EmailVerificationPromptController::__invoke failed: ' . $e->getMessage());
            return null;
        }
    }
}
