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
        'saml2/shoprite/acs' // Exclude the '/saml2/shoprite/acs' URI from CSRF verification
    ];
}
