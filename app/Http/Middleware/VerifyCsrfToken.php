<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * This array contains the URIs that are not subject to CSRF protection.
     * Requests to these URIs will not require a valid CSRF token, making them
     * exempt from the CSRF middleware.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/shoops', // Exclude the '/shoops' URI from CSRF verification
        '/jira',   // Exclude the '/jira' URI from CSRF verification
    ];

    /**
     * Add the CSRF token cookie to the response with the HttpOnly flag set.
     *
     * This method overrides the parent method to ensure that the XSRF-TOKEN
     * cookie, which is responsible for storing the CSRF token, is created
     * with the HttpOnly flag. This flag helps protect the cookie from being
     * accessed through client-side scripts, enhancing security.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return \Illuminate\Http\Response
     */
    protected function addCookieToResponse($request, $response)
    {
        // Call the parent method to add the default CSRF token cookie
        $response = parent::addCookieToResponse($request, $response);

        // Check if the XSRF-TOKEN cookie is present in the request
        if ($request->hasCookie('XSRF-TOKEN')) {
            // Retrieve the value of the XSRF-TOKEN cookie
            $xsrfToken = $request->cookie('XSRF-TOKEN');

            // Set the XSRF-TOKEN cookie with the HttpOnly flag
            // This prevents JavaScript from accessing the cookie
            $response->headers->setCookie(
                Cookie::make(
                    'XSRF-TOKEN',            // Cookie name
                    $xsrfToken,              // Cookie value (CSRF token)
                    0,                       // Expiration time (0 = session cookie)
                    '/',                     // Path (root of the domain)
                    null,                    // Domain (null = default)
                    config('session.secure'), // Secure flag (true if HTTPS)
                    true,                    // HttpOnly flag (prevents access via JavaScript)
                    false,                   // Raw flag (whether the value should be raw)
                    config('session.same_site') // SameSite flag (limits cross-site requests)
                )
            );
        }

        // Return the modified response with the secure XSRF-TOKEN cookie
        return $response;
    }
}
