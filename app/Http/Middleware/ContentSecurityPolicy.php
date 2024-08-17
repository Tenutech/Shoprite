<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * This method sets the Content-Security-Policy (CSP) headers for the response.
     * CSP helps mitigate XSS attacks by specifying which sources are allowed to be loaded by the browser.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request.
     * @param  \Closure  $next  The next middleware to be executed.
     * @return mixed  The HTTP response with CSP headers.
     */
    public function handle($request, Closure $next)
    {
        // Proceed to the next middleware and get the response
        $response = $next($request);

        // Define the Content-Security-Policy (CSP) header
        $csp = "default-src 'self'; "; // Only allow content from the same origin by default
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://maps.googleapis.com; "; // Allow scripts from the same origin, inline scripts, eval(), jsdelivr, and Google Maps
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "; // Allow styles from the same origin, inline styles, and Google Fonts
        $csp .= "font-src 'self' https://fonts.gstatic.com data:; "; // Allow fonts from the same origin, Google Fonts, and data URIs
        $csp .= "img-src 'self' data:; "; // Allow images from the same origin and data URIs
        $csp .= "connect-src 'self' https://cdn.lordicon.com https://maps.googleapis.com https://login.microsoftonline.com;"; // Allow connections to the same origin, lordicon CDN, and Google Maps
        $csp .= "form-action 'self' https://login.microsoftonline.com; "; // Allow form actions to your SAML provider
        $csp .= "frame-ancestors 'none'; "; // Prevent the page from being embedded in frames

        // Set the Content-Security-Policy header in the response
        $response->headers->set('Content-Security-Policy', $csp);

        // Return the response with the CSP headers
        return $response;
    }
}
