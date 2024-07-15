<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::user();
        
        if ($user) {
            // Get the last activity time
            $lastActivity = session('last_activity');

            // Define your timeout period (in seconds)
            $timeout = 30 * 60;

            // Check if the last activity time exceeds the timeout period
            if (time() - $lastActivity > $timeout) {
                $user->status_id = 2; // Set to "offline"
                $user->save();

                // Optionally, you could log the user out
                // Auth::logout();
            }

            // Update the last activity time
            session(['last_activity' => time()]);
        }

        return $response;
    }
}
