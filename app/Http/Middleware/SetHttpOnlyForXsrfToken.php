<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetHttpOnlyForXsrfToken
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
        $response = $next($request);

        if ($request->hasCookie('XSRF-TOKEN')) {
            // Retrieve the XSRF-TOKEN cookie value
            $xsrfToken = $request->cookie('XSRF-TOKEN');
            // Set the HttpOnly flag for the XSRF-TOKEN cookie
            $response->headers->setCookie(
                Cookie::make('XSRF-TOKEN', $xsrfToken, 0, '/', null, config('session.secure'), true, false, config('session.same_site'))
            );
        }

        return $response;
    }
}
