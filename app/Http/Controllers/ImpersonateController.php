<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonateController extends Controller
{
    /**
     * Start impersonation.
     *
     * @param  string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate($id)
    {
        try {
            // Decrypt and retrieve the user
            $userID = Crypt::decrypt($id);
            $userToImpersonate = User::findOrFail($userID);

            // Check if the authenticated user can impersonate
            if (Auth::user()->canImpersonate() && $userToImpersonate->canBeImpersonated()) {
                Auth::user()->impersonate($userToImpersonate);

                // Redirect based on role
                return match ($userToImpersonate->role_id) {
                    1, 2 => redirect('/admin/home'),
                    3 => redirect('/rpp/home'),
                    4 => redirect('/dtdp/home'),
                    5 => redirect('/dpp/home'),
                    6 => redirect('/manager/home'),
                    7 => redirect('/home'),
                    default => redirect('/home')
                };
            }

            return redirect()->back()->with('error', 'Unauthorized impersonation attempt.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to impersonate user.');
        }
    }

    /**
     * Stop impersonation.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonating($roleId = null)
    {
        // Access the impersonate manager
        $manager = app(ImpersonateManager::class);

        // Check if the current user is impersonating another user
        if ($manager->isImpersonating()) {
            // End impersonation
            $manager->leave();

            // Redirect based on impersonated user's role_id
            switch ($roleId) {
                case 1:
                    return redirect('/admin/super-admins')->with('success', 'Impersonation ended.');
                case 2:
                    return redirect('/admin/admins')->with('success', 'Impersonation ended.');
                case 3:
                    return redirect('/admin/rpps')->with('success', 'Impersonation ended.');
                case 4:
                    return redirect('/admin/dtdps')->with('success', 'Impersonation ended.');
                case 5:
                    return redirect('/admin/dpps')->with('success', 'Impersonation ended.');
                case 6:
                    return redirect('/admin/managers')->with('success', 'Impersonation ended.');
                case 7:
                    return redirect('/admin/users')->with('success', 'Impersonation ended.');
                default:
                    return redirect('/admin/users')->with('success', 'Impersonation ended.');
            }
        }

        return redirect()->back()->with('error', 'You are not impersonating any user.');
    }
}
