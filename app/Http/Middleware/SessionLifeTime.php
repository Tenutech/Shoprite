<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;

class SessionLifeTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Fetch session timeout setting from the database
        $sessionTimeout = Setting::where('key', 'session_timeout')->first();

        if ($sessionTimeout) {
            // Use the value from the DB
            $lifetime = (int) $sessionTimeout->value;
        } else {
            // Default to 30 minutes
            $lifetime = 30;
        }

        // Apply the session lifetime
        Config::set('session.lifetime', $lifetime);

        return $next($request);
    }
}
