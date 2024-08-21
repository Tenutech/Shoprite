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
            // Convert minutes to minutes, as Laravel expects session lifetime to be in minutes
            $lifetime = (int) $sessionTimeout->value;

            // Set the session lifetime configuration dynamically
            Config::set('session.lifetime', $lifetime);
        }

        return $next($request);
    }
}
