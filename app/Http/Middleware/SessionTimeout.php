<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $timeout = config('session.lifetime') * 60; // seconds

            if (session()->has('lastActivityTime')) {

                $lastActivity = session('lastActivityTime');
                $currentTime = time();

                if (($currentTime - $lastActivity) > $timeout) {

                    auth()->logout();
                    session()->flush();
                    session()->regenerate();

                    return redirect()->route('login')
                        ->with('error', 'Session expired due to inactivity.');
                }
            }

            // update last activity
            session(['lastActivityTime' => time()]);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('SessionTimeout::handle failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
