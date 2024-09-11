<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply the 'guest' middleware to all methods except 'logout'
        // This means only guests (unauthenticated users) can access the login methods
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override the method to support email or ID number login.
     *
     * @return string The field being used for login ('email' or 'id_number')
     */
    public function username()
    {
        // Get the input from the 'login' field in the request (this could be an email or ID number)
        $login = request()->input('login');

        // Determine if the input is an email address or an ID number
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'id_number';

        // Merge the field and its value into the request so it can be processed
        request()->merge([$field => $login]);

        // Return the field that will be used for login ('email' or 'id_number')
        return $field;
    }

    /**
     * Send the failed login response for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Get the field that was used for login (either 'email' or 'id_number')
        $field = $this->username();

        // Directly specify the error message to be returned for failed login attempts
        $message = 'The credentials you entered do not match our records.';

        // Attach the error message to the login field
        throw ValidationException::withMessages([
            'login' => $message,
        ]);
    }

    /**
     * The user has been authenticated.
     * This method is called after a user has successfully logged in.
     * You can use it to perform additional tasks upon authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user  The authenticated user instance
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Set the user's status to "online" (1)
        $user->status_id = 1;
        $user->save();

        // Store the current time in the session as 'last_activity'
        session(['last_activity' => time()]);

        // Redirect the user based on their role_id
        switch ($user->role_id) {
            case 1:
            case 2:
                return redirect('/admin/home'); // Redirect admins to admin home
            case 3:
                return redirect('/rpp/home'); // Redirect rpp to rpp home
            case 4:
                return redirect('/dtdp/home'); // Redirect dtdp to dtdp home
            case 5:
                return redirect('/dpp/home'); // Redirect dpp to dpp home
            case 6:
                return redirect('/manager/home'); // Redirect managers to manager home
            default:
                return redirect('/home'); // Fallback for any other cases
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // If the user is not authenticated, redirect to the home page
        if (!Auth::check()) {
            return redirect('/');
        }

        // Get the currently authenticated user
        $user = Auth::user();

        if ($user) {
            // Set the user's status to "offline" (2)
            $user->status_id = 2;
            $user->save();
        }

        // Log the user out of the application
        $this->guard()->logout();

        // Invalidate the session to clear all session data
        $request->session()->invalidate();

        // Regenerate the CSRF token to prevent session fixation attacks
        $request->session()->regenerateToken();

        // Redirect the user to the home page or perform additional tasks upon logout
        return $this->loggedOut($request) ?: redirect('/');
    }

    /**
     * Get the guard to be used during authentication.
     * This is a helper method used by the AuthenticatesUsers trait.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
