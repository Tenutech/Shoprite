<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        switch ($user->role_id) {
            case 1:
            case 2:
                $url = 'admin/';
                break;
            case 3:
                $url = 'manager/';
                break;
            default:
                $url = '/';
                break;
        }

        if (in_array($user->role_id, $roles)) {
            return $next($request);
        }

        return redirect($url . 'home');
    }
}
